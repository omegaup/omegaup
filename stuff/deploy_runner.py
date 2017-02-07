#!/usr/bin/python3

import argparse
import hashlib
import logging
import os.path
import shlex
import subprocess
import tempfile

LITERAL_FILES = {
    '/etc/sudoers.d/minijail': b'''
omegaup ALL = NOPASSWD: /var/lib/minijail/bin/minijail0
'''.lstrip(),
    '/etc/omegaup/runner/config.json': b'''
{
        "Logging": {
                "File": "/var/log/omegaup/runner.log"
        },
        "Runner": {
                "RuntimePath": "/var/lib/omegaup/runner",
                "GraderURL": "https://omegaup.com:11302"
        },
        "TLS": {
                "CertFile": "/etc/omegaup/runner/certificate.pem",
                "KeyFile": "/etc/omegaup/runner/key.pem"
        },
        "Tracing": {
                "File": "/var/log/omegaup/runner.tracing.json"
        }
}
'''.lstrip(),
    '/etc/systemd/system/omegaup-runner.service': b'''
[Unit]
Description=omegaUp runner
After=network.target

[Service]
Type=simple
User=omegaup
Group=omegaup
ExecStart=/usr/bin/omegaup-runner
WorkingDirectory=/var/lib/omegaup
Restart=always

[Install]
WantedBy=multi-user.target
'''.lstrip(),
}

DOWNLOAD_FILES = {
    'minijail-xenial-distrib-x86_64.tar.bz2': 'https://s3.amazonaws.com/omegaup-minijail/minijail-xenial-distrib-x86_64.tar.bz2',
    'omegaup-runner.tar.bz2': 'https://omegaup.com/omegaup-runner.tar.bz2',
}

class RemoteRunner:
    def __init__(self, hostname):
        self._hostname = hostname

    def run(self, args, input=None, capture=True, check=False):
        std = None
        if capture:
            std = subprocess.PIPE
        logging.debug('Running %s', ' '.join(shlex.quote(arg) for arg in args))
        return subprocess.run(['/usr/bin/ssh', self._hostname] + args,
                              stdout=std, stderr=std, universal_newlines=True,
                              input=input, shell=False, check=check)

    def sudo(self, args, **kwargs):
        return self.run(['/usr/bin/sudo'] + args, **kwargs)

    def scp(self, src, dest):
        subprocess.check_call(['/usr/bin/scp', src,
                               '%s:.tmp' % self._hostname])
        return self.sudo(['/bin/mv', '.tmp', dest])

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('-v', '--verbose', action='store_true')
    parser.add_argument('--upgrade', action='store_true')
    parser.add_argument('--certroot', required=True)
    parser.add_argument('runner', help='Runner name')
    args = parser.parse_args()

    if args.verbose:
        logging.basicConfig(level=logging.DEBUG)
    else:
        logging.basicConfig(level=logging.INFO)

    runner = RemoteRunner(args.runner)

    runner.sudo(['/bin/systemctl', 'stop', 'omegaup-runner'])

    if args.upgrade:
        runner.sudo(['/usr/bin/apt', 'update', '-y'], check=True)
        runner.sudo(['/usr/bin/apt', 'upgrade', '-y'], check=True)

    if runner.run(['/usr/bin/id', 'omegaup']).returncode != 0:
        runner.sudo(['/usr/sbin/useradd',
                     '--home-dir', '/var/lib/omegaup', '--create-home',
                     '--shell', '/usr/sbin/nologin', 'omegaup'], capture=False)
    if runner.run(['[', '-d', '/var/log/omegaup', ']']).returncode != 0:
        runner.sudo(['/bin/mkdir', '-p', '/var/log/omegaup'], check=True)
        runner.sudo(['/bin/chown', 'omegaup.omegaup', '/var/log/omegaup'],
                    check=True)
    if runner.run(['[', '-d', '/etc/omegaup/runner', ']']).returncode != 0:
        runner.sudo(['/bin/mkdir', '-p', '/etc/omegaup/runner'], check=True)

    for path, contents in LITERAL_FILES.items():
        expected = '%s  %s\n' % (hashlib.sha1(contents).hexdigest(), path)
        output = runner.sudo(['/usr/bin/sha1sum', path])
        if output.returncode != 0 or output.stdout != expected:
            runner.sudo(['/usr/bin/tee', path], input=contents.decode('utf-8'))

    for path, url in DOWNLOAD_FILES.items():
        if args.upgrade or runner.run(['[', '-f', path, ']']).returncode != 0:
            logging.info('Downloading %s...', url)
            runner.run(['/usr/bin/curl', '--remote-time', '--output', path,
                        '--url', url])
            logging.info('Extracting %s...', url)
            runner.sudo(['/bin/tar', '-xf', path, '-C', '/'])

    if runner.run(['[', '-f', '/etc/omegaup/runner/key.pem', ']']).returncode != 0:
        with tempfile.TemporaryDirectory() as tmpdirname:
            subprocess.check_call([
                '/usr/bin/certmanager', 'cert',
                '--root', args.certroot,
                '--hostname', args.runner,
                '--output', os.path.join(tmpdirname, 'key.pem'),
                '--cert-output', os.path.join(tmpdirname, 'certificate.pem')])
            runner.scp(os.path.join(tmpdirname, 'key.pem'),
                       '/etc/omegaup/runner/key.pem')
            runner.scp(os.path.join(tmpdirname, 'certificate.pem'),
                       '/etc/omegaup/runner/certificate.pem')

    runner.sudo(['/bin/systemctl', 'start', 'omegaup-runner'], check=True)

if __name__ == '__main__':
    main()
