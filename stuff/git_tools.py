#!/usr/bin/python3

'''
Tools used to write git hooks.
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
NULL_HASH = '0000000000000000000000000000000000000000'

class COLORS:
  HEADER = '\033[95m'
  OKGREEN = '\033[92m'
  FAIL = '\033[91m'
  NORMAL = '\033[0m'

def get_explicit_file_list(args):
  try:
    idx = args.index('--')
    files = args[idx+1:]
    args[idx:] = []
    return files
  except:
    return []

def validate_args(args):
  '''Validates whether args is valid.

  args.commits is valid if it has no commits (which operates
  against the working tree), one commit (shorthand for diffing
  from the creation of the repository until that commit), or two
  commits.
  '''
  if len(args.commits) not in (0, 1, 2):
    print('%sCan only specify zero, one, or two commits.%s' %
          (COLORS.FAIL, COLORS.NORMAL),
          file=sys.stderr)
    return False
  return True

def file_at_commit(commits, filename):
  '''Returns the contents of |filename| at git commit |commit|.'''
  if len(commits) == 0:
    with open(filename, 'rb') as f:
      return f.read()
  else:
    return subprocess.check_output(['/usr/bin/git', 'show',
      '%s:%s' % (commits[-1], filename)])

def root_dir():
  '''Returns the top-level directory of the project.'''
  return subprocess.check_output(['/usr/bin/git', 'rev-parse',
    '--show-toplevel'], universal_newlines=True).strip()

def changed_files(commits, whitelist=(), blacklist=()):
  '''Returns the list of changed files between commits.

  If the first commit is the null hash, all files present in the second commit
  will be considered.

  Only files that matched against at least one of the regular expressions in
  |whitelist|, and match against no regular expressions in |blacklist| will be
  present in the result.
  '''
  root = root_dir()

  # Get all files in the latter commit.
  result = set()
  if not commits:
    final_commit = 'HEAD'
  else:
    final_commit = commits[-1]
  for line in subprocess.check_output(['/usr/bin/git', 'ls-tree', '-r',
                                       final_commit], cwd=root).splitlines():
    m = GIT_LS_TREE_PATTERN.match(line)
    if not m:
      continue
    result.add(m.groups()[0])

  # Only keep files that were modified in the specified range.
  if len(commits) == 2:
    modified = set()
    for line in subprocess.check_output(['/usr/bin/git', 'diff-tree', '-r',
                                         '--diff-filter=d'] +
                                         commits, cwd=root).splitlines():
      m = GIT_DIFF_TREE_PATTERN.match(line)
      src, dest = m.groups()
      if dest:
        modified.add(dest)
      else:
        modified.add(src)
    result = result & modified

  # And in the whitelist.
  whitelist = [re.compile(r) for r in whitelist]
  result = [filename for filename in result if any(r.match(filename)
    for r in whitelist)]

  # And not in the blacklist.
  blacklist = [re.compile(r) for r in blacklist]
  result = [filename for filename in result if all(not r.match(filename)
    for r in blacklist)]

  return [str(filename, encoding='utf-8') for filename in result]

def parse_arguments(tool_description=None):
  parser = argparse.ArgumentParser(description=tool_description)
  subparsers = parser.add_subparsers(dest='tool')
  subparsers.required = True

  validate_parser = subparsers.add_parser('validate',
      help='Only validates, does not make changes')
  validate_parser.add_argument('commits', metavar='commit', nargs='*',
      type=str, help='Only include files changed between commits')
  validate_parser.add_argument('ignored', metavar='--', nargs='?')
  validate_parser.add_argument('ignored', metavar='file', nargs='*',
      help='If specified, only consider these files')

  fix_parser = subparsers.add_parser('fix',
      help='Fixes all violations and leaves the results in the working tree.')
  fix_parser.add_argument('commits', metavar='commit', nargs='*',
      type=str, help='Only include files changed between commits')
  fix_parser.add_argument('ignored', metavar='--', nargs='?')
  fix_parser.add_argument('ignored', metavar='file', nargs='*',
      help='If specified, only consider these files')

  files = get_explicit_file_list(sys.argv)
  args = parser.parse_args()
  if not validate_args(args):
    sys.exit(1)
  args.files = files
  return args

def get_fix_commandline(progname, args):
  params = [progname, 'fix']
  params.extend(args.commits)
  if args.files:
    params.append('--')
    params.extend(args.files)
  return ' '.join(pipes.quote(p) for p in params)

# vim: expandtab shiftwidth=2 tabstop=2
