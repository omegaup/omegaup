#!/usr/bin/env python3
# -*- coding: utf-8 -*-
'''The omegaUp translation string linter.'''

import re

from typing import List, Optional

from omegaup_hook_tools import linters

_FRONTEND_RE = re.compile(r'\bT\.(\w+)')
_TEMPLATE_RE = re.compile(r'\{#(\w+)#\}')
_LANG_RE = re.compile(r'^(\w+)\s*=', flags=re.MULTILINE)
_EXCLUDED_STRINGS = set(('lang', 'hasOwnProperty'))


class TranslationStringsLinter(linters.Linter):
    '''Runs translation_strings'''

    # pylint: disable=R0903

    def __init__(self, options: Optional[linters.Options] = None) -> None:
        super().__init__()
        del options
        with open('frontend/templates/en.lang', encoding='utf-8') as f:
            self.__valid_strings = set(_LANG_RE.findall(
                f.read())) | _EXCLUDED_STRINGS

    def run_one(self, filename: str, contents: bytes) -> linters.SingleResult:
        '''Runs the linter against |contents|.'''
        diagnostics: List[linters.Diagnostic] = []
        for lineno, line in enumerate(contents.decode('utf-8').split('\n'),
                                      start=1):
            if filename.endswith('.tpl'):
                regex = _TEMPLATE_RE
            else:
                regex = _FRONTEND_RE
            for match in regex.finditer(line):
                if match[1] in self.__valid_strings:
                    continue
                diagnostics.append(
                    linters.Diagnostic(
                        f'Missing translation string {match[1]!r}',
                        filename,
                        lineno=lineno,
                        line=line,
                        col=match.start(1) + 1,
                        col_end=match.end(1) + 1))
        if diagnostics:
            raise linters.LinterException('Missing translation strings',
                                          fixable=False,
                                          diagnostics=diagnostics)
        return linters.SingleResult(contents, ['translation_strings'])

    @property
    def name(self) -> str:
        '''Gets the name of the linter.'''
        return 'translation_strings'
