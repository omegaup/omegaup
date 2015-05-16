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
JS_TEMPLATES_PATH = 'frontend/www/js'
PSEUDOLOC = 'hacker-boy'
LINE_RE = re.compile(r'\s+=\s+')

class colors:
	HEADER = '\033[95m'
	OKGREEN = '\033[92m'
	FAIL = '\033[91m'
	NORMAL = '\033[0m'

parser = argparse.ArgumentParser(description='i18n tool')
parser.add_argument('--validate', dest='validate', action='store_true',
                    default=False, help='Only validates, does not make changes')

args = parser.parse_args()

root_dir = subprocess.check_output(['/usr/bin/git', 'rev-parse', '--show-toplevel']).strip()
templates_dir = os.path.join(root_dir, TEMPLATES_PATH)
js_templates_dir = os.path.join(root_dir, JS_TEMPLATES_PATH)
pseudoloc_file = os.path.join(templates_dir, PSEUDOLOC + '.lang')
strings = {}
languages = set([PSEUDOLOC])
not_sorted = set()

for lang_path in glob(os.path.join(templates_dir, '*.lang')):
	lang_filename = os.path.basename(lang_path)
	lang = os.path.splitext(lang_filename)[0]
	languages.add(lang)
	last_key = ''
	with codecs.open(lang_path, 'r', 'utf-8') as lang_file:
		for line in lang_file:
			try:
				key, value = LINE_RE.split(line.strip())
				if last_key >= key:
					not_sorted.add(lang)
				last_key = key
				if key not in strings:
					strings[key] = {}
				strings[key][lang] = value
			except:
				print >> sys.stderr, 'Invalid i18n line "%s" in file "%s"' % (line.strip(), lang_path)
				sys.exit(1)

errors = False
if args.validate and not_sorted:
	print >> sys.stderr, 'Entries in %s are not sorted.' % ', '.join(sorted(not_sorted))
	errors = True

for key, values in strings.iteritems():
	missing_languages = languages.difference(values.keys())
	if not args.validate and PSEUDOLOC in missing_languages:
		missing_languages.remove(PSEUDOLOC)
	if missing_languages:
		print >> sys.stderr, '%s%s%s' % (colors.HEADER, key, colors.NORMAL)
		for lang in sorted(languages):
			if lang in values:
				print >> sys.stderr, '\t%s%-10s%s %s' % (colors.OKGREEN, lang, colors.NORMAL, values[lang])
			else:
				print >> sys.stderr, '\t%s%-10s%s missing%s' % (colors.OKGREEN, lang, colors.FAIL, colors.NORMAL)
		errors = True

if errors:
	if args.validate:
		print >> sys.stderr, 'i18n validation errors. Please run %s to fix them.' % sys.argv[0]
	else:
		print >> sys.stderr, 'i18n validation errors. Please fix them manually.'
	sys.exit(1)

if args.validate:
	sys.exit(0)

def pseudoloc(s):
	healthy = u'elsot'
	yummy = u'31507'
	table = dict([(ord(healthy[i]), yummy[i]) for i in xrange(len(healthy))] + [(ord(u'"'), u'')])
	return u'"(%s)"' % s.translate(table)

for key, values in strings.iteritems():
	if key == 'locale':
	    values[PSEUDOLOC] = '"ps"'
	else:
	    values[PSEUDOLOC] = pseudoloc(values['en'])

for lang in languages:
	lang_path = os.path.join(templates_dir, lang + '.lang')
	with codecs.open(lang_path, 'w', 'utf-8') as lang_file:
		for key in sorted(strings.keys()):
			lang_file.write('%s = %s\n' % (key, strings[key][lang]))
	js_lang_path = os.path.join(js_templates_dir, 'lang.%s.js' % lang)
	with codecs.open(js_lang_path, 'w', 'utf-8') as lang_file:
		lang_file.write('OmegaUp.T = {\n')
		for key in sorted(strings.keys()):
			lang_file.write('\t%s: %s,\n' % (key, strings[key][lang]))
		lang_file.write('};\n')
