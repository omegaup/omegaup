#!/usr/bin/python

import argparse
import os.path
import subprocess
import sys

IGNORE_LIST = ['frontend/server/libs/dao/base/',
	'frontend/server/libs/dao/Estructura.php', 'frontend/server/libs/adodb/',
	'frontend/server/libs/log4php/', 'frontend/server/libs/google-api-php-client/',
	'frontend/server/libs/facebook-php-sdk/', 'frontend/server/libs/log4php/',
	'frontend/server/libs/Mailchimp/', 'frontend/server/libs/Markdown/',
	'frontend/server/libs/phpmailer/', 'frontend/server/libs/smarty/',
	'frontend/server/libs/PasswordHash.php', 'frontend/server/libs/ZipStream.php',
	'frontend/server/config.php', 'frontend/server/test/test_config.php']

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

def main():
	parser = argparse.ArgumentParser(description='PHP linter')
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

	if args.validate:
		# TODO(lhchavez): Remove the -n to also enforce being warning-free.
		phpcs_args = [which('phpcs'), '-n', '-s']
	else:
		phpcs_args = [which('phpcbf')]

	phpcs_args += ['--extensions=php', '--encoding=utf-8', '--standard=%s' %
			os.path.join(root_dir, 'stuff/omegaup-standard.xml'), '--ignore=%s' %
			','.join([os.path.join(root_dir, f) for f in IGNORE_LIST])] + changed_files

	errors = False

	try:
		subprocess.check_call(phpcs_args)
	except subprocess.CalledProcessError:
		errors = True

	if errors:
		if args.validate:
			if args.pre_push:
				pre_push_args = ' --pre-push'
			else:
				pre_push_args = ''
			print >> sys.stderr, '%sPHP validation errors.%s Please run `%s%s` to fix them.' % (colors.FAIL, colors.NORMAL, sys.argv[0], pre_push_args)
		return 1
	return 0

if __name__ == '__main__':
	sys.exit(main())

# vim: noexpandtab shiftwidth=2 tabstop=2
