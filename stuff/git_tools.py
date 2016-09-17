#!/usr/bin/python

import os.path
import subprocess

NULL_HASH = '0000000000000000000000000000000000000000'

def root_dir():
	return subprocess.check_output(['/usr/bin/git', 'rev-parse',
		'--show-toplevel']).strip()

def changed_files(from_commit):
	root = root_dir()
	if from_commit and from_commit != NULL_HASH:
		try:
			changed_files = filter(
					lambda x: 'frontend' in x and x.endswith('.php') and os.path.exists(x),
					[os.path.join(root, x) for x in subprocess.check_output(
						['/usr/bin/git', 'diff', '--name-only', from_commit, '--']
					).strip().split('\n')]
			)
		except subprocess.CalledProcessError:
			pass
	return [os.path.join(root, 'frontend')]


# vim: noexpandtab shiftwidth=2 tabstop=2
