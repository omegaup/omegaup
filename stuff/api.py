#!/usr/bin/python3
# -*- coding: utf-8 -*-
'''The omegaUp API linter.'''

import os
import subprocess
from typing import (Any, Callable, Dict, Mapping, Optional, Sequence, Text,
                    Tuple)

from hook_tools import linters

_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
_API_D_TS_PATH = 'frontend/www/js/omegaup/api.d.ts'


def _which(program: str) -> str:
    '''Looks for |program| in $PATH. Similar to UNIX's `which` command.'''
    for path in os.environ['PATH'].split(os.pathsep):
        exe_file = os.path.join(path.strip('"'), program)
        if os.path.isfile(exe_file) and os.access(exe_file, os.X_OK):
            return exe_file
    raise Exception('`%s` not found' % program)


def _generate_typescript() -> str:
    '''Generates the TypeScript version of the i18n file.'''

    command = [
        _which('php'),
        os.path.join(_ROOT, 'frontend/server/cmd/APITool.php'),
    ]
    result = subprocess.check_output(
        command, universal_newlines=True, cwd=_ROOT)
    command = [
        _which('prettier'),
        '--single-quote',
        '--trailing-comma=all',
        '--no-config',
        '--stdin-filepath',
        'api.d.ts',
    ]
    # pylint: disable=unexpected-keyword-arg
    result = subprocess.check_output(
        command, universal_newlines=True, cwd=_ROOT,
        input=result)
    return result


def _generate_content_entry(new_contents: Dict[str, bytes],
                            original_contents: Dict[str, bytes], path: str,
                            new_content: str,
                            contents_callback: Callable[[str], bytes]) -> None:
    new_contents[path] = new_content.encode('utf-8')
    original_contents[path] = contents_callback(path)


def _generate_new_contents(contents_callback: Callable[[str], bytes]
                           ) -> Tuple[Dict[str, bytes], Dict[str, bytes]]:
    new_contents: Dict[str, bytes] = {}
    original_contents: Dict[str, bytes] = {}
    _generate_content_entry(
        new_contents,
        original_contents,
        path=_API_D_TS_PATH,
        new_content=_generate_typescript(),
        contents_callback=contents_callback)

    return new_contents, original_contents


class ApiLinter(linters.Linter):
    '''Runs the API linter'''

    # pylint: disable=R0903

    def __init__(self, options: Optional[Dict[str, Any]] = None):
        super().__init__()
        self.__options = options or {}

    def run_one(self, filename: str,
                contents: bytes) -> Tuple[bytes, Sequence[Text]]:
        '''Runs the linter against |contents|.'''
        # pylint: disable=no-self-use
        del filename  # unused
        return contents, []

    def run_all(
            self, filenames: Sequence[Text],
            contents_callback: Callable[[Text], bytes]
    ) -> Tuple[Mapping[Text, bytes], Mapping[Text, bytes], Sequence[Text]]:
        '''Runs the linter against a subset of files.'''
        # pylint: disable=no-self-use
        del filenames  # unused

        new_contents, original_contents = _generate_new_contents(
            contents_callback)

        return new_contents, original_contents, ['api']

    @property
    def name(self) -> Text:
        '''Gets the name of the linter.'''
        return 'api'


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
