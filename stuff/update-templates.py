#!/usr/bin/python

import argparse
import codecs
from glob import glob
import os.path
import re
import string
import subprocess
import sys

TEMPLATES_PATH = 'frontend/templates'
WWW_PATH = 'frontend/www'
JS_RE = re.compile(r'.*[\'"]((/(?:ux|js)/.*?\.js)(\?ver=[0-9a-f]+)?)[\'"]')

class colors:
	HEADER = '\033[95m'
	OKGREEN = '\033[92m'
	FAIL = '\033[91m'
	NORMAL = '\033[0m'

parser = argparse.ArgumentParser(description='i18n tool')
parser.add_argument('--validate', dest='validate', action='store_true',
		default=False, help='Only validates, does not make changes')

args = parser.parse_args()

root_dir = subprocess.check_output(['/usr/bin/git', 'rev-parse',
	'--show-toplevel']).strip()
templates_dir = os.path.join(root_dir, TEMPLATES_PATH)
www_dir = os.path.join(root_dir, WWW_PATH)
hashes = {}

errors = {}
for tpl_path in glob(os.path.join(templates_dir, '*.tpl')):
	tpl_source = []
	has_errors = False
	with codecs.open(tpl_path, 'r', 'utf-8') as tpl_file:
		for line in tpl_file:
			match = JS_RE.match(line)
			if match:
				js_name = match.group(2)
				js_path = os.path.join(www_dir, js_name[1:])
				if js_name != '/js/mathjax/MathJax.js' and os.path.exists(js_path):
					if js_name not in hashes:
						hashes[js_name] = subprocess.check_output(['/usr/bin/git',
							'hash-object', js_path]).strip()
					expected_version = '?ver=' + hashes[js_name][0:6]
					if match.group(3) != expected_version:
						tpl_source.append(line[:match.start(1)] + js_name +
								expected_version + line[match.end(1):])
						has_errors = True
					else:
						tpl_source.append(line)
				else:
					tpl_source.append(line)
			else:
				tpl_source.append(line)
	if has_errors:
		errors[tpl_path] = ''.join(tpl_source)

if args.validate:
	if errors:
		print >> sys.stderr, '%sTemplate validation errors.%s Please run %s to fix them.' % (colors.FAIL, colors.NORMAL, sys.argv[0])
		sys.exit(1)
	else:
		sys.exit(0)

for tpl_path, tpl_source in errors.iteritems():
	with codecs.open(tpl_path, 'w', 'utf-8') as tpl_file:
		tpl_file.write(tpl_source)

# vim: noexpandtab shiftwidth=2 tabstop=2
