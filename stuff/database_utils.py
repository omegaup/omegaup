#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Library of utilities to work with MySQL.'''


import os
import shlex
import subprocess
import tempfile
from typing import Optional, Sequence


_MYSQL_BINARY = '/usr/bin/mysql'
_MYSQLDUMP_BINARY = '/usr/bin/mysqldump'


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
            # ${OMEGAUP_ROOT}/.my.cnf
            os.path.join(
                os.path.abspath(
                    os.path.join(os.path.dirname(__file__), '..')),
                '.my.cnf'),
            # ~/.my.cnf
            os.path.join(os.getenv('HOME') or '.', '.my.cnf'),
            '/etc/mysql/conf.d/mysql_password.cnf',
    ):
        if os.path.isfile(candidate_path):
            return candidate_path
    return None


def authentication(*,
                   config_file: Optional[str] = default_config_file(),
                   username: Optional[str] = None,
                   password: Optional[str] = None) -> Sequence[str]:
    '''Computes the authentication arguments for mysql binaries.'''
    if config_file and os.path.isfile(config_file):
        return ['--defaults-file=%s' % quote(config_file)]
    assert username
    args = ['--user=%s' % quote(username)]
    if password:
        args.append('--password=%s' % quote(password))
    return args


def mysql(query: str,
          *,
          dbname: Optional[str] = None,
          auth: Sequence[str] = ()) -> str:
    '''Runs the MySQL commandline client with |query| as query.'''
    args = [_MYSQL_BINARY] + list(auth)
    if dbname:
        args.append(dbname)
    args.append('-NBe')
    args.append(query)
    return subprocess.check_output(args, universal_newlines=True)


def mysqldump(*,
              dbname: Optional[str] = None,
              auth: Sequence[str] = ()) -> bytes:
    '''Runs the mysqldump commandline tool.'''
    args = [_MYSQLDUMP_BINARY] + list(auth)
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
