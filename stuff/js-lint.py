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

def _find_pip_tool(name):
  '''Tries to find a pip tool in a few default locations.'''
  for prefix in ['/usr/bin', '/usr/local/bin']:
    toolpath = os.path.join(prefix, name)
    if os.path.exists(toolpath):
      return toolpath
  return os.path.join(os.environ['HOME'], '.local/bin', name)

PIP_PATH = '/usr/bin/pip'
CLANG_FORMAT_PATH = '/usr/bin/clang-format-3.7'
# TODO(lhchavez): Use closure compiler instead since closure-linter does not
# support ES6 correctly.
FIXJSSTYLE_PATH = _find_pip_tool('fixjsstyle')

def run_linter(args, files, validate_only):
  '''Runs the Google Closure Compiler linter against |files|.'''
  root = git_tools.root_dir()
  file_violations = set()
  for filename in files:
    contents = git_tools.file_contents(args, root, filename)

    with tempfile.NamedTemporaryFile(suffix='.js') as f:
      f.write(contents)
      f.flush()
      f.seek(0, 0)

      try:
        subprocess.check_output(['yarn', 'run', 'refactor', '--', f.name,
                                 '--assume-filename=%s' % filename],
                                stderr=subprocess.STDOUT)
        subprocess.check_output([FIXJSSTYLE_PATH, '--strict', f.name],
                                stderr=subprocess.STDOUT)
        subprocess.check_output([CLANG_FORMAT_PATH, '-style=Google',
                                 '-assume-filename=%s' % filename,
                                 '-i', f.name], stderr=subprocess.STDOUT)
      except subprocess.CalledProcessError as e:
        print('File %s%s%s lint failed:\n%s' %
              (COLORS.HEADER, filename, COLORS.NORMAL,
               str(b'\n'.join(e.output.split(b'\n')[1:]), encoding='utf-8')),
              file=sys.stderr)
        file_violations.add(filename)
      with open(f.name, 'rb') as f2:
        new_contents = f2.read()
      if contents != new_contents:
        file_violations.add(filename)
        if validate_only:
          print('File %s%s%s lint failed.' %
                (COLORS.HEADER, filename, COLORS.NORMAL),
                file=sys.stderr)
        else:
          print('Fixing %s%s%s' % (COLORS.HEADER, filename,
            COLORS.NORMAL), file=sys.stderr)
          with open(os.path.join(root, filename), 'wb') as o:
            o.write(new_contents)
  return file_violations

def main():
  if not git_tools.verify_toolchain({
    PIP_PATH: 'sudo apt-get install python-pip',
    CLANG_FORMAT_PATH: 'sudo apt-get install clang-format-3.7',
    FIXJSSTYLE_PATH: 'pip install --user https://github.com/google/closure-linter/zipball/master'
  }):
    sys.exit(1)
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
