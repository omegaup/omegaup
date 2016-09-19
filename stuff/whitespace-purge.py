#!/usr/bin/python3

'''
Removes annoying superfluous whitespace.
'''

import argparse
import git_tools
import os
import re
import subprocess
import sys

from git_tools import COLORS

VALIDATIONS = [
  ('Windows-style EOF', re.compile(br'\r'), br'\n'),
  ('trailing whitespace', re.compile(br'[ \t]+\n'), br'\n'),
  ('consecutive empty lines', re.compile(br'\n\n\n+'), br'\n\n'),
  ('empty lines after an opening brace', re.compile(br'{\n\n+'), br'{\n'),
  ('empty lines before a closing brace',
   re.compile(br'\n+\n(\s*})'), br'\n\1'),
]

def run_validations(commit, files, validate_only):
  '''Runs all validations against |files| in |commit|.

  A validation consists of performing regex substitution against the contents
  of each file in |files|, at the git commit |commit|.  Validation fails if the
  resulting content is not identical to the original.  The contents of the
  files will be presented as a single string, allowing for multi-line matches.
  '''
  root = git_tools.root_dir()
  validation_passed = True
  for filename in files:
    filename = str(filename, encoding='utf-8')
    contents = git_tools.file_at_commit(commit, filename)
    violations = []

    # Run all validations sequentially, so all violations can be fixed
    # together.
    for error_string, search, replace in VALIDATIONS:
      replaced = search.sub(replace, contents)
      if replaced != contents:
        violations.append(error_string)
        contents = replaced

    if violations:
      validation_passed = False
      violations_message = ', '.join('%s%s%s' % (COLORS.FAIL, violation,
        COLORS.NORMAL) for violation in violations)
      if validate_only:
        print('File %s%s%s has %s.' % (COLORS.HEADER, filename, COLORS.NORMAL,
          violations_message), file=sys.stderr)
      else:
        print('Fixing %s%s%s for %s.' % (COLORS.HEADER, filename, COLORS.NORMAL,
          violations_message), file=sys.stderr)
        with open(os.path.join(root, filename), 'wb') as f:
          f.write(replaced)
  return validation_passed

def main():
  parser = argparse.ArgumentParser(description='purge whitespace')
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
      whitelist=[br'^frontend.*\.(php|css|js|sql|tpl|py)$'],
      blacklist=[br'.*third_party.*', br'.*dao/base.*'])
  if not changed_files:
    return 0

  validate_only = args.tool == 'validate'

  if not run_validations(args.commits[1], changed_files, validate_only):
    if validate_only:
      print('%sWhitespace validation errors.%s '
            'Please run `%s fix %s` to fix them.' % (COLORS.FAIL,
            COLORS.NORMAL, sys.argv[0], ' '.join(args.commits)),
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
