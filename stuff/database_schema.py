#!/usr/bin/env python3
# -*- coding: utf-8 -*-

'''Tool to validate schema.sql.'''

from __future__ import print_function

import argparse
import difflib
import os.path
import re
import subprocess
import sys
from typing import Optional

from omegaup_hook_tools import git_tools

import database_utils

OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))

_SCHEMA_FILENAME = 'frontend/database/schema.sql'


def _check_mutually_exclusive_schema_modifications(
        *,
        args: argparse.Namespace,
        root: str,
) -> bool:
    '''Ensures that schema.sql and dao_schema.sql are not modified together.'''
    merge_base = subprocess.run(
        [
            '/usr/bin/git',
            'rev-parse',
            '--abbrev-ref',
            '--symbolic-full-name',
            '@{u}',
        ],
        check=False,
        universal_newlines=True,
        stdout=subprocess.PIPE,
        cwd=root,
    ).stdout.strip() or 'origin/main'
    modified_files = set(
        filename.decode('utf-8') for filename in subprocess.run(
            [
                '/usr/bin/git',
                '--no-pager',
                'diff',
                '-z',
                '--name-only',
                merge_base,
            ],
            check=True,
            stdout=subprocess.PIPE,
            cwd=root).stdout.strip(b'\x00').split(b'\x00'))
    schema_sql_filename = 'frontend/database/schema.sql'
    dao_schema_sql_filename = 'frontend/database/dao_schema.sql'
    schema_sql_modified = schema_sql_filename in modified_files
    dao_schema_sql_modified = dao_schema_sql_filename in modified_files

    if not schema_sql_modified and not dao_schema_sql_modified:
        # Neither file got modified, all's good.
        return True
    if schema_sql_modified and dao_schema_sql_modified:
        # Welp, both files got modified, this is bad.
        print((f'{git_tools.COLORS.FAIL}{schema_sql_filename!r} and '
               f'{dao_schema_sql_filename!r} cannot be modified in '
               f'the same commit.{git_tools.COLORS.NORMAL}'),
              file=sys.stderr)
        return False
    if schema_sql_modified:
        # This is okay. Only the schema.sql file was modified. The rest of this
        # script will validate whether it has the correct contents.
        return True
    schema_sql = git_tools.file_contents(args, root, schema_sql_filename)
    dao_schema_sql = git_tools.file_contents(args, root,
                                             dao_schema_sql_filename)
    if schema_sql != dao_schema_sql:
        print((f'{git_tools.COLORS.FAIL}{dao_schema_sql_filename!r} can only '
               f'have the same contents as {schema_sql_filename!r}.'
               f'{git_tools.COLORS.NORMAL}'),
              file=sys.stderr)
        return False

    return True


def _expected_database_schema(*,
                              skip_container_check: bool = False,
                              config_file: Optional[str] = None,
                              username: Optional[str] = None,
                              password: Optional[str] = None,
                              hostname: Optional[str] = None,
                              port: Optional[int] = None,
                              verbose: bool = False) -> bytes:
    '''Runs mysqldump and removes the AUTO_INCREMENT annotation.'''
    args = [os.path.join(OMEGAUP_ROOT, 'stuff/db-migrate.py')]
    if skip_container_check:
        args.extend(['--skip-container-check'])
    if config_file:
        args.extend(['--mysql-config-file', config_file])
    if username is not None:
        args.extend(['--username', username])
    if password is not None:
        args.extend(['--password', password])
    if hostname is not None:
        args.extend(['--hostname', hostname])
    if port is not None:
        args.extend(['--port', str(port)])
    args.append('schema')
    stderr: Optional[int] = subprocess.DEVNULL
    if verbose:
        stderr = None
    schema = subprocess.check_output(args, stderr=stderr)
    return re.sub(br'AUTO_INCREMENT=\d+\s+', b'', schema)


def strip_mysql_extensions(sql: bytes) -> bytes:
    '''Strips MySQL extension comments.'''
    return re.sub(br'/\*!([^*]|\*[^/])*\*/', b'', sql,
                  flags=re.MULTILINE | re.DOTALL)


def main() -> None:
    '''Runs the linters against the chosen files.'''

    args = git_tools.parse_arguments(
        tool_description='validates schema.sql',
        extra_arguments=[
            git_tools.Argument(
                '--skip-container-check',
                action='store_true',
                help='Skip the container check'),
            git_tools.Argument(
                '--mysql-config-file',
                default=database_utils.default_config_file(),
                help='.my.cnf file that stores credentials'),
            git_tools.Argument(
                '--database', default='omegaup', help='MySQL database'),
            git_tools.Argument(
                '--hostname', default=None, type=str,
                help='Hostname of the MySQL server'),
            git_tools.Argument(
                '--port', default=13306, type=int,
                help='Port of the MySQL server'),
            git_tools.Argument(
                '--username', default='root', help='MySQL root username'),
            git_tools.Argument(
                '--password', default='omegaup', help='MySQL password')])

    if not args.skip_container_check:
        database_utils.check_inside_container()

    validate_only = args.tool == 'validate'

    filtered_files = list(filename for filename in args.files if
                          filename.endswith('.sql'))

    root = git_tools.root_dir()
    if not _check_mutually_exclusive_schema_modifications(
            args=args,
            root=root,
    ):
        sys.exit(1)
    if 'frontend/database/dao_schema.sql' in filtered_files:
        filtered_files.remove('frontend/database/dao_schema.sql')
    if not filtered_files:
        return

    expected = _expected_database_schema(
        skip_container_check=args.skip_container_check,
        config_file=args.mysql_config_file,
        username=args.username,
        password=args.password,
        hostname=args.hostname,
        port=args.port,
        verbose=args.verbose,
    )
    actual = git_tools.file_contents(args, root, _SCHEMA_FILENAME)

    expected_contents = strip_mysql_extensions(expected.strip())
    actual_contents = strip_mysql_extensions(actual.strip())

    if expected_contents != actual_contents:
        if validate_only:
            if git_tools.attempt_automatic_fixes(sys.argv[0], args,
                                                 filtered_files):
                sys.exit(1)
            sys.stderr.writelines(
                difflib.unified_diff(
                    actual_contents.decode('utf-8').splitlines(keepends=True),
                    expected_contents.decode('utf-8').splitlines(
                        keepends=True),
                    fromfile=_SCHEMA_FILENAME,
                    tofile=_SCHEMA_FILENAME))
            print((f'{git_tools.COLORS.FAIL}schema.sql validation '
                   f'errors.{git_tools.COLORS.NORMAL} '
                   'Please run '
                   f'`{git_tools.get_fix_commandline(args, filtered_files)}` '
                   'to fix them.'),
                  file=sys.stderr)
        else:
            with open(os.path.join(root, 'frontend/database/schema.sql'),
                      'wb') as f:
                f.write(expected)
            print((f'Files written to working directory. '
                   f'{git_tools.COLORS.HEADER}Please commit them '
                   f'before pushing.{git_tools.COLORS.NORMAL}'),
                  file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
