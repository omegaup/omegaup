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

PIP_PATH = '/usr/bin/pip'
CLANG_FORMAT_PATH = '/usr/bin/clang-format-3.7'
FIXJSSTYLE_PATH = os.path.join(os.environ['HOME'],
                               '.local/bin/fixjsstyle')
GJSLINT_PATH = os.path.join(os.environ['HOME'], '.local/bin/gjslint')

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
      f.seek(0, 0)

      if validate_only:
        try:
          output = subprocess.check_output([
            GJSLINT_PATH, '--nojsdoc', '--quiet',
            f.name])
        except subprocess.CalledProcessError as e:
          print('File %s%s%s:\n%s' % (COLORS.HEADER, filename, COLORS.NORMAL,
            str(b'\n'.join(e.output.split(b'\n')[1:]),
              encoding='utf-8')), file=sys.stderr)
          validation_passed = False
      else:
        previous_outputs = set("")
        while True:
          output = subprocess.check_output([FIXJSSTYLE_PATH, '--strict', f.name])
          if output in previous_outputs:
            break
          previous_outputs.add(output)
        subprocess.check_call([CLANG_FORMAT_PATH, '-style=Google',
                               '-assume-filename=%s' % filename,
                               '-i', f.name])
        with open(f.name, 'rb') as f2:
          new_contents = f2.read()
        if contents != new_contents:
          print('Fixing %s%s%s' % (COLORS.HEADER, filename,
            COLORS.NORMAL), file=sys.stderr)
          with open(os.path.join(root, filename), 'wb') as o:
            o.write(new_contents)
          validation_passed = False
  return validation_passed

def main():
  if not git_tools.verify_toolchain({
    PIP_PATH: 'sudo apt-get install python-pip',
    CLANG_FORMAT_PATH: 'sudo apt-get install clang-format-3.7',
    GJSLINT_PATH: 'pip install --user https://github.com/google/closure-linter/zipball/master'
  }):
    sys.exit(1)
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
