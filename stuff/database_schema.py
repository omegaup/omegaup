#!/usr/bin/python3
# type: ignore
# -*- coding: utf-8 -*-

'''Tool to validate schema.sql.'''

from __future__ import print_function

import os.path
import re
import subprocess
import sys

import database_utils
from hook_tools import git_tools

OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))


def _expected_database_schema(*, config_file=None, username=None,
                              password=None, verbose=False):
    '''Runs mysqldump and removes the AUTO_INCREMENT annotation.'''
    args = [os.path.join(OMEGAUP_ROOT, 'stuff/db-migrate.py')]
    if config_file:
        args.extend(['--mysql-config-file', config_file])
    if username:
        args.extend(['--username', username])
    if password:
        args.extend(['--password', password])
    args.append('schema')
    stderr = subprocess.DEVNULL
    if verbose:
        stderr = None
    schema = subprocess.check_output(args, stderr=stderr)
    return re.sub(br'AUTO_INCREMENT=\d+\s+', b'', schema)


def strip_mysql_extensions(sql):
    '''Strips MySQL extension comments.'''
    return re.sub(br'/\*!([^*]|\*[^/])*\*/', b'', sql,
                  flags=re.MULTILINE | re.DOTALL)


def main():
    '''Runs the linters against the chosen files.'''

    args = git_tools.parse_arguments(
        tool_description='validates schema.sql',
        extra_arguments=[
            git_tools.Argument(
                '--mysql-config-file',
                default=database_utils.default_config_file(),
                help='.my.cnf file that stores credentials'),
            git_tools.Argument(
                '--database', default='omegaup', help='MySQL database'),
            git_tools.Argument(
                '--username', default='root', help='MySQL root username'),
            git_tools.Argument(
                '--password', default='omegaup', help='MySQL password')])

    # If running in an automated environment, we can close stdin.
    # This will disable all prompts.
    if (args.continuous_integration
            or os.environ.get('CONTINUOUS_INTEGRATION') == 'true'):
        sys.stdin.close()

    validate_only = args.tool == 'validate'

    filtered_files = list(filename for filename in args.files if
                          filename.endswith('.sql'))
    if not filtered_files:
        return

    root = git_tools.root_dir()
    expected = _expected_database_schema(config_file=args.mysql_config_file,
                                         username=args.username,
                                         password=args.password,
                                         verbose=args.verbose)
    actual = git_tools.file_contents(
        args, root, 'frontend/database/schema.sql')

    if (strip_mysql_extensions(expected.strip()) != strip_mysql_extensions(
            actual.strip())):
        if validate_only:
            if git_tools.attempt_automatic_fixes(sys.argv[0], args,
                                                 filtered_files):
                sys.exit(1)
            print('%sschema.sql validation errors.%s '
                  'Please run `%s` to fix them.' % (
                      git_tools.COLORS.FAIL, git_tools.COLORS.NORMAL,
                      git_tools.get_fix_commandline(sys.argv[0], args,
                                                    filtered_files)),
                  file=sys.stderr)
        else:
            with open(os.path.join(root,
                                   'frontend/database/schema.sql'), 'wb') as f:
                f.write(expected)
            print('Files written to working directory. '
                  '%sPlease commit them before pushing.%s' % (
                      git_tools.COLORS.HEADER, git_tools.COLORS.NORMAL),
                  file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
