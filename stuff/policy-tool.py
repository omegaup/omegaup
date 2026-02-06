#!/usr/bin/env python3
# pylint: disable=invalid-name
# This program is intended to be invoked from the console, not to be used as a
# module.
'''
A tool that helps validate policy updates.

This tool is used for both developers to ensure that every time a policy is
updated, a new entry is added to the database.

Developers will only need to invoke this like:

    stuff/policy-tool.py update

every time a policy file has been updated. Travis can also use the 'validate',
command to validate that the latest policy is present in the database.
'''

from __future__ import print_function

import argparse
import os.path
import subprocess
import sys

from typing import Generator, Sequence, Tuple

import database_utils

OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))


def _latest() -> Generator[Tuple[str, str], None, None]:
    '''Gets the latest versions of all privacy statements.'''

    git_privacy_path = 'frontend/privacy'
    privacy_path = os.path.join(OMEGAUP_ROOT, git_privacy_path)
    if not os.path.isdir(privacy_path):
        return
    for statement_type in os.listdir(privacy_path):
        statement_path = os.path.join(git_privacy_path, statement_type)
        git_object_id = subprocess.check_output(
            ['/usr/bin/git', 'ls-tree', '-d', 'HEAD^{tree}', statement_path],
            cwd=OMEGAUP_ROOT,
            universal_newlines=True).strip().split()[2]
        yield (statement_type, git_object_id)


def _missing(
        args: argparse.Namespace,
        auth: Sequence[str],
) -> Generator[Tuple[str, str], None, None]:
    '''Gets all the missing privacy statements.'''

    for statement_type, git_object_id in _latest():
        if int(
                database_utils.mysql(
                    f'SELECT COUNT(*) FROM `PrivacyStatements` WHERE '
                    f'`type` = "{statement_type}" AND '
                    f'`git_object_id` = "{git_object_id}";',
                    dbname=args.database,
                    auth=auth,
                    container_check=not args.skip_container_check,
                )) != 0:
            continue
        yield (statement_type, git_object_id)


def validate(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Validates that the latest statements are present in the database.'''

    valid = True
    for statement_type, git_object_id in _missing(args, auth):
        print(f'Missing database entry for type {statement_type} '
              f'and object id {git_object_id}')
        valid = False
    if not valid:
        print('Run `./stuff/policy-tool.py upgrade` to generate '
              'the upgrade script.')
        sys.exit(1)


def upgrade(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Creates the database upgrade script for the latest policies.'''

    missing = list(_missing(args, auth))
    if not missing:
        return

    print('-- PrivacyStatements')
    print('INSERT INTO `PrivacyStatements` (`type`, `git_object_id`) VALUES ')
    values = ','.join(
        f"  ('{entry[0]}', '{entry[1]}')" for entry in missing)
    print(values + ';')


def _main() -> None:
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser()
    parser.add_argument('--skip-container-check',
                        action='store_true',
                        help='Skip the container check')
    parser.add_argument('--mysql-config-file',
                        default=database_utils.default_config_file(),
                        help='.my.cnf file that stores credentials')
    parser.add_argument('--database', default='omegaup', help='MySQL database')
    parser.add_argument('--hostname',
                        default=None,
                        type=str,
                        help='Hostname of the MySQL server')
    parser.add_argument('--port',
                        default=13306,
                        type=int,
                        help='Port of the MySQL server')
    parser.add_argument('--username',
                        default='root',
                        help='MySQL root username')
    parser.add_argument('--password', default='omegaup', help='MySQL password')
    subparsers = parser.add_subparsers(dest='command')
    subparsers.required = True

    # Commands for development.
    parser_validate = subparsers.add_parser(
        'validate', help='Validates that the versioning is sane')
    parser_validate.set_defaults(func=validate)

    parser_upgrade = subparsers.add_parser('upgrade',
                                           help='Generates the upgrade script')
    parser_upgrade.set_defaults(func=upgrade)

    args = parser.parse_args()

    if not args.skip_container_check:
        database_utils.check_inside_container()

    auth = database_utils.authentication(config_file=args.mysql_config_file,
                                         username=args.username,
                                         password=args.password,
                                         hostname=args.hostname,
                                         port=args.port)
    args.func(args, auth)


if __name__ == '__main__':
    _main()

# vim: expandtab shiftwidth=4 tabstop=4
