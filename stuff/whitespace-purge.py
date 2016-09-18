#!/usr/bin/python

import argparse
import git_tools
import os
import subprocess
import sys

FILTERS = ['--include=*.css', '--include=*.js', '--include=*.php',
	'--include=*.sql', '--include=*.tpl', '--exclude-dir=facebook-php-sdk',
	'--exclude-dir=Markdown', '--exclude-dir=google-api-php-client',
	'--exclude-dir=smarty', '--exclude-dir=adodb', '--exclude-dir=phpmailer',
	'--exclude-dir=Mailchimp', '--exclude-dir=base', '--exclude-dir=log4php',
	'--exclude-dir=mathjax', '--exclude-dir=*.git', '--exclude-dir=pagedown',
	'--exclude-dir=karel', '--exclude=jquery*', '--exclude=bootstrap*',
	'--exclude=codemirror*', '--exclude-dir=frontend/server/libs/dao/base',
	'--exclude-dir=karel.js', '--exclude-dir=templates_c']

class colors:
	HEADER = '\033[95m'
	OKGREEN = '\033[92m'
	FAIL = '\033[91m'
	NORMAL = '\033[0m'

def which(program):
	for path in os.environ["PATH"].split(os.pathsep):
		exe_file = os.path.join(path.strip('"'), program)
		if os.path.isfile(exe_file) and os.access(exe_file, os.X_OK):
			return exe_file
	raise Exception('`%s` not found' % program)

def run_validation(grep_flags, detect_regex, error_string, fix_command, files,
		validate_only):
	violations = []
	try:
		violations += subprocess.check_output([which('grep'), grep_flags] + FILTERS +
			[detect_regex] + files).strip().split('\n')
	except subprocess.CalledProcessError:
		# If the command failed, that means that nothing matched.
		return False
	if violations:
		if validate_only:
			print >> sys.stderr, '%s%s: %s%s' % (colors.FAIL, error_string, colors.NORMAL,
					' '.join(violations))
		else:
			subprocess.check_call(fix_command + violations)
		return True
	return False

def main():
	parser = argparse.ArgumentParser(description='purge whitespace')
	parser.add_argument('--from-commit', dest='from_commit', type=str,
			help='Only include files changed from a certain commit')
	parser.add_argument('--validate', dest='validate', action='store_true',
			default=False, help='Only validates, does not make changes')

	args = parser.parse_args()

	changed_files = git_tools.changed_files(args.from_commit)
	if not changed_files:
		return 0

	errors = False

	errors |= run_validation('-Rl', r'\s\+$',
			'Files have trailing whitespace',
			[which('sed'), '-i', '-e', r's/\s*$//'],
			changed_files, args.validate)
	errors |= run_validation('-PRzl', r'(?s)\n\n\n',
			'Files have consecutive empty lines',
			['/usr/bin/perl', '-i', '-0pe', r's/\n\n\n+/\n\n/g'],
			changed_files, args.validate)
	errors |= run_validation('-PRzl', r'(?s){\n\n',
			'Files have an empty line after an opening brace',
			['/usr/bin/perl', '-i', '-0pe', r's/{\n\n+/{\n/g'],
			changed_files, args.validate)
	errors |= run_validation('-PRzl', r'(?s)\n\n\s*}',
			'Files have an empty line before a closing brace',
			['/usr/bin/perl', '-i', '-0pe', r's/\n\n+(\s*})/\n\1/g'],
			changed_files, args.validate)

	if errors:
		if args.validate:
			if args.from_commit:
				extra_args = ' --from-commit=%s' % args.from_commit
			else:
				extra_args = ''
			print >> sys.stderr, '%sWhitespace validation errors.%s Please run `%s%s` to fix them.' % (colors.FAIL, colors.NORMAL, sys.argv[0], extra_args)
		return 1
	return 0

if __name__ == '__main__':
	sys.exit(main())

# vim: noexpandtab shiftwidth=2 tabstop=2
