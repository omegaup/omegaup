#!/usr/bin/python

import argparse
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
	'--exclude=codemirror*']

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
	parser.add_argument('remote', nargs='?', default='origin',
			help='The remote that will be pushed to')
	parser.add_argument('--pre-push', dest='pre_push', action='store_true',
			default=False, help='Only include files to be pushed')
	parser.add_argument('--validate', dest='validate', action='store_true',
			default=False, help='Only validates, does not make changes')

	args = parser.parse_args()

	root_dir = subprocess.check_output(['/usr/bin/git', 'rev-parse',
		'--show-toplevel']).strip()
	if args.pre_push:
		changed_files = filter(
				lambda x: 'frontend' in x and x.endswith('.php') and os.path.exists(x),
				[os.path.join(root_dir, x) for x in subprocess.check_output(
					['/usr/bin/git', 'diff', '--name-only', args.remote, '--']
				).strip().split('\n')]
		)
	else:
		changed_files = [os.path.join(root_dir, 'frontend')]

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
			if args.pre_push:
				pre_push_args = ' --pre-push'
			else:
				pre_push_args = ''
			print >> sys.stderr, '%sWhitespace validation errors.%s Please run `%s%s` to fix them.' % (colors.FAIL, colors.NORMAL, sys.argv[0], pre_push_args)
		return 1
	return 0

if __name__ == '__main__':
	sys.exit(main())

# vim: noexpandtab shiftwidth=2 tabstop=2
