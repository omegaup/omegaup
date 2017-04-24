#!/usr/bin/python3

import git_tools
import os
import os.path
import subprocess
import tempfile

def _find_pip_tool(name):
  '''Tries to find a pip tool in a few default locations.'''
  for prefix in ['/usr/bin', '/usr/local/bin']:
    toolpath = os.path.join(prefix, name)
    if os.path.exists(toolpath):
      return toolpath
  return os.path.join(os.environ['HOME'], '.local/bin', name)


_PIP_PATH = '/usr/bin/pip'
_CLANG_FORMAT_PATH = '/usr/bin/clang-format-3.7'
# TODO(lhchavez): Use closure compiler instead since closure-linter does not
# support ES6 correctly.
_FIXJSSTYLE_PATH = _find_pip_tool('fixjsstyle')
_TIDY_PATH = os.path.join(git_tools.OMEGAUP_ROOT, 'bin/tidy')

_JAVASCRIPT_TOOLCHAIN_VERIFIED = False

def lint_javascript(filename, contents):
  '''Runs clang-format and the Google Closure Compiler on |contents|.'''

  global _JAVASCRIPT_TOOLCHAIN_VERIFIED

  if not _JAVASCRIPT_TOOLCHAIN_VERIFIED and not git_tools.verify_toolchain({
    _PIP_PATH: 'sudo apt-get install python-pip',
    _CLANG_FORMAT_PATH: 'sudo apt-get install clang-format-3.7',
    _FIXJSSTYLE_PATH: 'pip install --user https://github.com/google/closure-linter/zipball/master',
  }):
    sys.exit(1)

  _JAVASCRIPT_TOOLCHAIN_VERIFIED = True

  with tempfile.NamedTemporaryFile(suffix='.js') as f:
    f.write(contents)
    f.flush()
    f.seek(0, 0)

    subprocess.check_output(['yarn', 'run', 'refactor', '--', f.name,
                             '--assume-filename=%s' % filename],
                            stderr=subprocess.STDOUT)
    subprocess.check_output([_FIXJSSTYLE_PATH, '--strict', f.name],
                             stderr=subprocess.STDOUT)
    subprocess.check_output([_CLANG_FORMAT_PATH, '-style=Google',
                             '-assume-filename=%s' % filename,
                             '-i', f.name], stderr=subprocess.STDOUT)
    with open(f.name, 'rb') as f2:
      return f2.read()

def lint_html(contents):
  '''Runs tidy on |contents|.'''

  contents = (b'<!DOCTYPE html>\n<html>\n<head>\n  <title></title>\n'
              b'</head><body>\n' + contents + b'\n</body>\n</html>')

  args = [_TIDY_PATH, '-q', '-config',
      os.path.join(git_tools.OMEGAUP_ROOT, 'stuff/tidy.txt')]
  p = subprocess.Popen(args, stdin=subprocess.PIPE, stdout=subprocess.PIPE,
      stderr=subprocess.PIPE)

  new_contents, stderr = p.communicate(contents)
  retcode = p.wait()

  if retcode in (0, 1):
    # |retcode| == 1 means that there were warnings.
    lines = new_contents.split(b'\n')
    return b'\n'.join(line.rstrip() for line in lines[8:-3])

  raise subprocess.CalledProcessError(retcode, cmd, output=stderr)

# vim: expandtab shiftwidth=2 tabstop=2
