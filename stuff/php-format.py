#!/usr/bin/python3

'''
Runs the PHP Code Beautifier against files that will be uploaded through git.
'''

import argparse
import git_tools
import io
import os.path
import subprocess
import sys

from git_tools import COLORS

def which(program):
  '''Looks for |program| in $PATH. Similar to UNIX's `which` command.'''
  for path in os.environ["PATH"].split(os.pathsep):
    exe_file = os.path.join(path.strip('"'), program)
    if os.path.isfile(exe_file) and os.access(exe_file, os.X_OK):
      return exe_file
  raise Exception('`%s` not found' % program)

def main():
  parser = argparse.ArgumentParser(description='PHP linter')
  subparsers = parser.add_subparsers(dest='tool')

  validate_parser = subparsers.add_parser('validate',
      help='Only validates, does not make changes')
  validate_parser.add_argument('commits', metavar='commit', nargs='*',
      type=str, help='Only include files changed between commits')

  fix_parser = subparsers.add_parser('fix',
      help='Fixes all violations and leaves the results in the working tree.')
  fix_parser.add_argument('commits', metavar='commit', nargs='*',
      type=str, help='Only include files changed between commits')

  args = parser.parse_args()
  if not git_tools.validate_args(args):
    return 1

  changed_files = git_tools.changed_files(args.commits,
      whitelist=[br'^frontend.*\.php$'],
      blacklist=[br'.*third_party.*', br'.*dao/base.*',
                 br'frontend/server/libs/dao/Estructura.php',
                 br'frontend/server/libs/dao/model.inc.php'])
  if not changed_files:
    return 0

  root = git_tools.root_dir()
  phpcs_args = [which('phpcbf'), '--encoding=utf-8',
      '--standard=%s' % os.path.join(root, 'stuff/omegaup-standard.xml')]

  validate_only = args.tool == 'validate'
  validation_passed = True

  for filename in changed_files:
    filename = str(filename, encoding='utf-8')
    contents = git_tools.file_at_commit(args.commits[1], filename)
    cmd = phpcs_args + ['--stdin-path=%s' % filename]
    with subprocess.Popen(cmd, stdin=subprocess.PIPE, stdout=subprocess.PIPE,
        cwd=root) as p:
      replaced = p.communicate(contents)[0]
      if p.returncode != 0 and not replaced:
        # phpcbf returns 1 if there was no change to the file. If there was an
        # actual error, there won't be anything in stdout.
        validation_passed = False
        print('Execution of "%s" %sfailed with return code %d%s.' % (
              ' '.join(cmd), COLORS.FAIL, COLORS.NORMAL), file=sys.stderr)
    if contents != replaced:
      validation_passed = False
      if validate_only:
        print('File %s%s%s has %slint errors%s.' % (COLORS.HEADER, filename,
          COLORS.NORMAL, COLORS.FAIL, COLORS.NORMAL), file=sys.stderr)
      else:
        print('Fixing %s%s%s for %slint errors%s.' % (COLORS.HEADER, filename,
          COLORS.NORMAL, COLORS.FAIL, COLORS.NORMAL), file=sys.stderr)
        with open(os.path.join(root, filename), 'wb') as f:
          f.write(replaced)

  if not validation_passed:
    if validate_only:
      print('%sPHP validation errors.%s '
            'Please run `%s fix %s` to fix them.' % (git_tools.COLORS.FAIL,
              git_tools.COLORS.NORMAL, sys.argv[0], ' '.join(args.commits)),
              file=sys.stderr)
    else:
      print('Files written to working directory. '
          '%sPlease commit them before pushing.%s' % (COLORS.HEADER,
          COLORS.NORMAL), file=sys.stderr)
    return 1
  return 0

if __name__ == '__main__':
  sys.exit(main())

# vim: expandtab shiftwidth=2 tabstop=2
