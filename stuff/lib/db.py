#!/usr/bin/python3

'''Library of common database code shared across cron scripts.

Using this library consists of two parts:
- Configuring a command line parser with configure_parser.
- Getting a DB connection using arguments from the command line.
'''

import argparse
import configparser
import getpass
import os
from typing import Optional

import MySQLdb
import MySQLdb.connections


def default_config_file_path() -> Optional[str]:
    '''Try to autodetect the config file path.'''
    for candidate_path in (
            # ${OMEGAUP_ROOT}/.my.cnf
            os.path.join(
                os.path.abspath(
                    os.path.join(os.path.dirname(__file__), '..', '..')),
                '.my.cnf'),
            # ~/.my.cnf
            os.path.join(os.getenv('HOME') or '.', '.my.cnf'),
            '/etc/mysql/conf.d/mysql_password.cnf',
    ):
        if os.path.isfile(candidate_path):
            return candidate_path
    return None


def configure_parser(parser: argparse.ArgumentParser) -> None:
    '''Add DB-related arguments to `parser`'''
    db_args = parser.add_argument_group('DB Access')
    db_args.add_argument('--mysql-config-file', type=str,
                         default=default_config_file_path(),
                         help='.my.cnf file that stores credentials')
    db_args.add_argument('--host', type=str, help='MySQL host',
                         default='localhost')
    db_args.add_argument('--user', type=str, help='MySQL username')
    db_args.add_argument('--password', type=str, help='MySQL password')
    db_args.add_argument('--database', type=str, help='MySQL database',
                         default='omegaup')


def connect(args: argparse.Namespace) -> MySQLdb.connections.Connection:
    '''Connects to MySQL with the arguments provided.

    Returns a MySQLdb connection.
    '''
    host = args.host
    user = args.user
    password = args.password
    if user is None and os.path.isfile(args.mysql_config_file):
        config = configparser.ConfigParser()
        config.read(args.mysql_config_file)
        # Puppet quotes some configuration entries.
        host = config['client']['host'].strip("'")
        user = config['client']['user'].strip("'")
        password = config['client']['password'].strip("'")
    if password is None:
        password = getpass.getpass()

    assert user is not None, 'Missing --user parameter'
    assert host is not None, 'Missing --host parameter'
    assert password is not None, 'Missing --password parameter'

    dbconn = MySQLdb.connect(
        host=host,
        user=user,
        passwd=password,
        db=args.database
    )
    dbconn.autocommit(False)
    return dbconn


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
