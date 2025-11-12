#!/usr/bin/env python3
# -*- coding: utf-8 -*-
'''The omegaUp API linter.'''

import os
import subprocess
import sys
from typing import Any, Callable, Dict, Optional, Sequence, Text, Tuple

from omegaup_hook_tools import linters

_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def _which(program: str) -> str:
    '''Looks for |program| in $PATH. Similar to UNIX's `which` command.'''
    for path in os.environ['PATH'].split(os.pathsep):
        exe_file = os.path.join(path.strip('"'), program)
        if os.path.isfile(exe_file) and os.access(exe_file, os.X_OK):
            return exe_file
    raise Exception(f'`{program}` not found')


def _generate_typescript(filename: str) -> str:
    '''Generates the TypeScript version of the i18n file.'''

    command = [
        _which('php'),
        os.path.join(_ROOT, 'frontend/server/cmd/APITool.php'),
        f'--file={filename}',
    ]
    result = subprocess.run(command,
                            text=True,
                            cwd=_ROOT,
                            stdout=subprocess.PIPE,
                            check=False)
    if result.returncode:
        # Log what happened, for debugging purposes.
        print(result, file=sys.stderr)
        result.check_returncode()
    command = [
        _which('prettier'),
        '--single-quote',
        '--trailing-comma=all',
        '--no-config',
        '--stdin-filepath',
        filename,
    ]
    return subprocess.run(command,
                          text=True,
                          cwd=_ROOT,
                          input=result.stdout,
                          stdout=subprocess.PIPE,
                          check=True).stdout


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
    for path in ('frontend/www/js/omegaup/api.ts',
                 'frontend/www/js/omegaup/api_types.ts',
                 'frontend/server/src/Controllers/README.md',
                 'frontend/www/docs/Controllers.md'):
        _generate_content_entry(
            new_contents,
            original_contents,
            path=path,
            new_content=_generate_typescript(os.path.basename(path)),
            contents_callback=contents_callback)

    return new_contents, original_contents


class ApiLinter(linters.Linter):
    '''Runs the API linter'''

    # pylint: disable=R0903

    def __init__(self, options: Optional[Dict[str, Any]] = None):
        super().__init__()
        del options

    def run_all(
            self, filenames: Sequence[str],
            contents_callback: linters.ContentsCallback
    ) -> linters.MultipleResults:
        '''Runs the linter against a subset of files.'''
        del filenames  # unused

        new_contents, original_contents = _generate_new_contents(
            contents_callback)

        return linters.MultipleResults(new_contents, original_contents,
                                       ['api'])

    @property
    def name(self) -> Text:
        '''Gets the name of the linter.'''
        return 'api'


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
