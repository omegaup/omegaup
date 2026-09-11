#!/usr/bin/env python3
'''Unit tests for database_utils.'''

import os
import subprocess
import sys
import unittest
from unittest import mock

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import database_utils


class DatabaseUtilsTest(unittest.TestCase):
    '''Test cases for database_utils module.'''

    def test_inside_container_true(self) -> None:
        '''inside_container returns True when /opt/omegaup exists.'''
        with mock.patch('os.path.isdir', return_value=True):
            self.assertTrue(database_utils.inside_container())

    def test_inside_container_false(self) -> None:
        '''inside_container returns False when /opt/omegaup is missing.'''
        with mock.patch('os.path.isdir', return_value=False):
            self.assertFalse(database_utils.inside_container())

    def test_check_inside_container_when_inside(self) -> None:
        '''check_inside_container returns if inside container.'''
        with mock.patch.object(
            database_utils, 'inside_container', return_value=True
        ):
            with mock.patch('subprocess.run') as mock_run:
                database_utils.check_inside_container()
                mock_run.assert_not_called()

    def test_check_inside_container_user_declines(self) -> None:
        '''check_inside_container exits 1 if user answers n.'''
        with mock.patch.object(
            database_utils, 'inside_container', return_value=False
        ):
            with mock.patch('sys.stdin.isatty', return_value=True):
                with mock.patch('builtins.input', return_value='n'):
                    with self.assertRaises(SystemExit) as exc_info:
                        database_utils.check_inside_container()
                    self.assertEqual(exc_info.exception.code, 1)

    def test_check_inside_container_user_accepts(self) -> None:
        '''check_inside_container runs separate docker compose args on y.'''
        fake_completed = mock.Mock(
            spec=subprocess.CompletedProcess, returncode=0
        )
        with mock.patch.object(
            database_utils, 'inside_container', return_value=False
        ):
            with mock.patch('sys.stdin.isatty', return_value=True):
                with mock.patch('builtins.input', return_value='y'):
                    with mock.patch(
                        'subprocess.run', return_value=fake_completed
                    ) as mock_run:
                        with mock.patch.object(
                            sys, 'argv', ['db-migrate.py', 'validate']
                        ):
                            with self.assertRaises(SystemExit) as exc_info:
                                database_utils.check_inside_container()
                            self.assertEqual(exc_info.exception.code, 0)
                            mock_run.assert_called_once_with(
                                [
                                    'docker',
                                    'compose',
                                    'exec',
                                    '-T',
                                    'frontend',
                                    'db-migrate.py',
                                    'validate',
                                ],
                                check=False,
                            )

    def test_mysql_outside_container(self) -> None:
        '''mysql() prepends separate docker compose arguments.'''
        with mock.patch.object(
            database_utils, 'inside_container', return_value=False
        ):
            with mock.patch(
                'subprocess.check_output', return_value='1\n'
            ) as mock_output:
                result = database_utils.mysql(
                    'SELECT 1;',
                    container_check=True,
                    dbname='omegaup',
                    auth=['--user=test'],
                )
                self.assertEqual(result, '1\n')
                mock_output.assert_called_once_with(
                    [
                        'docker',
                        'compose',
                        'exec',
                        '-T',
                        'frontend',
                        '/usr/bin/mysql',
                        '--user=test',
                        'omegaup',
                        '-NBe',
                        'SELECT 1;',
                    ],
                    universal_newlines=True,
                    stderr=subprocess.PIPE,
                )

    def test_mysql_inside_container(self) -> None:
        '''mysql() does not prepend docker compose when inside.'''
        with mock.patch.object(
            database_utils, 'inside_container', return_value=True
        ):
            with mock.patch(
                'subprocess.check_output', return_value='1\n'
            ) as mock_output:
                result = database_utils.mysql(
                    'SELECT 1;', container_check=True
                )
                self.assertEqual(result, '1\n')
                mock_output.assert_called_once_with(
                    ['/usr/bin/mysql', '-NBe', 'SELECT 1;'],
                    universal_newlines=True,
                    stderr=subprocess.PIPE,
                )

    def test_mysqldump_outside_container(self) -> None:
        '''mysqldump() prepends separate docker compose arguments.'''
        with mock.patch.object(
            database_utils, 'inside_container', return_value=False
        ):
            with mock.patch('subprocess.check_call') as mock_call:
                with mock.patch('tempfile.NamedTemporaryFile') as mock_temp:
                    mock_file = mock.MagicMock()
                    mock_file.name = '/tmp/fake.sql'
                    mock_file.read.return_value = b'CREATE TABLE test;'
                    mock_temp.return_value.__enter__.return_value = mock_file

                    dump_data = database_utils.mysqldump(
                        container_check=True,
                        dbname='omegaup',
                        auth=['--user=test'],
                    )
                    self.assertEqual(dump_data, b'CREATE TABLE test;')
                    mock_call.assert_called_once()
                    called_args = mock_call.call_args[0][0]
                    self.assertEqual(
                        called_args[:5],
                        ['docker', 'compose', 'exec', '-T', 'frontend'],
                    )
                    self.assertEqual(called_args[5], '/usr/bin/mysqldump')
                    self.assertIn('--user=test', called_args)
                    self.assertIn('omegaup', called_args)

    def test_mysqldump_inside_container(self) -> None:
        '''mysqldump() does not prepend docker compose when inside.'''
        with mock.patch.object(
            database_utils, 'inside_container', return_value=True
        ):
            with mock.patch('subprocess.check_call') as mock_call:
                with mock.patch('tempfile.NamedTemporaryFile') as mock_temp:
                    mock_file = mock.MagicMock()
                    mock_file.name = '/tmp/fake.sql'
                    mock_file.read.return_value = b'CREATE TABLE test;'
                    mock_temp.return_value.__enter__.return_value = mock_file

                    dump_data = database_utils.mysqldump(container_check=True)
                    self.assertEqual(dump_data, b'CREATE TABLE test;')
                    mock_call.assert_called_once()
                    called_args = mock_call.call_args[0][0]
                    self.assertEqual(called_args[0], '/usr/bin/mysqldump')
                    self.assertNotIn('docker', called_args)

    def test_quote(self) -> None:
        '''quote() escapes special shell characters.'''
        self.assertEqual(database_utils.quote('simple'), 'simple')
        self.assertIn(
            database_utils.quote('with space'),
            ("'with space'", '"with space"'),
        )

    def test_authentication_with_config_file(self) -> None:
        '''authentication() returns --defaults-file when file exists.'''
        with mock.patch('os.path.isfile', return_value=True):
            auth_flags = database_utils.authentication(
                config_file='/tmp/my.cnf'
            )
            self.assertIn(
                auth_flags,
                (
                    ["--defaults-file='/tmp/my.cnf'"],
                    ['--defaults-file=/tmp/my.cnf'],
                ),
            )

    def test_authentication_with_credentials(self) -> None:
        '''authentication() returns user, password, host, and port flags.'''
        with mock.patch('os.path.isfile', return_value=False):
            auth_flags = database_utils.authentication(
                config_file=None,
                username='dbuser',
                password='secretpassword',
                hostname='dbhost',
                port=3306,
            )
            self.assertTrue(
                "--user='dbuser'" in auth_flags
                or '--user=dbuser' in auth_flags
            )
            self.assertTrue(
                "--password='secretpassword'" in auth_flags
                or '--password=secretpassword' in auth_flags
            )
            self.assertIn('--protocol=TCP', auth_flags)
            self.assertTrue(
                "--host='dbhost'" in auth_flags
                or '--host=dbhost' in auth_flags
            )
            self.assertIn('--port=3306', auth_flags)


if __name__ == '__main__':
    unittest.main()
