#!/usr/bin/python3

'''
Runs the HTML and JavaScript linters on .vue files.
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
from html.parser import HTMLParser

class VueHTMLParser(HTMLParser):
  def __init__(self):
    super(VueHTMLParser, self).__init__()
    self._stack = []
    self._tags = []

  def parse(self, contents):
    self._stack = []
    self._tags = []
    self.feed(contents)

    lines = contents.split('\n')

    sections = []
    for tag, starttag, start, end in self._tags:
      line_range = []
      if len(lines[start[0]]) > len(starttag) + start[1]:
        line_range.append(lines[start[0]][len(starttag) + start[1]:])
      line_range += lines[start[0]+1:end[0]]
      if end[1] > 0:
        line_range.append(lines[end[0]][:end[1]])
      sections.append((tag, starttag, '\n'.join(line_range)))
    return sections

  def handle_starttag(self, tag, attrs):
    line, col = self.getpos()
    self._stack.append((tag, self.get_starttag_text(), (line - 1, col)))

  def handle_endtag(self, tag):
    while self._stack and self._stack[-1][0] != tag:
      self._stack.pop()
    assert self._stack and self._stack[-1][0] == tag
    _, starttag, begin = self._stack.pop()
    if not self._stack:
      line, col = self.getpos()
      self._tags.append((tag, starttag, begin, (line - 1, col)))

def run_linter(args, files, validate_only):
  '''Runs the Google Closure Compiler linter+prettier against |files|.'''
  root = git_tools.root_dir()
  file_violations = set()
  for filename in files:
    contents = git_tools.file_contents(args, root, filename)
    parser = VueHTMLParser()
    try:
      sections = parser.parse(contents.decode('utf-8'))
    except AssertionError:
      print('File %s%s%s lint failed' % (COLORS.FAIL,
        filename, COLORS.NORMAL), file=sys.stderr)
      validation_passed = False
      continue

    new_sections = []
    for tag, starttag, section_contents in sections:
      try:
        if tag == 'script':
          new_section_contents = lint_tools.lint_javascript(filename + '.js',
                                                            section_contents.encode('utf-8'))
          new_sections.append('%s\n%s\n</%s>' % (starttag,
            new_section_contents.decode('utf-8'), tag))
        elif tag == 'template':
          new_section_contents = lint_tools.lint_html(section_contents.encode('utf-8'))
          new_sections.append('%s\n%s\n</%s>' % (starttag,
            new_section_contents.decode('utf-8'), tag))
        else:
          new_sections.append('%s\n%s\n</%s>' % (starttag, section_contents, tag))
      except subprocess.CalledProcessError as e:
        print('File %s%s%s lint failed:\n%s' % (COLORS.FAIL,
          filename, COLORS.NORMAL, str(b'\n'.join(e.output.split(b'\n')[1:]),
            encoding='utf-8')), file=sys.stderr)
        validation_passed = False
        break

    if len(new_sections) != len(sections):
      continue

    new_contents = ('\n\n'.join(new_sections)).encode('utf-8') + b'\n'
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
  args = git_tools.parse_arguments(tool_description='lints vue',
        file_whitelist=[br'^frontend/www/.*\.vue$'])

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
