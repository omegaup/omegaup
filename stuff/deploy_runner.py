#!/usr/bin/env python3
'''Deploys one runner.'''

import argparse
import logging
import os.path
import shlex
import subprocess
import tempfile
import time
from typing import List, Optional

DOWNLOAD_FILES = [
    ('https://github.com/omegaup/omegajail/releases/download/v3.0.0/'
     'omegajail-focal-rootfs-x86_64.tar.xz'),
    ('https://github.com/omegaup/omegajail/releases/download/v3.0.1/'
     'omegajail-focal-distrib-x86_64.tar.xz'),
    'https://s3.amazonaws.com/omegaup-dist/omegaup-runner-config.tar.xz',
    ('https://github.com/omegaup/quark/releases/download/v1.6.3/'
     'omegaup-runner.tar.xz'),
    ('https://github.com/omegaup/logslurp/releases/download/v0.1.4/'
     'omegaup-logslurp.tar.xz'),
]

NULL_HASH = '0000000000000000000000000000000000000000'

LOGSLURP_TEMPLATE = r'''{{
    "streams": [
        {{
            "labels": {{
                "environment": "runner",
                "host": "{hostname}",
                "job": "omegaup-runner"
            }},
            "path": "/var/log/omegaup/runner.log",
            "timestamp_layout": "2006-01-02T15:04:05-0700",
            "regexp": "(?m)^t=(?P<ts>\\d+-\\d+-\\d+T\\d+:\\d+:\\d+[-+]\\d+) lvl=(?P<lvl>[^ ]+?) (.*?)$"
        }}
    ]
}}'''  # noqa

LOGSLURP_SERVICE = '''[Unit]
Description=omegaUp logslurp service
Wants=omegaup-runner.service
After=network.target omegaup-runner.service

[Service]
Type=simple
User=omegaup
Group=omegaup
ExecStart=/usr/bin/omegaup-logslurp
WorkingDirectory=/var/lib/omegaup
Restart=always
CPUSchedulingPolicy=idle
IOSchedulingClass=idle

[Install]
WantedBy=multi-user.target'''


class RemoteRunner:
    '''Runs commands in the runner machine through ssh.'''
    def __init__(self, hostname: str):
        self._hostname = hostname

    @property
    def hostname(self) -> str:
        '''Returns the remote hostname.'''
        return self._hostname

    def run(self,
            args: List[str],
            *,
            stdin: Optional[str] = None,
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
                              input=stdin,
                              stdout=std,
                              stderr=std,
                              universal_newlines=True,
                              shell=False,
                              check=check)

    def sudo(self,
             args: List[str],
             *,
             stdin: Optional[str] = None,
             capture: bool = True,
             check: bool = False
             ) -> subprocess.CompletedProcess:  # type: ignore
        '''Wrapper to run a command under sudo through ssh.'''

        return self.run(['/usr/bin/sudo'] + args,
                        stdin=stdin,
                        capture=capture,
                        check=check)

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
             f'{self._hostname}:.tmp'])
        if mode is not None:
            self.sudo(['/bin/chmod', f'0{mode:o}', '.tmp'])
        if owner is not None:
            self.sudo(['/bin/chown', owner, '.tmp'])
        if group is not None:
            self.sudo(['/bin/chgrp', group, '.tmp'])
        return self.sudo(['/bin/mv', '.tmp', dest])

    def put(self,
            contents: str,
            dest: str,
            *,
            mode: Optional[int] = None,
            owner: Optional[str] = None,
            group: Optional[str] = None
            ) -> subprocess.CompletedProcess:  # type: ignore
        '''Puts the provided file contents onto the remote machine.'''
        # pylint: disable=too-many-arguments

        # If there is no need to change the owner, we can run as the current
        # user.
        if owner is not None:
            run = self.sudo
        else:
            run = self.run

        tmpfile = f'/tmp/.{time.time()}.tmp'
        run(['/bin/cp', '/dev/stdin', tmpfile],
            stdin=contents,
            capture=False,
            check=True)
        if owner is not None:
            run(['/bin/chown', owner, tmpfile])

        if mode is not None:
            run(['/bin/chmod', f'0{mode:o}', tmpfile])
        if group is not None:
            run(['/bin/chgrp', group, tmpfile])
        return run(['/bin/mv', tmpfile, dest])


def hash_for(filename: str) -> str:
    '''Returns the hash for the specified file.'''

    sha1sum_filename = f'{filename}.SHA1SUM'
    if not os.path.exists(sha1sum_filename):
        logging.info('%s not found, returning null hash for %s',
                     sha1sum_filename, filename)
        return NULL_HASH
    with open(sha1sum_filename, encoding='utf-8') as f:
        return f.read().strip()


def _create_users(runner: RemoteRunner) -> None:
    if runner.run(['/usr/bin/id', 'omegaup']).returncode != 0:
        runner.sudo(['/usr/sbin/useradd', '--home-dir', '/var/lib/omegaup',
                     '--create-home', '--shell', '/usr/sbin/nologin',
                     'omegaup'],
                    capture=False)


def _create_directories(runner: RemoteRunner) -> None:
    if runner.run(['[', '-d', '/var/log/omegaup', ']']).returncode != 0:
        runner.sudo(['/bin/mkdir', '-p', '/var/log/omegaup'], check=True)
        runner.sudo(['/bin/chown', 'omegaup.omegaup', '/var/log/omegaup'],
                    check=True)
    if runner.run(['[', '-d', '/etc/omegaup/runner', ']']).returncode != 0:
        runner.sudo(['/bin/mkdir', '-p', '/etc/omegaup/runner'], check=True)
    if runner.run(['[', '-d', '/etc/omegaup/logslurp', ']']).returncode != 0:
        runner.sudo(['/bin/mkdir', '-p', '/etc/omegaup/logslurp'], check=True)


def _download_files(runner: RemoteRunner) -> None:
    refresh_omegajail_rootfs = False

    for url in DOWNLOAD_FILES:
        filename = os.path.basename(url)
        url_filename = f'.{filename}.url'
        quoted_filename = shlex.quote(filename)
        quoted_url_filename = shlex.quote(url_filename)
        if runner.run([
                (f'[[ '
                 f'-f {quoted_url_filename} && '
                 f'"`cat {quoted_url_filename}`" == "{shlex.quote(url)}" '
                 f']]'),
        ]).returncode == 0:
            logging.info('URLs matched, skipping')
            continue
        logging.info('Downloading %s...', url)
        runner.run([
            f'[ -f {quoted_filename} ] && rm {quoted_filename}'
        ])
        runner.run([
            '/usr/bin/curl', '--location', '--remote-time', '--output',
            filename, '--url', url
        ], check=True)
        if 'rootfs' in filename:
            refresh_omegajail_rootfs = True
            continue
        if 'omegajail' in filename and refresh_omegajail_rootfs:
            # If we are going to refresh the rootfs anyways, might as well
            # delay this until when the rootfs is refreshed.
            continue
        logging.info('Extracting %s...', url)
        runner.sudo(['/bin/tar', '-xf', filename, '-C', '/'], check=True)
        runner.put(url, url_filename)

    if refresh_omegajail_rootfs:
        logging.info('Refreshing omegajail rootfs...')
        runner.sudo(['/bin/rm', '-rf', '/var/lib/omegajail'], check=True)
        for url in DOWNLOAD_FILES:
            filename = os.path.basename(url)
            url_filename = f'.{filename}.url'
            if 'omegajail' not in filename:
                continue
            logging.info('Extracting %s...', url)
            runner.sudo(['/bin/tar', '-xf', filename, '-C', '/'], check=True)
            runner.put(url, url_filename)


def _create_ssl_keys(runner: RemoteRunner, certroot: str) -> None:
    if runner.run(['[', '-f', '/etc/omegaup/runner/key.pem', ']'
                   ]).returncode == 0:
        return
    with tempfile.TemporaryDirectory() as tmpdirname:
        subprocess.check_call([
            '/usr/bin/certmanager', 'cert', '--root', certroot,
            '--hostname', runner.hostname, '--output',
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


def _install_runner_service(runner: RemoteRunner) -> None:
    if runner.run([
            '[', '-h',
            ('/etc/systemd/system/multi-user.target.wants/'
             'omegaup-runner.service'),
            ']'
    ]).returncode == 0:
        return
    runner.sudo(['/bin/systemctl', 'enable', 'omegaup-runner'], check=True)


def _install_logslurp_service(runner: RemoteRunner) -> None:
    if runner.run([
            '[',
            '-f',
            '/etc/omegaup/logslurp/config.json',
            ']',
    ]).returncode != 0:
        runner.put(LOGSLURP_TEMPLATE.format(hostname=runner.hostname),
                   '/etc/omegaup/logslurp/config.json',
                   mode=int('0600', 8),
                   owner='omegaup',
                   group='omegaup')

    if runner.run([
            '[',
            '-f',
            '/etc/systemd/system/omegaup-logslurp.service',
            ']',
    ]).returncode != 0:
        runner.put(LOGSLURP_SERVICE,
                   '/etc/systemd/system/omegaup-logslurp.service',
                   owner='root',
                   group='root')

    if runner.run([
            '[', '-h',
            ('/etc/systemd/system/multi-user.target.wants/'
             'omegaup-logslurp.service'),
            ']'
    ]).returncode != 0:
        runner.sudo(['/bin/systemctl', 'enable', 'omegaup-logslurp'],
                    check=True)
        runner.sudo(['/bin/systemctl', 'start', 'omegaup-logslurp'],
                    check=True)


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

    _download_files(runner)
    _create_users(runner)
    _create_directories(runner)
    _create_ssl_keys(runner, args.certroot)
    _install_runner_service(runner)
    _install_logslurp_service(runner)

    runner.sudo(['/bin/systemctl', 'daemon-reload'], check=True)
    runner.sudo(['/bin/systemctl', 'start', 'omegaup-runner'], check=True)


if __name__ == '__main__':
    main()
