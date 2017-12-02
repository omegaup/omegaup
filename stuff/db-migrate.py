#!/usr/bin/python3

'''
A tool that helps perform database schema migrations.

This tool is used for both Puppet and developers to perform database schema
migrations.  It performs the migrations in an idempotent way by tracking the
last revision that was applied to the database.  In order to avoid tracking
this metadata in the database this script will be modifying, a table called
`Revision` will be created in a database called `_omegaup_migrations`.

Developers will only need to invoke this like:

  stuff/db-migrate.py migrate --development-environment

every time a new script has been added to the repository.  Puppet can also use
the 'exists', 'latest', and 'migrate' commands to perform the migrations
automatically.
'''

import argparse
import os.path
import sys

import database_utils


OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))


def _revision(args, auth):
  '''
  Returns the latest revision that has been applied to the database. Returns 0
  if no revision has been applied.
  '''
  ensure(args, auth)
  return int(database_utils.mysql(
        'SELECT COALESCE(MAX(id), 0) FROM `Revision`;',
        dbname='_omegaup_metadata', auth=auth).strip())


def _scripts(args):
  '''
  Returns the list of scripts in the frontend/database/ directory in the
  omegaUp checkout, ordered by revision.
  '''
  scripts = []
  scripts_dir = os.path.join(OMEGAUP_ROOT, 'frontend', 'database')
  for filename in os.listdir(scripts_dir):
    if not filename.endswith('.sql'):
      continue
    parts = filename.split('_', 1)
    if len(parts) != 2 or not all(x.isdigit() for x in parts[0]):
      continue
    scripts.append(
        (int(parts[0]), parts[1], os.path.join(scripts_dir, filename)))
  scripts.sort()
  return scripts


def exists(args, auth):
  '''
  A helper command for puppet. Exits with 1 (error) if the metadata database
  has not been installed.
  '''
  if not database_utils.mysql('SHOW DATABASES LIKE "_omegaup_metadata";',
                              auth=auth):
    sys.exit(1)
  if not database_utils.mysql('SHOW TABLES LIKE "Revision";',
                              dbname='_omegaup_metadata', auth=auth):
    sys.exit(1)


def latest(args, auth):
  '''
  A helper command for puppet. Exits with 1 (error) if the latest script in the
  checkout has not been applied to the database.
  '''
  if _revision(args, auth) < _scripts(args)[-1][0]:
    sys.exit(1)


def migrate(args, auth, update_metadata=True):
  '''
  Performs the database schema migration.  This command applies all scripts
  that have not yet been applied in order, and records their application in the
  metadata database.  This command is idempotent and can be run any number of
  times.
  '''
  latest_revision = 0
  if update_metadata:
    latest_revision = _revision(args, auth)
  for revision, name, path in _scripts(args):
    if latest_revision >= revision:
      continue
    if args.limit and revision > args.limit:
      break
    if args.noop:
      sys.stderr.write('Installing %s\n' % path)
    else:
      comment = "migrate"
      if name.startswith('test_') and not args.development_environment:
        comment = "skipped"
      else:
        for dbname in args.databases.split(','):
          database_utils.mysql('source %s;' % database_utils.quote(path),
                               dbname=dbname, auth=auth)
      if update_metadata:
        database_utils.mysql(
            'INSERT INTO `Revision` VALUES(%d, CURRENT_TIMESTAMP, "%s");' %
            (revision, comment), dbname='_omegaup_metadata', auth=auth)


def ensure(args, auth):
  '''
  Creates both the metadata database and table, if they don't exist yet.
  '''
  database_utils.mysql('CREATE DATABASE IF NOT EXISTS `_omegaup_metadata`;',
                       auth=auth)
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
      '`comment` VARCHAR(50));', dbname='_omegaup_metadata', auth=auth)


def reset(args, auth):
  '''
  Forces the metadata table to be in a particular revision.  Note that this
  does not apply or unapply any changes to the actual database, so use this
  only for testing or recovering a botched migration!
  '''
  ensure(args, auth)
  database_utils.mysql(
      'DELETE FROM `Revision` WHERE `id` >= %d;' % args.revision,
      dbname='_omegaup_metadata', auth=auth)
  if args.revision > 0:
    database_utils.mysql(
        'INSERT INTO `Revision` VALUES(%d, CURRENT_TIMESTAMP, "manual reset");'
        % args.revision, dbname='_omegaup_metadata', auth=auth)


def print_revision(args, auth):
  '''
  Prints the current revision.
  '''
  print(_revision(args, auth))


def purge(args, auth):
  '''
  Use purge to start from scratch - Drops & re-creates databases including the
  metadata. Note that purge will not re-apply the schema.
  '''
  for dbname in args.databases.split(','):
    database_utils.mysql('DROP DATABASE IF EXISTS `%s`;' % dbname, auth=auth)
    database_utils.mysql('CREATE DATABASE `%s` CHARACTER SET UTF8 COLLATE '
                         'utf8_general_ci;' % dbname, auth=auth)

def schema(args, auth):
  '''
  Prints the schema without modifying the usual database tables.

  This does touch the database, but is restricted to a dummy database `_omegaup_schema`.
  '''
  _SCHEMA_DB = '_omegaup_schema'
  args.databases = _SCHEMA_DB
  args.noop = False
  args.development_environment = False
  purge(args, auth)
  migrate(args, auth, update_metadata=False)
  sys.stdout.buffer.write(database_utils.mysqldump(dbname=_SCHEMA_DB, auth=auth))
  database_utils.mysql('DROP DATABASE `%s`;' % _SCHEMA_DB, auth=auth)


def main():
  parser = argparse.ArgumentParser()
  parser.add_argument('--mysql-config-file',
      default=database_utils.default_config_file(),
      help='.my.cnf file that stores credentials')
  parser.add_argument('--username', default='root', help='MySQL root username')
  parser.add_argument('--password', default='omegaup', help='MySQL password')
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
  parser_migrate.add_argument('--noop', action='store_true',
                              help='Only print scripts that would be installed')
  parser_migrate.add_argument('--development-environment',
                              dest='development_environment',
                              action='store_true',
                              help='Installs scripts flagged as for testing')
  parser_migrate.add_argument('--databases', default='omegaup,omegaup-test',
                              help='Comma-separated list of databases')
  parser_migrate.add_argument('--limit', type=int,
                              help='Last revision to include')
  parser_migrate.set_defaults(func=migrate)

  # Commands for development.
  parser_ensure = subparsers.add_parser(
      'ensure', help='Ensures that the migration table exists')
  parser_ensure.set_defaults(func=ensure)

  parser_reset = subparsers.add_parser(
      'reset', help='Resets the migration table to a particular revision')
  parser_reset.add_argument('revision', help='The desired revision', type=int)
  parser_reset.set_defaults(func=reset)

  parser_revision = subparsers.add_parser(
      'revision', help='Gets the current revision')
  parser_revision.set_defaults(func=print_revision)

  parser_purge = subparsers.add_parser(
      'purge', help='Start from scratch - Drop & Create empty databases')
  parser_purge.add_argument('--databases', default='omegaup,omegaup-test,_omegaup_metadata',
                            help='Comma-separated list of databases')
  parser_purge.set_defaults(func=purge)

  parser_schema = subparsers.add_parser(
      'schema', help=('Show the database schema. Does not actually read/write '
                      'from the database'))
  parser_schema.add_argument('--limit', type=int,
                             help='Last revision to include')
  parser_schema.set_defaults(func=schema)

  args = parser.parse_args()
  auth = database_utils.authentication(config_file=args.mysql_config_file,
                                       username=args.username,
                                       password=args.password)
  args.func(args, auth)


if __name__ == '__main__':
  main()


# vim: expandtab shiftwidth=2 tabstop=2
