#!/usr/bin/python3

import argparse
from glob import glob
import os.path
import re
import string
import json
import subprocess
import sys

TEMPLATES_PATH = 'frontend/templates'
JS_TEMPLATES_PATH = 'frontend/www/js/omegaup'
PSEUDOLOC = 'pseudo'
LINE_RE = re.compile(r'\s+=\s+')
VALUE_RE = re.compile(r'^"((?:[^"]|\\")*)"$')

class colors:
	HEADER = '\033[95m'
	OKGREEN = '\033[92m'
	FAIL = '\033[91m'
	NORMAL = '\033[0m'

parser = argparse.ArgumentParser(description='i18n tool')
parser.add_argument('--validate', dest='validate', action='store_true',
		default=False, help='Only validates, does not make changes')

parser.add_argument('--fill-missing-with-english', dest='fillmissing', action='store_true',
		default=False, help='Fill missing words with the english version of it')

args = parser.parse_args()

root_dir = subprocess.check_output(['/usr/bin/git', 'rev-parse', '--show-toplevel'],
					universal_newlines=True).strip()
templates_dir = os.path.join(root_dir, TEMPLATES_PATH)
js_templates_dir = os.path.join(root_dir, JS_TEMPLATES_PATH)
pseudoloc_file = os.path.join(templates_dir, PSEUDOLOC + '.lang')
strings = {}
languages = set([PSEUDOLOC])
not_sorted = set()

def generate_javascript(lang):
	result = []
	result.append('// generated by stuff/i18n.py. DO NOT EDIT.')
	result.append("var omegaup = require('../dist/omegaup.js');\n")
	result.append('omegaup.OmegaUp.loadTranslations({')
	for key in sorted(strings.keys()):
		result.append('\t%s: %s,' % (key, json.dumps(strings[key][lang])))
	result.append('});\n')
	return '\n'.join(result)

def generate_json(lang):
	json_map = {}
	for key in sorted(strings.keys()):
		json_map[key] = strings[key][lang]
	return json.dumps(json_map, sort_keys=True, indent='\t')

for lang_path in glob(os.path.join(templates_dir, '*.lang')):
	lang_filename = os.path.basename(lang_path)
	lang = os.path.splitext(lang_filename)[0]
	languages.add(lang)
	last_key = ''
	with open(lang_path, 'r') as lang_file:
		for lineno, line in enumerate(lang_file):
			try:
				key, value = LINE_RE.split(line.strip(), 1)
				if last_key >= key:
					not_sorted.add(lang)
				last_key = key
				if key not in strings:
					strings[key] = {}
				m = VALUE_RE.match(value)
				if m is None:
					raise Exception("Invalid value")
				strings[key][lang] = m.group(1).replace(r'\"', '"')
			except:
				print('Invalid i18n line "%s" in %s:%d' % (
						line.strip(), lang_path, lineno + 1), file=sys.stderr)
				sys.exit(1)

errors = False
if args.validate and not_sorted:
	print('Entries in %s are not sorted.' % ', '.join(sorted(not_sorted)), file=sys.stderr)
	errors = True

if args.fillmissing:
	for key, values in strings.items():
		missing_languages = languages.difference(list(values.keys()))
		if missing_languages:
			print('Fixing %s%s for %s%s' % (colors.HEADER, key, lang, colors.NORMAL), file=sys.stderr)
			english_word = values['en']
			for lang in sorted(languages):
				if lang not in values:
					with open(templates_dir + "/" + lang+".lang", 'a') as myfile:
						print('%s = %s' % (key, english_word), file=myfile)
	print('Done fixing your missing files, re-run this tool again as usual.', file=sys.stderr)
	sys.exit(0)

for key, values in strings.items():
	missing_languages = languages.difference(list(values.keys()))
	if not args.validate and PSEUDOLOC in missing_languages:
		missing_languages.remove(PSEUDOLOC)
	if missing_languages:
		print('%s%s%s' % (colors.HEADER, key, colors.NORMAL), file=sys.stderr)
		for lang in sorted(languages):
			if lang in values:
				print('\t%s%-10s%s %s' % (colors.OKGREEN, lang, colors.NORMAL, values[lang]), file=sys.stderr)
			else:
				print('\t%s%-10s%s missing%s' % (colors.OKGREEN, lang, colors.FAIL, colors.NORMAL), file=sys.stderr)
		errors = True

if args.validate:
	for lang in languages:
		js_lang_path = os.path.join(js_templates_dir, 'lang.%s.js' % lang)
		with open(js_lang_path, 'r') as lang_file:
			if lang_file.read() != generate_javascript(lang):
				print('Entries in %s do not match the .lang file.' % js_lang_path, file=sys.stderr)
				errors = True
		json_lang_path = os.path.join(js_templates_dir, 'lang.%s.json' % lang)
		with open(json_lang_path, 'r') as lang_file:
			obtained = lang_file.read().strip()
			expected = generate_json(lang).strip()
			if obtained != expected:
				print('Entries in %s do not match the .lang file.' % json_lang_path, file=sys.stderr)
				print(base64.b64encode(gzip.compress(expected.encode('utf-8'))), file=sys.stderr)
				print(base64.b64encode(gzip.compress(obtained.encode('utf-8'))), file=sys.stderr)
				errors = True

if errors:
	if args.validate:
		print('i18n validation errors. Please run %s to fix them.' % sys.argv[0], file=sys.stderr)
	else:
		print('i18n validation errors. Please fix them manually.', file=sys.stderr)
	sys.exit(1)

if args.validate:
	sys.exit(0)

def pseudoloc(s):
	healthy = 'elsot'
	yummy = '31507'
	table = dict([(ord(healthy[i]), yummy[i]) for i in range(len(healthy))])
	tokens = re.split('(%\([a-zA-Z0-9_-]+\))', s)
	for i in range(len(tokens)):
		if tokens[i].startswith('%(') and tokens[i].endswith(')'):
			continue
		tokens[i] = tokens[i].translate(table)
	return '(%s)' % ''.join(tokens)

for key, values in strings.items():
	if key == 'locale':
		values[PSEUDOLOC] = PSEUDOLOC
	else:
		values[PSEUDOLOC] = pseudoloc(values['en'])

for lang in languages:
	lang_path = os.path.join(templates_dir, lang + '.lang')
	with open(lang_path, 'w') as lang_file:
		for key in sorted(strings.keys()):
			lang_file.write('%s = "%s"\n' % (key, strings[key][lang].replace('"', r'\"')))
	js_lang_path = os.path.join(js_templates_dir, 'lang.%s.js' % lang)
	with open(js_lang_path, 'w') as lang_file:
		lang_file.write(generate_javascript(lang))
	json_lang_path = os.path.join(js_templates_dir, 'lang.%s.json' % lang)
	with open(json_lang_path, 'w') as lang_file:
		lang_file.write(generate_json(lang))

# vim: noexpandtab
