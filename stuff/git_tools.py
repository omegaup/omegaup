#!/usr/bin/python3

'''
Utility functions used to write git hooks.
'''

import argparse
import os.path
import pipes
import re
import subprocess
import sys

GIT_DIFF_TREE_PATTERN = re.compile(
    br'^:\d+ \d+ [0-9a-f]+ [0-9a-f]+ [ACDMRTUX]\d*\t([^\t]+)(?:\t([^\t]+))?$')
GIT_LS_TREE_PATTERN = re.compile(br'^\d* blob [0-9a-f]+\t(.*)$')

class COLORS:
  HEADER = '\033[95m'
  OKGREEN = '\033[92m'
  FAIL = '\033[91m'
  NORMAL = '\033[0m'

def _get_explicit_file_list(args):
  '''Returns the explicit file list from the commandline.

  Developers might want to use an explicit file list in case there is a file
  with the same name as a commit. The way git disambiguates is that arguments
  that come before -- are references, and the ones that come after are files.
  We use the same convention.
  '''
  try:
    idx = args.index('--')
    files = args[idx+1:]
    args[idx:] = []
    return files
  except:
    return []

def _validate_args(args, files):
  '''Validates whether args is valid.

  args.commits is valid if it has one commit (diffing from that commit against
  the working tree) or two commits.
  '''
  if args.all_files:
    if args.commits != ['HEAD'] or files:
      print('%s--all-files is incompatible with `commits` or `files`.%s' %
            (COLORS.FAIL, COLORS.NORMAL),
            file=sys.stderr)
      return False
  if len(args.commits) not in (1, 2):
    # args.commits can never be empty since its default value is ['HEAD'], but
    # the user can specify zero commits.
    print('%sCan only specify zero, one or two commits.%s' %
          (COLORS.FAIL, COLORS.NORMAL),
          file=sys.stderr)
    return False
  return True

def _files_to_consider(args, whitelist=(), blacklist=()):
  '''Returns the list of files to consider.

  If the first commit is the null hash, all files present in the second commit
  will be considered.

  Only files that matched against at least one of the regular expressions in
  |whitelist|, and match against no regular expressions in |blacklist| will be
  present in the result.
  '''
  root = root_dir()

  # Get all files in the latter commit.
  result = set()
  if args.all_files:
    for line in subprocess.check_output(['/usr/bin/git', 'ls-tree', '-r',
                                         'HEAD'], cwd=root).splitlines():
      m = GIT_LS_TREE_PATTERN.match(line)
      if not m:
        continue
      result.add(m.groups()[0])
  else:
    # Only keep files that were modified in the specified range.
    if len(args.commits) == 1:
      cmd = ['/usr/bin/git', 'diff-index', '--diff-filter=d'] + args.commits
    else:
      cmd = ['/usr/bin/git', 'diff-tree', '-r',
             '--diff-filter=d'] + args.commits
    for line in subprocess.check_output(cmd, cwd=root).splitlines():
      m = GIT_DIFF_TREE_PATTERN.match(line)
      src, dest = m.groups()
      if dest:
        result.add(dest)
      else:
        result.add(src)

  # And in the whitelist.
  whitelist = [re.compile(r) for r in whitelist]
  result = [filename for filename in result if any(r.match(filename)
    for r in whitelist)]

  # And not in the blacklist.
  blacklist = [re.compile(r) for r in blacklist]
  result = [filename for filename in result if all(not r.match(filename)
    for r in blacklist)]

  return [str(filename, encoding='utf-8') for filename in result]

def file_contents(args, root, filename):
  '''Returns the contents of |filename| At the revision specified by |args|.'''
  if len(args.commits) == 1:
    # Zero or one commits (where the former is a shorthand for 'HEAD') always
    # diff against the current contents of the file in the filesystem.
    with open(os.path.join(root, filename), 'rb') as f:
      return f.read()
  else:
    return subprocess.check_output(['/usr/bin/git', 'show',
      '%s:%s' % (args.commits[-1], filename)])

def root_dir():
  '''Returns the top-level directory of the project.'''
  return subprocess.check_output(['/usr/bin/git', 'rev-parse',
    '--show-toplevel'], universal_newlines=True).strip()

def parse_arguments(tool_description=None, file_whitelist=(),
    file_blacklist=()):
  '''Parses the commandline arguments.'''
  parser = argparse.ArgumentParser(description=tool_description)
  parser.add_argument('--verbose', action='store_true',
      help='Prints verbose information')
  subparsers = parser.add_subparsers(dest='tool')
  subparsers.required = True

  validate_parser = subparsers.add_parser('validate',
      help='Only validates, does not make changes')
  validate_parser.add_argument('--all-files', action='store_true',
      help='Considers all files. Incompatible with `commits` and `files`')
  validate_parser.add_argument('commits', metavar='commit', nargs='*',
      default=['HEAD'], type=str,
      help='Only include files changed between commits')
  validate_parser.add_argument('ignored', metavar='--', nargs='?')
  validate_parser.add_argument('ignored', metavar='file', nargs='*',
      help='If specified, only consider these files')

  fix_parser = subparsers.add_parser('fix',
      help='Fixes all violations and leaves the results in the working tree.')
  fix_parser.add_argument('--all-files', action='store_true',
      help='Considers all files. Incompatible with `commits` and `files`')
  fix_parser.add_argument('commits', metavar='commit', nargs='*',
      default=['HEAD'], type=str,
      help='Only include files changed between commits')
  fix_parser.add_argument('ignored', metavar='--', nargs='?')
  fix_parser.add_argument('ignored', metavar='file', nargs='*',
      help='If specified, only consider these files')

  files = _get_explicit_file_list(sys.argv)
  args = parser.parse_args()
  if not _validate_args(args, files):
    sys.exit(1)
  if files:
    args.files = files
  else:
    args.files = _files_to_consider(args, whitelist=file_whitelist,
        blacklist=file_blacklist)
  if args.verbose:
    print('Files to consider: %s' % ' '.join(args.files),
          file=sys.stderr)
  return args

def get_fix_commandline(progname, args):
  '''Gets the commandline the developer must run to fix violations.'''
  params = [progname, 'fix']
  params.extend(args.commits)
  if args.files:
    params.append('--')
    params.extend(args.files)
  return ' '.join(pipes.quote(p) for p in params)

def verify_toolchain(binaries):
  '''Verifies that the developer has all necessary tools installed.'''
  success = True
  for path, install_cmd in binaries.items():
    if not os.path.isfile(path):
      print('%s%s not found.%s ' 'Please run `%s` to install.' %
          (git_tools.COLORS.FAIL, path, git_tools.COLORS.NORMAL,
           install_cmd), file=sys.stderr)
      success = False
  return success

# vim: expandtab shiftwidth=2 tabstop=2
