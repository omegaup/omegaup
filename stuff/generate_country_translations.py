#!/usr/bin/env python3
# -*- coding: utf-8 -*-
'''
Generates country name translation entries for en.lang, es.lang, and pt.lang
from Unicode CLDR data (via the Babel library).

Usage:
    python3 stuff/generate_country_translations.py

The generated entries are written directly into the .lang files.  Run the
i18n linter afterwards to regenerate pseudo.lang and the derived .ts/.json
files:

    python3 -m pytest stuff/i18n_linter.py   # or however linting is invoked

To regenerate when upgrading CLDR (i.e. upgrading Babel):
    1. Update the Babel version in the project requirements.
    2. Re-run this script.
    3. Review the diff — names may change across CLDR releases.
    4. Commit en.lang, es.lang, pt.lang, then let the linter sync the rest.

Pinned Babel version used to generate these translations: 2.10.3
(Babel embeds CLDR data; its version pins the CLDR revision.)

Two country codes are not present in CLDR and are handled manually:
  AN — Netherlands Antilles (dissolved 2010, still in omegaUp DB)
  XC — Catalonia (non-ISO custom code used by omegaUp)
'''

import os
import re
import sys
from typing import Dict, List

try:
    from babel import Locale  # type: ignore
    import babel
    BABEL_VERSION = babel.__version__
except ImportError:
    print('Error: Babel is required.  Install it with: pip install Babel',
          file=sys.stderr)
    sys.exit(1)

REPO_ROOT = os.path.join(os.path.dirname(__file__), '..')
TEMPLATES_DIR = os.path.join(REPO_ROOT, 'frontend', 'templates')
DB_MIGRATION = os.path.join(REPO_ROOT, 'frontend', 'database',
                            '00004_countries.sql')

LANGS = ['en', 'es', 'pt']

# Country codes not present in CLDR — provide translations manually.
MANUAL_OVERRIDES: Dict[str, Dict[str, str]] = {
    'AN': {
        'en': 'Netherlands Antilles',
        'es': 'Antillas Neerlandesas',
        'pt': 'Antilhas Neerlandesas',
    },
    'XC': {
        'en': 'Catalonia',
        'es': 'Cataluña',
        'pt': 'Catalunha',
    },
}

# 'xx' is handled via the existing `countryNotSet` key — skip it here.
SKIP_CODES = {'xx'}


def _read_country_codes_from_migration(path: str) -> List[str]:
    with open(path, encoding='utf-8') as f:
        content = f.read()
    codes = re.findall(r"'([A-Z]{2})'", content)
    return sorted(set(codes))


def _read_lang_file(path: str) -> Dict[str, str]:
    entries: Dict[str, str] = {}
    with open(path, encoding='utf-8') as f:
        for line in f:
            m = re.match(r'^(\w+)\s*=\s*"((?:[^"]|\\")*)"', line.rstrip())
            if m:
                entries[m.group(1)] = m.group(2).replace(r'\"', '"')
    return entries


def _write_lang_file(path: str, entries: Dict[str, str]) -> None:
    lines = []
    for key in sorted(entries.keys()):
        escaped = entries[key].replace('"', r'\"')
        lines.append(f'{key} = "{escaped}"\n')
    with open(path, 'w', encoding='utf-8') as f:
        f.writelines(lines)


def main() -> None:
    codes = _read_country_codes_from_migration(DB_MIGRATION)
    # XC exists in production but not in the migration file.
    if 'XC' not in codes:
        codes = sorted(codes + ['XC'])

    locales = {lang: Locale(lang) for lang in LANGS}

    new_entries: Dict[str, Dict[str, str]] = {}
    missing: List[str] = []
    for code in codes:
        if code in SKIP_CODES:
            continue
        key = f'country{code}'
        translations: Dict[str, str] = {}
        for lang, locale in locales.items():
            if code in MANUAL_OVERRIDES:
                translations[lang] = MANUAL_OVERRIDES[code][lang]
            else:
                name = locale.territories.get(code, '')
                if not name:
                    missing.append(f'{code} (lang={lang})')
                translations[lang] = name
        if all(translations.values()):
            new_entries[key] = translations
        else:
            print(f'Warning: skipping {code} — missing in some locales',
                  file=sys.stderr)

    if missing:
        print(f'Warning: CLDR missing for: {", ".join(missing)}', file=sys.stderr)

    added = 0
    for lang in LANGS:
        path = os.path.join(TEMPLATES_DIR, f'{lang}.lang')
        existing = _read_lang_file(path)
        before = len(existing)
        for key, translations in new_entries.items():
            if key not in existing:
                existing[key] = translations[lang]
                added += 1
        if len(existing) > before:
            _write_lang_file(path, existing)
            print(f'Updated {lang}.lang: added {len(existing) - before} entries')
        else:
            print(f'{lang}.lang: all entries already present, no changes')

    print(f'Done.  Babel version: {BABEL_VERSION}')
    print('Run the i18n linter to regenerate pseudo.lang and .ts/.json files.')


if __name__ == '__main__':
    main()
