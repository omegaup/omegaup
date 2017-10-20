#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Library of utilities to work with MySQL.'''


import os
import shlex
import subprocess


_MYSQL_BINARY = '/usr/bin/mysql'
_MYSQLDUMP_BINARY = '/usr/bin/mysqldump'


def quote(s):
    '''Escapes the string |s| so it can be safely used in a shell command.'''
    if 'quote' in dir(shlex):
        # This is unavailable in Python <3.3
        return shlex.quote(s)
    import pipes
    return pipes.quote(s)


def default_config_file():
    '''Returns the default config file path for MySQL.'''
    return os.path.join(os.getenv('HOME') or '.', '.my.cnf')


def authentication(*, config_file=default_config_file(), username=None,
                   password=None):
    '''Computes the authentication arguments for mysql binaries.'''
    if config_file and os.path.isfile(config_file):
        return ['--defaults-extra-file=%s' % quote(config_file)]
    else:
        assert username
        args = ['--user=%s' % quote(username)]
        if password:
            args.append('--password=%s' % quote(password))
        return args


def mysql(query, *, dbname=None, auth=None):
    '''Runs the MySQL commandline client with |query| as query.'''
    args = [_MYSQL_BINARY] + auth
    if dbname:
        args.append(dbname)
    args.append('-NBe')
    args.append(query)
    return subprocess.check_output(args, universal_newlines=True)


def mysqldump(*, dbname=None, auth=None):
    '''Runs the mysqldump commandline tool.'''
    args = [_MYSQLDUMP_BINARY] + auth
    if dbname:
        args.append(dbname)
    args.extend(['--no-data', '--skip-comments', '--skip-opt',
                 '--create-options', '--single-transaction', '--routines'])
    return subprocess.check_output(args, universal_newlines=True)