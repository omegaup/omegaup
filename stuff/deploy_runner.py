#!/usr/bin/python3

import argparse
import hashlib
import logging
import os.path
import shlex
import subprocess
import tempfile

DOWNLOAD_FILES = {
    'omegajail-xenial-distrib-x86_64.tar.bz2': 'https://s3.amazonaws.com/omegaup-omegajail/omegajail-xenial-distrib-x86_64.tar.bz2',
    'omegaup-runner.tar.bz2': 'https://s3.amazonaws.com/omegaup-dist/omegaup-runner.tar.bz2',
}

NULL_HASH = '0000000000000000000000000000000000000000'

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

    def scp(self, src, dest, mode=None, owner=None, group=None):
        subprocess.check_call(['/usr/bin/scp', src,
                               '%s:.tmp' % self._hostname])
        if mode != None:
            self.sudo(['/bin/chmod', '0%o' % mode, '.tmp'])
        if owner != None:
            self.sudo(['/bin/chown', owner, '.tmp'])
        if group != None:
            self.sudo(['/bin/chgrp', group, '.tmp'])
        return self.sudo(['/bin/mv', '.tmp', dest])

def hash_for(filename):
    sha1sum_filename = '%s.SHA1SUM' % filename
    if not os.path.exists(sha1sum_filename):
        logging.info('%s not found, returning null hash for %s',
                     sha1sum_filename, filename)
        return NULL_HASH
    with open(sha1sum_filename) as f:
        return f.read().strip()

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

    for path, url in DOWNLOAD_FILES.items():
        if args.upgrade or runner.run(['[[ -f %s && "`sha1sum -b %s`" == "%s" ]]' %
                                       (shlex.quote(path), shlex.quote(path), hash_for(path))]).returncode != 0:
            logging.info('Downloading %s...', url)
            runner.run(['[ -f %s ] && rm %s' % (shlex.quote(path), shlex.quote(path))])
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
                       '/etc/omegaup/runner/key.pem', mode=int('0600', 8),
                       owner='omegaup', group='omegaup')
            runner.scp(os.path.join(tmpdirname, 'certificate.pem'),
                       '/etc/omegaup/runner/certificate.pem',
                       owner='omegaup', group='omegaup')

    if runner.run([
        '[', '-h',
        '/etc/systemd/system/multi-user.target.wants/omegaup-runner.service',
        ']']).returncode != 0:
        runner.sudo(['/bin/systemctl', 'enable', 'omegaup-runner'], check=True)

    runner.sudo(['/bin/rm', '-f', '/etc/sudoers.d/minijail'], check=True)
    runner.sudo(['/bin/systemctl', 'daemon-reload'], check=True)
    runner.sudo(['/bin/systemctl', 'start', 'omegaup-runner'], check=True)

if __name__ == '__main__':
    main()
