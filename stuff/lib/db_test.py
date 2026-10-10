#!/usr/bin/env python3
'''Unit tests for the database connection helpers.'''

import os
import sys
import tempfile

from typing import Any, Dict, Iterator
from unittest import mock

import pytest

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db  # pylint: disable=wrong-import-position

_PASSWORD = 'pa%ss%(word)s'


@pytest.fixture(name='config_file')
def _config_file() -> Iterator[str]:
    '''A my.cnf whose passwords hold configparser's escape character.'''
    with tempfile.NamedTemporaryFile(mode='w', suffix='.cnf') as config:
        config.write('[client]\n'
                     'host=localhost\n'
                     'port=13306\n'
                     'user=omegaup\n'
                     f'password={_PASSWORD}\n'
                     '[clientreadonly]\n'
                     'host=replica\n'
                     'port=13307\n'
                     'user=reader\n'
                     f"password='{_PASSWORD}'\n"
                     'database=omegaup\n')
        config.flush()
        yield config.name


def _arguments(config_file: str) -> lib.db.DatabaseConnectionArguments:
    return lib.db.DatabaseConnectionArguments(
        host=None, user=None, password=None,  # type: ignore
        mysql_config_file=config_file, database='omegaup', port=3306)


def _connect_kwargs(connect: Any, config_file: str) -> Dict[str, Any]:
    '''Returns what the helper handed to mysql.connector.connect.'''
    with mock.patch('mysql.connector.connect') as mysql_connect:
        connect(_arguments(config_file))
    kwargs: Dict[str, Any] = mysql_connect.call_args.kwargs
    return kwargs


def test_connect_reads_a_password_with_a_percent_sign(
        config_file: str) -> None:
    '''`%` is an escape to configparser's default interpolation.'''
    kwargs = _connect_kwargs(lib.db.connect, config_file)

    assert kwargs['password'] == _PASSWORD
    assert kwargs['user'] == 'omegaup'
    assert kwargs['host'] == 'localhost'
    assert kwargs['port'] == 13306


def test_connect_readonly_reads_a_password_with_a_percent_sign(
        config_file: str) -> None:
    '''The replica section goes through the same parser.'''
    kwargs = _connect_kwargs(lib.db.connect_readonly, config_file)

    assert kwargs['password'] == _PASSWORD
    assert kwargs['user'] == 'reader'
    assert kwargs['host'] == 'replica'
    assert kwargs['port'] == 13307
