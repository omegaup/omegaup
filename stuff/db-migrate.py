#!/usr/bin/env python3
# pylint: disable=invalid-name
# This program is intended to be invoked from the console, not to be used as a
# module.
'''
A tool that helps perform database schema migrations.

This tool is used for both Puppet and developers to perform database schema
migrations. It performs the migrations in an idempotent way by tracking the
last revision that was applied to the database. In order to avoid tracking
this metadata in the database this script will be modifying, a table called
`Revision` will be created in a database called `_omegaup_migrations`.

Developers will only need to invoke this like:

    stuff/db-migrate.py migrate --development-environment

every time a new script has been added to the repository. Puppet can also
use the 'exists', 'latest', and 'migrate' commands to perform the migrations
automatically.
'''

from __future__ import print_function

import argparse
import contextlib
import logging
import os.path
import subprocess
import sys
import time
from typing import Iterator, List, Optional, Sequence, Tuple

import boto3  # type: ignore

import database_utils

OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.realpath(__file__)), "."))

import lib.logs  # pylint: disable=wrong-import-position


_BLOCKING_PROCESSES_QUERY = '''
SELECT DISTINCT
    PROCESSLIST_ID
FROM
    performance_schema.metadata_locks
INNER JOIN
    performance_schema.threads ON THREAD_ID = OWNER_THREAD_ID
WHERE
    PROCESSLIST_ID <> CONNECTION_ID() AND
    OBJECT_TYPE = 'TABLE' AND
    OBJECT_SCHEMA IN (%s);
'''


def _revision(args: argparse.Namespace, auth: Sequence[str]) -> int:
    '''Returns the latest revision that has been applied to the database.

    Returns 0 if no revision has been applied.
    '''
    ensure(args, auth)
    return int(
        database_utils.mysql(
            'SELECT COALESCE(MAX(id), 0) FROM `Revision`;',
            dbname='_omegaup_metadata',
            auth=auth,
            container_check=not args.skip_container_check,
        ).strip())


def _scripts() -> List[Tuple[int, str, str]]:
    '''
    Returns the list of scripts in the frontend/database/ directory in the
    omegaUp checkout, ordered by revision.
    '''
    scripts: List[Tuple[int, str, str]] = []
    scripts_dir = os.path.join(OMEGAUP_ROOT, 'frontend', 'database')
    for filename in os.listdir(scripts_dir):
        if not filename.endswith('.sql'):
            continue
        parts = filename.split('_', 1)
        if (len(parts) != 2 or
                len(parts[0]) != 5 or
                not all(x.isdigit() for x in parts[0])):
            continue
        scripts.append((int(parts[0], 10), parts[1],
                        os.path.join(scripts_dir, filename)))
    scripts.sort()
    return scripts


def _set_aws_rds_timeout(args: argparse.Namespace,
                         auth: Sequence[str],
                         timeout: Optional[int] = None) -> None:
    '''Set the MySQL through AWS RDS timeouts.'''
    del auth  # unused
    rds = boto3.client('rds')

    retry_limit = 6
    for i in range(retry_limit):
        try:
            if timeout is None:
                rds.reset_db_parameter_group(
                    DBParameterGroupName=args.aws_rds_parameter_group_name,
                    ResetAllParameters=False,
                    Parameters=[
                        {
                            'ApplyMethod': 'immediate',
                            'ParameterName': 'wait_timeout',
                        },
                    ],
                )
            else:
                rds.modify_db_parameter_group(
                    DBParameterGroupName=args.aws_rds_parameter_group_name,
                    Parameters=[
                        {
                            'ApplyMethod': 'immediate',
                            'ParameterName': 'wait_timeout',
                            'ParameterValue': '10',
                        },
                    ],
                )
            return
        except Exception as e:  # pylint: disable=broad-except
            if i == retry_limit - 1:
                raise
            logging.exception(
                'Could not modify MySQL parameter group, retrying... %r',
                type(e))
            time.sleep(10)


def _set_mysql_timeout(args: argparse.Namespace,
                       auth: Sequence[str],
                       timeout: Optional[int] = None) -> None:
    '''Set the MySQL timeouts.'''
    if timeout is None:
        timeout_str = 'DEFAULT'
    else:
        timeout_str = str(timeout)
    database_utils.mysql(
        f'SET GLOBAL interactive_timeout = {timeout_str};',
        dbname='mysql',
        auth=auth,
        container_check=not args.skip_container_check,
    )
    database_utils.mysql(
        f'SET GLOBAL wait_timeout = {timeout_str};',
        dbname='mysql',
        auth=auth,
        container_check=not args.skip_container_check,
    )


@contextlib.contextmanager
def _connection_timeout_wrapper(  # pylint: disable=too-many-arguments
        args: argparse.Namespace,
        auth: Sequence[str],
        databases: Sequence[str],
        aws: bool,
        lower_timeout: bool,
        kill_blocking_connections: bool = False) -> Iterator[None]:
    '''A context manager that temporarily lowers the wait timeout.

    This can also also optionally kill any existing connections to the
    database. By doing so, the next time they connect, they will use the
    lowered wait timeout, which in turn should make this script be able to grab
    any locks within ~10s.
    '''
    def _set_timeout(timeout: Optional[int]) -> None:
        if aws:
            _set_aws_rds_timeout(args, auth, timeout)
        else:
            _set_mysql_timeout(args, auth, timeout)
    try:
        if lower_timeout:
            logging.info('Lowering MySQL timeout...')
            _set_timeout(10)

        if kill_blocking_connections:
            logging.info('Killing all other MySQL connections...')
            for line in database_utils.mysql(
                    (_BLOCKING_PROCESSES_QUERY %
                     (', '.join(f'"{dbname}"' for dbname in databases))),
                    dbname='mysql',
                    auth=auth,
                    container_check=not args.skip_container_check,
            ).strip().split('\n'):
                if not line.strip():
                    continue
                try:
                    if aws:
                        database_utils.mysql(
                            f'CALL mysql.rds_kill({line.split()[0]});',
                            dbname='mysql',
                            auth=auth,
                            container_check=not args.skip_container_check,
                        )
                    else:
                        database_utils.mysql(
                            f'KILL {line.split()[0]};',
                            dbname='mysql',
                            auth=auth,
                            container_check=not args.skip_container_check,
                        )
                except subprocess.CalledProcessError:
                    # The command already logged the error.
                    pass
        else:
            # If we are not killing connections, at least sleep on it.
            time.sleep(10)

        yield
    finally:
        if lower_timeout:
            logging.info('Restoring MySQL timeout...')
            _set_timeout(None)


def exists(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Determines whether the metadata database is present.

    Exits with 1 (error) if the metadata database has not been installed.
    This is a helper command for Puppet.
    '''
    if not database_utils.mysql(
            'SHOW DATABASES LIKE "_omegaup_metadata";',
            auth=auth,
            container_check=not args.skip_container_check,
    ):
        sys.exit(1)
    if not database_utils.mysql(
            'SHOW TABLES LIKE "Revision";',
            dbname='_omegaup_metadata',
            auth=auth,
            container_check=not args.skip_container_check,
    ):
        sys.exit(1)


def latest(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Determines whether the latest revision is deployed.

    Exits with 1 (error) if the latest script in the checkout has not been
    applied to the database. This is a helper command for Puppet.
    '''
    if _revision(args, auth) < _scripts()[-1][0]:
        sys.exit(1)


def migrate(args: argparse.Namespace,
            auth: Sequence[str],
            update_metadata: bool = True) -> None:
    '''Performs the database schema migration.

    This command applies all scripts that have not yet been applied in order,
    and records their application in the metadata database. This command is
    idempotent and can be run any number of times.
    '''
    latest_revision = 0
    if update_metadata:
        latest_revision = _revision(args, auth)
    logging.info('Latest revision is %d', latest_revision)
    logging.info('Reading scripts... Update metadata: %r', update_metadata)
    scripts = _scripts()
    logging.info('Found %d scripts', len(scripts))
    if not scripts:
        # If there are no scripts that need to be run, there is no need to even
        # touch the connection timeout.
        logging.info('No scripts to run, exiting...')
        return

    databases = args.databases.split(',')
    with _connection_timeout_wrapper(
            args,
            auth,
            databases=databases,
            aws=args.aws,
            lower_timeout=args.lower_timeout,
            kill_blocking_connections=args.kill_blocking_connections):
        for revision, name, path in scripts:
            if latest_revision >= revision:
                continue
            if args.limit and revision > args.limit:
                break
            if args.noop:
                sys.stderr.write(f'Installing {path}\n')
                continue
            logging.info('Running script for revision %d...', revision)
            comment = "migrate"
            if name.startswith('test_') and not args.development_environment:
                comment = "skipped"
            else:
                for dbname in databases:
                    try:
                        # Start the transaction
                        database_utils.mysql(
                            'START TRANSACTION;',
                            dbname=dbname,
                            auth=auth,
                            container_check=not args.skip_container_check,
                        )
                        # Run the script
                        database_utils.mysql(
                            f'source {database_utils.quote(path)};',
                            dbname=dbname,
                            auth=auth,
                            container_check=not args.skip_container_check,
                        )
                        # Commit the transaction
                        database_utils.mysql(
                            'COMMIT;',
                            dbname=dbname,
                            auth=auth,
                            container_check=not args.skip_container_check,
                        )
                        logging.info('Transaction committed successfully for '
                                     'database: %s', dbname)

                    except subprocess.CalledProcessError as e:
                        # Rollback the transaction in case of error
                        database_utils.mysql(
                            'ROLLBACK;',
                            dbname=dbname,
                            auth=auth,
                            container_check=not args.skip_container_check,
                        )
                        logging.error('Transaction rolled back due to error '
                                      'in script %r: %s', path, e.stderr)
                        raise

            if update_metadata:
                database_utils.mysql(
                    ('INSERT INTO `Revision` '
                     'VALUES(%d, CURRENT_TIMESTAMP, "%s");') %
                    (revision, comment),
                    dbname='_omegaup_metadata',
                    auth=auth,
                    container_check=not args.skip_container_check,
                )
            logging.info('Done running script for revision %d', revision)


def validate(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Validates that the versioning is has no repeated or missing entries.'''
    del args, auth  # unused

    expected_revision = 0
    valid = True
    for revision, _, path in _scripts():
        expected_revision += 1
        if expected_revision != revision:
            print(
                f'Expected revision {expected_revision} for path {path}')
            valid = False
    if not valid:
        sys.exit(1)


def ensure(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Creates both the metadata database and table, if they don't exist yet.
    '''
    database_utils.mysql(
        'CREATE DATABASE IF NOT EXISTS `_omegaup_metadata`;',
        auth=auth,
        container_check=not args.skip_container_check,
    )
    # This is the table that tracks the migrations. |id| is the revision,
    # |applied| is the timestamp the operation was made and |comment| is a
    # human-readable comment about the migration. It can be either 'migrate' if
    # it was applied normally, 'skipped' if it was not applied due to not being
    # run in a development environment, and 'manual reset' if it was added as a
    # result of the 'reset' command.
    database_utils.mysql(
        'CREATE TABLE IF NOT EXISTS `Revision`'
        '(`id` INTEGER NOT NULL PRIMARY KEY, '
        '`applied` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, '
        '`comment` VARCHAR(50));',
        dbname='_omegaup_metadata',
        auth=auth,
        container_check=not args.skip_container_check,
    )


def reset(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Forces the metadata table to be in a particular revision.

    Note that this does not apply or unapply any changes to the actual
    database, so use this only for testing or recovering a botched migration!
    '''
    ensure(args, auth)
    database_utils.mysql(
        f'DELETE FROM `Revision` WHERE `id` >= {args.revision};',
        dbname='_omegaup_metadata',
        auth=auth,
        container_check=not args.skip_container_check,
    )
    if args.revision > 0:
        database_utils.mysql(
            (f'INSERT INTO `Revision` '
             f'VALUES({args.revision}, CURRENT_TIMESTAMP, "manual reset");'),
            dbname='_omegaup_metadata',
            auth=auth,
            container_check=not args.skip_container_check,
        )


def print_revision(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Prints the current revision.'''
    print(_revision(args, auth))


def purge(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Use purge to start from scratch.

    Drops & re-creates databases including the metadata. Note that purge will
    not re-apply the schema.
    '''
    databases = args.databases.split(',')
    with _connection_timeout_wrapper(
            args,
            auth,
            databases=databases,
            aws=args.aws,
            lower_timeout=args.lower_timeout,
            kill_blocking_connections=args.kill_blocking_connections):
        for dbname in databases:
            logging.info('Dropping database %s', dbname)
            database_utils.mysql(
                f'DROP DATABASE IF EXISTS `{dbname}`;',
                auth=auth,
                container_check=not args.skip_container_check,
            )
            logging.info('Creating database %s', dbname)
            database_utils.mysql(
                f'CREATE DATABASE `{dbname}` CHARACTER SET UTF8 COLLATE '
                'utf8_general_ci;',
                auth=auth,
                container_check=not args.skip_container_check,
            )
            logging.info('Done creating database %s', dbname)


def schema(args: argparse.Namespace, auth: Sequence[str]) -> None:
    '''Prints the schema without modifying the usual database tables.

    This does touch the database, but is restricted to a dummy database
    `_omegaup_schema`.
    '''
    _SCHEMA_DB = '_omegaup_schema'
    args.databases = _SCHEMA_DB
    args.noop = False
    args.development_environment = False
    purge(args, auth)
    migrate(args, auth, update_metadata=False)
    # This is a false positive.
    # pylint: disable=no-member
    sys.stdout.buffer.write(
        database_utils.mysqldump(
            dbname=_SCHEMA_DB,
            auth=auth,
            container_check=not args.skip_container_check,
        ))
    database_utils.mysql(
        f'DROP DATABASE `{_SCHEMA_DB}`;',
        auth=auth,
        container_check=not args.skip_container_check,
    )


def main() -> None:
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser()
    parser.add_argument(
        '--skip-container-check',
        action='store_true',
        help='Skip the container check')
    parser.add_argument(
        '--mysql-config-file',
        default=database_utils.default_config_file(),
        help='.my.cnf file that stores credentials')
    parser.add_argument(
        '--hostname', default=None, type=str,
        help='Hostname of the MySQL server')
    parser.add_argument(
        '--port', default=13306, type=int,
        help='Port of the MySQL server')
    parser.add_argument(
        '--username', default='root', help='MySQL root username')
    parser.add_argument('--password', default='omegaup', help='MySQL password')
    parser.add_argument('--aws-rds-parameter-group-name',
                        default='omegaup-frontend',
                        help=('The name of the Parameter Group name. '
                              'Required with --lower-timeout and --aws.'))
    parser.add_argument('--lower-timeout',
                        action='store_true',
                        help='Temporarily lower the wait timeout.')
    parser.add_argument(
        '--kill-blocking-connections',
        action='store_true',
        help='Kill all connections that hold a lock.')
    parser.add_argument(
        '--aws',
        action='store_true',
        help='Use AWS-specific commands.')
    subparsers = parser.add_subparsers(dest='command')
    subparsers.required = True

    # Commands for puppet.
    parser_exists = subparsers.add_parser(
        'exists', help='Checks if the migration table exists')
    parser_exists.set_defaults(func=exists)

    parser_latest = subparsers.add_parser(
        'latest', help='Checks if the database is at the latest revision')
    parser_latest.set_defaults(func=latest)

    parser_migrate = subparsers.add_parser(
        'migrate', help='Migrates the database to the latest revision')
    parser_migrate.add_argument(
        '--noop',
        action='store_true',
        help=('Only print scripts that would be '
              'installed'))
    parser_migrate.add_argument(
        '--development-environment',
        dest='development_environment',
        action='store_true',
        help='Installs scripts flagged as for testing')
    parser_migrate.add_argument(
        '--databases',
        default='omegaup,omegaup-test',
        help='Comma-separated list of databases')
    parser_migrate.add_argument(
        '--limit', type=int, help='Last revision to include')
    parser_migrate.set_defaults(func=migrate)

    # Commands for development.
    parser_validate = subparsers.add_parser(
        'validate', help='Validates that the versioning is sane')
    parser_validate.set_defaults(func=validate)

    parser_ensure = subparsers.add_parser(
        'ensure', help='Ensures that the migration table exists')
    parser_ensure.set_defaults(func=ensure)

    parser_reset = subparsers.add_parser(
        'reset', help='Resets the migration table to a particular revision')
    parser_reset.add_argument(
        'revision', help='The desired revision', type=int)
    parser_reset.set_defaults(func=reset)

    parser_revision = subparsers.add_parser(
        'revision', help='Gets the current revision')
    parser_revision.set_defaults(func=print_revision)

    parser_purge = subparsers.add_parser(
        'purge', help='Start from scratch - Drop & Create empty databases')
    parser_purge.add_argument(
        '--databases',
        default=('omegaup,omegaup-test,'
                 '_omegaup_metadata'),
        help='Comma-separated list of databases')
    parser_purge.set_defaults(func=purge)

    parser_schema = subparsers.add_parser(
        'schema',
        help=('Show the database schema. Does not actually '
              'read/write from the database'))
    parser_schema.add_argument(
        '--limit', type=int, help='Last revision to include')
    parser_schema.set_defaults(func=schema)

    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    if not args.skip_container_check:
        database_utils.check_inside_container()

    auth = database_utils.authentication(config_file=args.mysql_config_file,
                                         username=args.username,
                                         password=args.password,
                                         hostname=args.hostname,
                                         port=args.port)
    args.func(args, auth)


if __name__ == '__main__':
    main()
