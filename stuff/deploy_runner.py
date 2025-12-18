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

        subprocess.check_call(
            ['/usr/bin/scp', src,
             f'{self._hostname}:.tmp']
        )
        if mode is not None:
            self.sudo(['/bin/chmod', '0%o' % mode, '.tmp'])
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

        run = self.sudo if owner is not None else self.run
        tmpfile = f'/tmp/.{time.time()}.tmp'
        run(['/bin/cp', '/dev/stdin', tmpfile],
            stdin=contents,
            capture=False,
            check=True)
        if owner is not None:
            run(['/bin/chown', owner, tmpfile])
        if mode is not None:
            run(['/bin/chmod', '0%o' % mode, tmpfile])
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


def main() -> None:
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser()
    parser.add_argument('-v', '--verbose', action='store_true')
    parser.add_argument('--upgrade', action='store_true')
    parser.add_argument('--certroot', required=True)
    parser.add_argument('runner', help='Runner name')
    args = parser.parse_args()

    logging.basicConfig(level=logging.DEBUG if args.verbose else logging.INFO)

    runner = RemoteRunner(args.runner)

    runner.sudo(['/bin/systemctl', 'stop', 'omegaup-runner'])

    if args.upgrade:
        runner.sudo(['/usr/bin/apt', 'update', '-y'], check=True)
        runner.sudo(['/usr/bin/apt', 'upgrade', '-y'], check=True)

    runner.sudo(['/bin/systemctl', 'daemon-reload'], check=True)
    runner.sudo(['/bin/systemctl', 'start', 'omegaup-runner'], check=True)


if __name__ == '__main__':
    main()
