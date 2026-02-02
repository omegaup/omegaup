#!/usr/bin/env python3
# -*- coding: utf-8 -*-
'''Find unused translation strings.

This requires psalm to be run just prior.
'''

import logging
import os
import re
import sys

from typing import Set

_ALLOWLIST_RE = re.compile(
    r'^(frontend/www/js/.*\.(js|ts|vue))|(frontend/templates/.*\.tpl)$')
_FRONTEND_RE = re.compile(r'\bT\.(\w+)')
_TEMPLATE_RE = re.compile(r'\{#(\w+)#\}')
_LANG_RE = re.compile(r'^(\w+)\s*=', flags=re.MULTILINE)
_EXCLUDED_STRINGS = set(('lang', 'hasOwnProperty'))


def _get_expected_strings() -> Set[str]:
    """Obtains all translation strings from the frontend."""
    expected_strings: Set[str] = set()

    # First consider the strings found in templates, JavaScript, TypeScript,
    # and Vue.
    for root, _, filenames in os.walk('frontend'):
        for filename in filenames:
            path = os.path.join(root, filename)
            if not _ALLOWLIST_RE.match(path):
                continue
            if filename.endswith('.tpl'):
                regex = _TEMPLATE_RE
            else:
                regex = _FRONTEND_RE
            with open(path, encoding='utf-8') as f:
                for line in f:
                    for linematch in regex.finditer(line):
                        if linematch[1] in _EXCLUDED_STRINGS:
                            continue
                        expected_strings.add(linematch[1])

    # Now get the Psalm-obtained translation strings from PHP.
    for filename in os.listdir('frontend/tests/runfiles/translation_strings'):
        path = os.path.join('frontend/tests/runfiles/translation_strings',
                            filename)
        with open(path, encoding='utf-8') as f:
            for line in f:
                expected_strings.add(line.strip())

    return expected_strings


def _main() -> None:
    logging.basicConfig(level=logging.INFO)

    expected_strings = _get_expected_strings()
    success = True

    # Finally, compare the expected list to the list of strings in one of the
    # translation string files.
    with open('frontend/templates/en.lang', encoding='utf-8') as f:
        for lineno, line in enumerate(f, start=1):
            match = _LANG_RE.match(line.strip())
            if not match:
                continue
            translation_string_name = match.group(1)
            # Badge names and descriptions.
            if translation_string_name.startswith('badge_'):
                continue
            # Verdict translations and their descriptions.
            if translation_string_name.startswith('verdict'):
                continue
            # Parameter name translations (constructed dynamically in PHP).
            if translation_string_name == 'parameterName_finish_time':
                continue
            # public / student course information description
            if translation_string_name.endswith(
                    'CourseInformationDescription'):
                continue

            if translation_string_name not in expected_strings:
                success = False
                print(
                    f'::error '
                    f'file=frontend/templates/en.lang,'
                    f'line={lineno}::'
                    f'Translation string {translation_string_name!r} not used')

    if not success:
        sys.exit(1)


if __name__ == '__main__':
    _main()
