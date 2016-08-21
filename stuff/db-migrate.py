#!/usr/bin/python3

import argparse
import os.path
import shlex
import subprocess
import sys


MYSQL_BINARY = '/usr/bin/mysql'
OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))


def _quote(s):
  if 'quote' in dir(shlex):
    # This is unavailable in Python <3.3
    return shlex.quote(s)
  import pipes
  return pipes.quote(s)


def _mysql(args, mysql_args):
  auth = []
  if os.path.isfile(args.config_file):
    auth.append('--defaults-extra-file=%s' % _quote(args.config_file))
  else:
    auth.append('--user=%s' % _quote(args.username))
    if args.password:
      auth.append('--password=%s' % _quote(args.password))
  return subprocess.check_output(
      [MYSQL_BINARY] + auth + mysql_args,
      universal_newlines=True)


def _revision(args):
  ensure(args)
  return int(
      _mysql(args,
             ['_omegaup_metadata', '-NBe',
               'SELECT COALESCE(MAX(id), 0) FROM `Revision`;']).strip())


def _scripts(args):
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


def exists(args):
  if not _mysql(args, ['-NBe', 'SHOW DATABASES LIKE "_omegaup_metadata";']):
    sys.exit(1)
  if not _mysql(args, ['_omegaup_metadata', '-NBe', 'SHOW TABLES LIKE "Revision";']):
    sys.exit(1)


def latest(args):
  if _revision(args) != _scripts(args)[-1][0]:
    sys.exit(1)


def migrate(args):
  latest_revision = _revision(args)
  for revision, name, path in _scripts(args):
    if latest_revision >= revision:
      continue
    if args.noop:
      sys.stderr.write('Installing %s\n' % path)
    else:
      comment = "migrate"
      if name.startswith('test_') and not args.development_environment:
        comment = "skipped"
      else:
        _mysql(args, ['omegaup', '-NBe', 'source %s;' % _quote(path)])
        _mysql(args, ['omegaup-test', '-NBe', 'source %s;' % _quote(path)])
      _mysql(args, ['_omegaup_metadata', '-NBe',
        'INSERT INTO `Revision` VALUES(%d, CURRENT_TIMESTAMP, "%s");' %
        (revision, comment)])


def ensure(args):
  _mysql(args, [
    '-NBe', 'CREATE DATABASE IF NOT EXISTS `_omegaup_metadata`;'])
  _mysql(args, [
    '_omegaup_metadata', '-NBe', 'CREATE TABLE IF NOT EXISTS `Revision`'
    '(`id` INTEGER NOT NULL PRIMARY KEY, '
    '`applied` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, '
    '`comment` VARCHAR(50));'])


def reset(args):
  ensure(args)
  _mysql(args, ['_omegaup_metadata', '-NBe',
                'DELETE FROM `Revision` WHERE `id` >= %d;' % args.revision])
  if args.revision > 0:
    _mysql(args, ['_omegaup_metadata', '-NBe',
      'INSERT INTO `Revision` VALUES(%d, CURRENT_TIMESTAMP, "manual reset");' %
      args.revision])


def print_revision(args):
  print(_revision(args))


def main():
  parser = argparse.ArgumentParser()
  parser.add_argument('--config-file', dest='config_file',
      default=os.path.join(os.getenv('HOME') or '.', '.my.cnf'),
      help='.my.cnf file that stores credentials')
  parser.add_argument('--username', default='root', help='MySQL root username')
  parser.add_argument('--password', default='omegaup', help='MySQL password')
  subparsers = parser.add_subparsers()

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

  args = parser.parse_args()
  args.func(args)

if __name__ == '__main__':
  main()

# vim: expandtab shiftwidth=2 tabstop=2
