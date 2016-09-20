#!/usr/bin/python

import argparse
import git_tools
import os.path
import subprocess
import sys

IGNORE_LIST = ['frontend/server/libs/dao/base/',
	'frontend/server/libs/dao/Estructura.php', 'frontend/server/libs/third_party/',
	'frontend/server/config.php', 'frontend/server/test/test_config.php',
	'frontend/tests/templates_c']

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
	parser.add_argument('--from-commit', dest='from_commit', type=str,
			help='Only include files changed from a certain commit')
	parser.add_argument('--validate', dest='validate', action='store_true',
			default=False, help='Only validates, does not make changes')

	args = parser.parse_args()

	root_dir = git_tools.root_dir()
	changed_files = git_tools.changed_files(args.from_commit)

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
			if args.from_commit:
				extra_args = ' --from-commit=%s' % args.from_commit
			else:
				extra_args = ''
			print >> sys.stderr, '%sPHP validation errors.%s Please run `%s%s` to fix them.' % (colors.FAIL, colors.NORMAL, sys.argv[0], extra_args)
		return 1
	return 0

if __name__ == '__main__':
	sys.exit(main())

# vim: noexpandtab shiftwidth=2 tabstop=2
