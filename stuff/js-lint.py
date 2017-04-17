#!/usr/bin/python3

'''
Runs clang-format and the Google Closure Compiler on all
JavaScript files.
'''

import argparse
import git_tools
import lint_tools
import os
import os.path
import re
import subprocess
import sys

from git_tools import COLORS

def run_linter(args, files, validate_only):
  '''Runs the Google Closure Compiler linter+prettier against |files|.'''
  root = git_tools.root_dir()
  file_violations = set()
  for filename in files:
    contents = git_tools.file_contents(args, root, filename)
    try:
      new_contents = lint_tools.lint_javascript(filename, contents)
    except subprocess.CalledProcessError as e:
      print('File %s%s%s lint failed:\n%s' % (COLORS.FAIL,
        filename, COLORS.NORMAL, str(b'\n'.join(e.output.split(b'\n')[1:]),
          encoding='utf-8')), file=sys.stderr)
      validation_passed = False
      continue
    if contents != new_contents:
      validation_passed = False
      if validate_only:
        print('File %s%s%s lint failed' %
              (COLORS.HEADER, filename, COLORS.NORMAL), file=sys.stderr)
      else:
        print('Fixing %s%s%s' % (COLORS.HEADER, filename,
          COLORS.NORMAL), file=sys.stderr)
        with open(os.path.join(root, filename), 'wb') as o:
          o.write(new_contents)
  return file_violations

def main():
  args = git_tools.parse_arguments(tool_description='lints javascript',
        file_whitelist=[br'^frontend/www/(js|ux)/.*\.js$'],
        file_blacklist=[br'.*third_party.*', br'.*js/omegaup/lang\..*'])

  if not args.files:
    return 0

  validate_only = args.tool == 'validate'

  file_violations = run_linter(args, args.files, validate_only)
  if file_violations:
    if validate_only:
      if git_tools.attempt_automatic_fixes(sys.argv[0], args, file_violations):
        return 1
      print('%sValidation errors.%s '
            'Please run `%s` to fix them.' % (COLORS.FAIL,
            COLORS.NORMAL,
            git_tools.get_fix_commandline(sys.argv[0], args, file_violations)),
            file=sys.stderr)
    else:
      print('Files written to working directory. '
            '%sPlease commit them before pushing.%s' %
            (COLORS.HEADER, COLORS.NORMAL), file=sys.stderr)
    return 1
  return 0

if __name__ == '__main__':
  sys.exit(main())

# vim: expandtab shiftwidth=2 tabstop=2
