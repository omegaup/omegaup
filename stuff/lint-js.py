#!/usr/bin/python3

'''
Runs clang-format and the Google Closure Compiler on all
JavaScript files.
'''

import argparse
import git_tools
import os
import os.path
import re
import subprocess
import sys
import tempfile

from git_tools import COLORS

def run_linter(commits, files, validate_only):
  '''Runs the Google Closure Compiler linter against |files| in |commits|.
  '''
  root = git_tools.root_dir()
  validation_passed = True
  for filename in files:
    contents = git_tools.file_at_commit(commits, filename)

    with tempfile.NamedTemporaryFile(suffix='.js') as f:
      f.write(contents)
      f.flush()

      if validate_only:
        try:
          output = subprocess.check_output([
            os.path.join(os.environ['HOME'], '.local/bin/gjslint'),
            '--nojsdoc', '--strict', '--quiet', f.name])
        except subprocess.SubprocessError as e:
          print('File %s%s%s:\n%s' % (COLORS.HEADER, filename, COLORS.NORMAL,
            str(b'\n'.join(e.output.split(b'\n')[1:]),
              encoding='utf-8')), file=sys.stderr)
          validation_passed = False
      else:
        subprocess.check_call(['/usr/bin/clang-format-3.7', '-i', f.name])
        subprocess.check_call([
          os.path.join(os.environ['HOME'],
            '.local/bin/fixjsstyle'), '--strict',
          f.name])
        with open(f.name, 'rb') as f2:
          new_contents = f2.read()
        if contents != new_contents:
          validation_passed = False
          with open(os.path.join(root, filename), 'wb') as o:
            o.write(new_contents)
  return validation_passed

def main():
  args = git_tools.parse_arguments(tool_description='lints javascript')

  if args.files:
    changed_files = args.files
  else:
    changed_files = git_tools.changed_files(args.commits,
        whitelist=[br'^frontend/www/(js|ux)/.*\.js$'],
        blacklist=[br'.*third_party.*', br'.*js/omegaup/lang\..*'])
  if not changed_files:
    return 0

  validate_only = args.tool == 'validate'

  if not run_linter(args.commits, changed_files, validate_only):
    if validate_only:
      print('%sValidation errors.%s '
            'Please run `%s` to fix them.' % (COLORS.FAIL,
            COLORS.NORMAL, git_tools.get_fix_commandline(sys.argv[0], args)),
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
