#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Tool to validate schema.sql.'''

from __future__ import print_function

import logging
import os.path
import re
import subprocess
import sys

import database_utils
import hook_tools.git_tools as git_tools


def _expected_database_schema(*, dbname='omegaup', auth=None):
    '''Runs mysqldump and removes the AUTO_INCREMENT annotation.'''
    schema = database_utils.mysqldump(dbname=dbname, auth=auth)
    return re.sub(br'AUTO_INCREMENT=\d+\s+', b'', schema)


def strip_mysql_extensions(sql):
    '''Strips MySQL extension comments.'''
    return re.sub(br'/\*!([^*]|\*[^/])*\*/', b'', sql,
                  flags=re.MULTILINE|re.DOTALL)


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
    if (args.continuous_integration or
            os.environ.get('CONTINUOUS_INTEGRATION') == 'true'):
        sys.stdin.close()

    validate_only = args.tool == 'validate'

    filtered_files = list(filename for filename in args.files if
                          filename.endswith('.sql'))
    if not filtered_files:
        return

    auth = database_utils.authentication(config_file=args.mysql_config_file,
                                         username=args.username,
                                         password=args.password)
    root = git_tools.root_dir()
    expected = _expected_database_schema(dbname=args.database, auth=auth)
    actual = git_tools.file_contents(
            args, root, 'frontend/database/schema.sql')

    if (strip_mysql_extensions(expected.strip()) !=
        strip_mysql_extensions(actual.strip())):
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
