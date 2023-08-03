#!/usr/bin/env python3
'''Library of common database code shared across cron scripts.

Using this library consists of two parts:
- Configuring a command line parser with configure_parser.
- Getting a DB connection using arguments from the command line.
'''

import argparse
import configparser
import contextlib
import getpass
import os
from typing import (overload, ContextManager, Generator, Literal, NamedTuple,
                    Optional, Union)

import mysql.connector


class DatabaseConnectionArguments(NamedTuple):
    '''Arguments for database connection.'''
    host: str
    user: str
    password: str
    mysql_config_file: str
    database: str
    port: int

    @staticmethod
    def from_args(args: argparse.Namespace) -> 'DatabaseConnectionArguments':
        '''Converts arguments to a named tuple for the database connection'''
        return DatabaseConnectionArguments(
            host=args.host,
            user=args.user,
            password=args.password,
            mysql_config_file=args.mysql_config_file,
            database=args.database,
            port=args.port,
        )

    @staticmethod
    def from_args_readonly(
            args: argparse.Namespace) -> 'DatabaseConnectionArguments':
        '''Converts arguments to a named tuple for the database connection'''
        return DatabaseConnectionArguments(
            host=args.readonly_host,
            user=args.readonly_user,
            password=args.readonly_password,
            mysql_config_file=args.mysql_config_file,
            database=args.readonly_database,
            port=args.readonly_port,
        )


class Connection:
    '''A MySQL connection.'''

    def __init__(self, dbconn: mysql.connector.MySQLConnection) -> None:
        self.conn = dbconn

    @overload
    def cursor(
        self,
        *,
        buffered: Literal[True],
        dictionary: Literal[False] = ...,
    ) -> ContextManager[mysql.connector.cursor.MySQLCursorBuffered]:
        ...

    @overload
    def cursor(
        self,
        *,
        buffered: Literal[False] = ...,
        dictionary: Literal[True],
    ) -> ContextManager[mysql.connector.cursor.MySQLCursorDict]:
        ...

    @overload
    def cursor(
        self,
        *,
        buffered: Literal[True],
        dictionary: Literal[True],
    ) -> ContextManager[mysql.connector.cursor.MySQLCursorBufferedDict]:
        ...

    @overload
    def cursor(
        self,
        *,
        buffered: Literal[False] = ...,
        dictionary: Literal[False] = ...,
    ) -> ContextManager[mysql.connector.cursor.MySQLCursor]:
        ...

    # mypy and contextmanagers have an outstanding bad relationship :/
    @contextlib.contextmanager  # type: ignore
    def cursor(
        self,
        *,
        buffered: bool = False,
        dictionary: bool = False,
    ) -> Union[
        Generator[mysql.connector.cursor.MySQLCursorBuffered, None, None],
        Generator[mysql.connector.cursor.MySQLCursorDict, None, None],
        Generator[mysql.connector.cursor.MySQLCursorBufferedDict, None, None],
        Generator[mysql.connector.cursor.MySQLCursor, None, None],
    ]:
        '''Returns a context manager for a MySQL cursor.'''
        cursor = self.conn.cursor(buffered=buffered, dictionary=dictionary)
        try:
            yield cursor
        finally:
            cursor.close()


def default_config_file_path() -> Optional[str]:
    '''Try to autodetect the config file path.'''
    for candidate_path in (
            # ~/.my.cnf
            os.path.join(os.getenv('HOME') or '.', '.my.cnf'), ):
        if os.path.isfile(candidate_path):
            return candidate_path
    return None


def configure_parser(parser: argparse.ArgumentParser) -> None:
    '''Add DB-related arguments to `parser`'''
    db_args = parser.add_argument_group('DB Access')
    db_args.add_argument('--mysql-config-file',
                         type=str,
                         default=default_config_file_path(),
                         help='.my.cnf file that stores credentials')

    db_args.add_argument('--host',
                         type=str,
                         help='MySQL host',
                         default='localhost')
    db_args.add_argument('--user', type=str, help='MySQL username')
    db_args.add_argument('--password', type=str, help='MySQL password')
    db_args.add_argument('--database',
                         type=str,
                         help='MySQL database',
                         default='omegaup')
    db_args.add_argument('--port', type=int, help='MySQL port', default=13306)

    db_args.add_argument('--readonly-host',
                         type=str,
                         help='Read-only MySQL host')
    db_args.add_argument('--readonly-user',
                         type=str,
                         help='Read-only MySQL username')
    db_args.add_argument('--readonly-password',
                         type=str,
                         help='MySQL password')
    db_args.add_argument('--readonly-database',
                         type=str,
                         help='Read-only MySQL database',
                         default='omegaup')
    db_args.add_argument('--readonly-port',
                         type=int,
                         help='Read-only MySQL port',
                         default=3306)


def connect(args: DatabaseConnectionArguments) -> Connection:
    '''Connects to MySQL with the arguments provided.

    Returns a MySQL connection.
    '''
    port = args.port
    host = args.host
    user = args.user
    password = args.password
    database = args.database
    if (user is None and args.mysql_config_file
            and os.path.isfile(args.mysql_config_file)):
        config = configparser.ConfigParser()
        config.read(args.mysql_config_file)
        # Puppet quotes some configuration entries.
        port = int(config['client']['port'].strip("'"))
        host = config['client']['host'].strip("'")
        user = config['client']['user'].strip("'")
        password = config['client']['password'].strip("'")
    if password is None:
        password = getpass.getpass()

    assert user is not None, 'Missing --user parameter'
    assert host is not None, 'Missing --host parameter'
    assert password is not None, 'Missing --password parameter'
    assert database is not None, 'Missing --database parameter'

    return Connection(
        mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database,
            port=port,
        ))


def connect_readonly(
        args: DatabaseConnectionArguments) -> Optional[Connection]:
    '''Connects to the read-only MySQL replica with the arguments provided.

    Returns a MySQL connection, or None if the read-only replica is not
    configured. The caller can use the regular MySQL connection instead.
    '''
    port = args.port
    host = args.host
    user = args.user
    password = args.password
    database = args.database
    if (user is None and args.mysql_config_file
            and os.path.isfile(args.mysql_config_file)):
        config = configparser.ConfigParser()
        config.read(args.mysql_config_file)
        # Puppet quotes some configuration entries.
        if 'clientreadonly' in config:
            port = int(config['clientreadonly']['port'].strip("'"))
            host = config['clientreadonly']['host'].strip("'")
            user = config['clientreadonly']['user'].strip("'")
            password = config['clientreadonly']['password'].strip("'")
            database = config['clientreadonly']['database'].strip("'")

    if not port or not host or not user or not password or not database:
        return None

    return Connection(
        mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database,
            port=port,
        ))


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
