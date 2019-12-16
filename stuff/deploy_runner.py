#!/usr/bin/python3
'''Deploys one runner.'''

import argparse
import logging
import os.path
import shlex
import subprocess
import tempfile
from typing import List, Optional

DOWNLOAD_FILES = {
    'omegajail-bionic-rootfs-x86_64.tar.xz':
    'https://s3.amazonaws.com/omegaup-omegajail/'
    'omegajail-bionic-rootfs-x86_64.tar.xz',
    'omegajail-bionic-distrib-x86_64.tar.xz':
    'https://s3.amazonaws.com/omegaup-omegajail/'
    'omegajail-bionic-distrib-x86_64.tar.xz',
    'omegaup-runner-config.tar.xz':
    'https://s3.amazonaws.com/omegaup-dist/'
    'omegaup-runner-config.tar.xz',
    'omegaup-runner.tar.xz':
    'https://github.com/omegaup/quark/releases/download/v1.1.25/'
    'omegaup-runner.tar.xz',
}

NULL_HASH = '0000000000000000000000000000000000000000'


class RemoteRunner:
    '''Runs commands in the runner machine through ssh.'''
    def __init__(self, hostname: str):
        self._hostname = hostname

    def run(self,
            args: List[str],
            *,
            capture: bool = True,
            check: bool = False
            ) -> subprocess.CompletedProcess:  # type: ignore
        '''Wrapper around subprocess.run through ssh.'''

        std = None
        if capture:
            std = subprocess.PIPE
        logging.debug('Running %s', ' '.join(shlex.quote(arg) for arg in args))
        # Travis uses Python <3.5, which does not yet have subprocess.run.
        # pylint: disable=no-member
        return subprocess.run(['/usr/bin/ssh', self._hostname] + args,
                              stdout=std,
                              stderr=std,
                              universal_newlines=True,
                              shell=False,
                              check=check)

    def sudo(self,
             args: List[str],
             *,
             capture: bool = True,
             check: bool = False
             ) -> subprocess.CompletedProcess:  # type: ignore
        '''Wrapper to run a command under sudo through ssh.'''

        return self.run(['/usr/bin/sudo'] + args, capture=capture, check=check)

    def scp(self,
            src: str,
            dest: str,
            *,
            mode: Optional[int] = None,
            owner: Optional[str] = None,
            group: Optional[str] = None
            ) -> subprocess.CompletedProcess:  # type: ignore
        '''Copies a file to the remote machine.'''
        # pylint: disable=too-many-arguments

        subprocess.check_call(
            ['/usr/bin/scp', src,
             '%s:.tmp' % self._hostname])
        if mode is not None:
            self.sudo(['/bin/chmod', '0%o' % mode, '.tmp'])
        if owner is not None:
            self.sudo(['/bin/chown', owner, '.tmp'])
        if group is not None:
            self.sudo(['/bin/chgrp', group, '.tmp'])
        return self.sudo(['/bin/mv', '.tmp', dest])


def hash_for(filename: str) -> str:
    '''Returns the hash for the specified file.'''

    sha1sum_filename = '%s.SHA1SUM' % filename
    if not os.path.exists(sha1sum_filename):
        logging.info('%s not found, returning null hash for %s',
                     sha1sum_filename, filename)
        return NULL_HASH
    with open(sha1sum_filename) as f:
        return f.read().strip()


def main() -> None:
    '''Main entrypoint.'''

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
        runner.sudo(['/usr/sbin/useradd', '--home-dir', '/var/lib/omegaup',
                     '--create-home', '--shell', '/usr/sbin/nologin',
                     'omegaup'],
                    capture=False)
    if runner.run(['[', '-d', '/var/log/omegaup', ']']).returncode != 0:
        runner.sudo(['/bin/mkdir', '-p', '/var/log/omegaup'], check=True)
        runner.sudo(['/bin/chown', 'omegaup.omegaup', '/var/log/omegaup'],
                    check=True)
    if runner.run(['[', '-d', '/etc/omegaup/runner', ']']).returncode != 0:
        runner.sudo(['/bin/mkdir', '-p', '/etc/omegaup/runner'], check=True)

    for path, url in DOWNLOAD_FILES.items():
        if runner.run([
                (f'[[ '
                 f'-f {shlex.quote(path)} && '
                 f'"`sha1sum -b {shlex.quote(path)}`" == "{hash_for(path)}" '
                 f']]'),
        ]).returncode == 0:
            logging.info('Hashes matched, skipping')
            continue
        logging.info('Downloading %s...', url)
        runner.run(
            ['[ -f %s ] && rm %s' % (shlex.quote(path), shlex.quote(path))])
        runner.run(
            ['/usr/bin/curl', '--remote-time', '--output', path, '--url', url])
        logging.info('Extracting %s...', url)
        runner.sudo(['/bin/tar', '-xf', path, '-C', '/'])

    if runner.run(['[', '-f', '/etc/omegaup/runner/key.pem', ']'
                   ]).returncode != 0:
        with tempfile.TemporaryDirectory() as tmpdirname:
            subprocess.check_call([
                '/usr/bin/certmanager', 'cert', '--root', args.certroot,
                '--hostname', args.runner, '--output',
                os.path.join(tmpdirname, 'key.pem'), '--cert-output',
                os.path.join(tmpdirname, 'certificate.pem')
            ])
            runner.scp(os.path.join(tmpdirname, 'key.pem'),
                       '/etc/omegaup/runner/key.pem',
                       mode=int('0600', 8),
                       owner='omegaup',
                       group='omegaup')
            runner.scp(os.path.join(tmpdirname, 'certificate.pem'),
                       '/etc/omegaup/runner/certificate.pem',
                       owner='omegaup',
                       group='omegaup')

    if runner.run([
            '[',
            '-h',
            ('/etc/systemd/system/multi-user.target.wants/'
             'omegaup-runner.service'),
            ']'
    ]).returncode != 0:
        runner.sudo(['/bin/systemctl', 'enable', 'omegaup-runner'], check=True)

    runner.sudo(['/bin/systemctl', 'daemon-reload'], check=True)
    runner.sudo(['/bin/systemctl', 'start', 'omegaup-runner'], check=True)


if __name__ == '__main__':
    main()
