#!/usr/bin/env python3
'''Unit tests for the database connection helpers.'''

import os
import sys
import tempfile

from typing import Any, Callable, Dict, Iterator

from unittest import mock

import pytest

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db  # pylint: disable=wrong-import-position

ConfigFileFactory = Callable[[str], str]


@pytest.fixture(name='config_file')
def _config_file() -> Iterator[ConfigFileFactory]:
    '''Writes a my.cnf with the given password in both sections.'''
    with tempfile.TemporaryDirectory() as directory:

        def write(password: str) -> str:
            path = os.path.join(directory, 'my.cnf')
            with open(path, 'w', encoding='utf-8') as config:
                config.write('[client]\n'
                             'host=localhost\n'
                             'port=13306\n'
                             'user=omegaup\n'
                             f'password={password}\n'
                             '[clientreadonly]\n'
                             'host=replica\n'
                             'port=13307\n'
                             'user=reader\n'
                             f"password='{password}'\n"
                             'database=omegaup\n')
            return path

        yield write


def _connect_kwargs(connect: Callable[[lib.db.DatabaseConnectionArguments],
                                      Any],
                    config_file: str) -> Dict[str, Any]:
    '''Returns what the helper handed to mysql.connector.connect.'''
    # No user, so the helpers read everything from the file.
    arguments = lib.db.DatabaseConnectionArguments(
        host=None,
        user=None,
        password=None,
        mysql_config_file=config_file,
        database='omegaup',
        port=3306,
    )
    with mock.patch('mysql.connector.connect') as mysql_connect:
        connect(arguments)
    kwargs: Dict[str, Any] = mysql_connect.call_args.kwargs
    return kwargs


@pytest.mark.parametrize('password', ['omegaup', 'pa%ss%(word)s'])
def test_connect_reads_the_password(config_file: ConfigFileFactory,
                                    password: str) -> None:
    '''`%` is an escape to configparser's default interpolation.'''
    kwargs = _connect_kwargs(lib.db.connect, config_file(password))

    assert kwargs['password'] == password
    assert kwargs['user'] == 'omegaup'
    assert kwargs['host'] == 'localhost'
    assert kwargs['port'] == 13306


@pytest.mark.parametrize('password', ['omegaup', 'pa%ss%(word)s'])
def test_connect_readonly_reads_the_password(config_file: ConfigFileFactory,
                                             password: str) -> None:
    '''The replica section goes through the same parser.'''
    kwargs = _connect_kwargs(lib.db.connect_readonly, config_file(password))

    assert kwargs['password'] == password
    assert kwargs['user'] == 'reader'
    assert kwargs['host'] == 'replica'
    assert kwargs['port'] == 13307
