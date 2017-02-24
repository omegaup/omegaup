#!/usr/bin/python3

'''
Runs the PHP Code Beautifier against files that will be uploaded through git.
'''

import argparse
import git_tools
import io
import os.path
import pipes
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
  args = git_tools.parse_arguments(tool_description='PHP linter',
        file_whitelist=[br'^frontend.*\.php$'],
        file_blacklist=[br'.*third_party.*'])
  if not args.files:
    return 0

  root = git_tools.root_dir()
  phpcs_args = [which('phpcbf'), '--encoding=utf-8',
      '--standard=%s' % os.path.join(root, 'stuff/phpcbf/Standards/OmegaUp/ruleset.xml')]

  validate_only = args.tool == 'validate'
  file_violations = set()

  for filename in args.files:
    contents = git_tools.file_contents(args, root, filename)
    cmd = phpcs_args + ['--stdin-path=%s' % filename]
    if args.verbose:
      print('Executing "%s".' % (
            ' '.join(pipes.quote(arg) for arg in cmd)), file=sys.stderr)
    with subprocess.Popen(cmd, stdin=subprocess.PIPE, stdout=subprocess.PIPE,
        cwd=root) as p:
      replaced = p.communicate(contents)[0]
      if p.returncode != 0 and not replaced:
        # phpcbf returns 1 if there was no change to the file. If there was an
        # actual error, there won't be anything in stdout.
        file_violations.add(filename)
        print('Execution of "%s" %sfailed with return code %d%s.' % (
              ' '.join(cmd), COLORS.FAIL, COLORS.NORMAL), file=sys.stderr)
    if contents != replaced:
      file_violations.add(filename)
      if validate_only:
        print('File %s%s%s has %slint errors%s.' % (COLORS.HEADER, filename,
          COLORS.NORMAL, COLORS.FAIL, COLORS.NORMAL), file=sys.stderr)
      else:
        print('Fixing %s%s%s for %slint errors%s.' % (COLORS.HEADER, filename,
          COLORS.NORMAL, COLORS.FAIL, COLORS.NORMAL), file=sys.stderr)
        with open(os.path.join(root, filename), 'wb') as f:
          f.write(replaced)

  if file_violations:
    if validate_only:
      if git_tools.attempt_automatic_fixes(sys.argv[0], args, file_violations):
        return 1
      print('%sPHP validation errors.%s '
            'Please run `%s` to fix them.' % (git_tools.COLORS.FAIL,
              git_tools.COLORS.NORMAL,
              git_tools.get_fix_commandline(sys.argv[0], args, file_violations)),
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
