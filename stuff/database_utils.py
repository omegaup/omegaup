#!/usr/bin/env python3
# -*- coding: utf-8 -*-

'''Library of utilities to work with MySQL.'''


import logging
import os
import shlex
import subprocess
import sys
import tempfile
from typing import Optional, Sequence


_MYSQL_BINARY = '/usr/bin/mysql'
_MYSQLDUMP_BINARY = '/usr/bin/mysqldump'


def inside_container() -> bool:
    '''Returns whether this command is run inside a container.'''
    return os.path.isdir('/opt/omegaup')


def check_inside_container() -> None:
    '''Re-runs the current command inside the container if needed.'''
    if inside_container():
        return
    sys.stderr.write(
        '\033[91mThis command needs to be run inside the container.\033[0m\n')
    sys.stderr.write('\n')
    answer = 'n'
    if sys.stdin.isatty():
        try:
            answer = input(
                '\033[95mDo you want to run this now?\033[0m [y/N]: ')
            answer = answer.lower().strip()
        except KeyboardInterrupt:
            sys.stderr.write('\n')
    if answer != 'y':
        sys.stderr.write('\nYou can use the following command to run '
                         'it inside the container:\n\n')
        sys.stderr.write(
            f'    docker compose exec -T frontend {shlex.join(sys.argv)}\n')
        sys.stderr.write('\n')
        sys.exit(1)
    result = subprocess.run(['docker compose', 'exec', '-T', 'frontend'] +
                            sys.argv,
                            check=False)
    sys.exit(result.returncode)


def quote(s: str) -> str:
    '''Escapes the string |s| so it can be safely used in a shell command.'''
    if 'quote' in dir(shlex):
        # This is unavailable in Python <3.3
        return shlex.quote(s)
    # pylint: disable=import-outside-toplevel
    import pipes
    return pipes.quote(s)


def default_config_file() -> Optional[str]:
    '''Returns the default config file path for MySQL.'''
    for candidate_path in (
            # ~/.my.cnf
            os.path.join(os.getenv('HOME') or '.', '.my.cnf'),
    ):
        if os.path.isfile(candidate_path):
            return candidate_path
    return None


def authentication(*,
                   config_file: Optional[str] = default_config_file(),
                   username: Optional[str] = None,
                   password: Optional[str] = None,
                   hostname: Optional[str] = None,
                   port: Optional[int] = None) -> Sequence[str]:
    '''Computes the authentication arguments for mysql binaries.'''
    if config_file and os.path.isfile(config_file):
        return [f'--defaults-file={quote(config_file)}']
    assert username
    args = [f'--user={quote(username)}']
    if password is not None:
        if password:
            args.append(f'--password={quote(password)}')
        else:
            args.append('--skip-password')
    if hostname is not None:
        args.extend(['--protocol=TCP', f'--host={quote(hostname)}'])
        if port is not None:
            args.extend([f'--port={port}'])
    return args


def mysql(query: str,
          *,
          container_check: bool = True,
          dbname: Optional[str] = None,
          auth: Sequence[str] = ()) -> str:
    '''Runs the MySQL commandline client with |query| as query.'''
    args = []
    if container_check and not inside_container():
        args.extend(['docker compose', 'exec', '-T', 'frontend'])
    args += [_MYSQL_BINARY] + list(auth)
    if dbname:
        args.append(dbname)
    args.append('-NBe')
    args.append(query)
    try:
        return subprocess.check_output(args,
                                       universal_newlines=True,
                                       stderr=subprocess.PIPE)
    except subprocess.CalledProcessError as e:
        logging.exception('failed to run %r: %s', query, e.stderr)
        raise


def mysqldump(*,
              container_check: bool = True,
              dbname: Optional[str] = None,
              auth: Sequence[str] = ()) -> bytes:
    '''Runs the mysqldump commandline tool.'''
    args = []
    if container_check and not inside_container():
        args.extend(['docker compose', 'exec', '-T', 'frontend'])
    args += [_MYSQLDUMP_BINARY] + list(auth)
    if dbname:
        args.append(dbname)
    with tempfile.NamedTemporaryFile(mode='rb') as outfile:
        args.extend([
            '--no-data',
            '--skip-comments',
            '--skip-opt',
            '--create-options',
            '--single-transaction',
            '--routines',
            '--default-character-set=utf8',
            '--result-file',
            outfile.name,
        ])
        subprocess.check_call(args)
        return outfile.read()
