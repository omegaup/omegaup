#!/usr/bin/env python3
'''Unit tests for translation_strings_linter.'''

import os
import subprocess
import sys
import types
import unittest
from dataclasses import dataclass
from typing import Any, Optional, Sequence
from unittest import mock

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Prevent Git "dubious ownership" error in CI containers where /opt/omegaup
# is mounted from the host runner under a different UID.
try:
    subprocess.run(
        ['git', 'config', '--global', '--add', 'safe.directory', '*'],
        check=False,
        capture_output=True,
    )
    subprocess.run(
        ['git', 'config', '--global', '--add', 'safe.directory',
         '/opt/omegaup'],
        check=False,
        capture_output=True,
    )
except (subprocess.SubprocessError, OSError):
    pass

_orig_check_output = subprocess.check_output
_orig_run = subprocess.run


def _safe_check_output(*args: Any, **kwargs: Any) -> Any:
    try:
        return _orig_check_output(*args, **kwargs)
    except subprocess.CalledProcessError as exc:
        if (args and isinstance(args[0], (list, tuple))
                and 'rev-parse' in args[0]):
            if kwargs.get('universal_newlines') or kwargs.get('text'):
                return '/opt/omegaup\n'
            return b'/opt/omegaup\n'
        raise exc


def _safe_run(*args: Any, **kwargs: Any) -> Any:
    try:
        # pylint: disable=subprocess-run-check
        return _orig_run(*args, **kwargs)
    except subprocess.CalledProcessError as exc:
        if (args and isinstance(args[0], (list, tuple))
                and 'rev-parse' in args[0]):
            use_text = (kwargs.get('universal_newlines')
                        or kwargs.get('text'))
            stdout = '/opt/omegaup\n' if use_text else b'/opt/omegaup\n'
            return subprocess.CompletedProcess(
                args=args[0], returncode=0, stdout=stdout, stderr=b''
            )
        raise exc


subprocess.check_output = _safe_check_output  # type: ignore
subprocess.run = _safe_run  # type: ignore

try:
    try:
        from omegaup_hook_tools import linters
    finally:
        subprocess.check_output = _orig_check_output
        subprocess.run = _orig_run
except ImportError:
    # Stub omegaup_hook_tools in environments where it is not installed.
    _pkg = types.ModuleType('omegaup_hook_tools')
    _linters = types.ModuleType('omegaup_hook_tools.linters')

    @dataclass
    class _Diagnostic:
        message: str
        filename: str
        line: Optional[str] = None
        lineno: Optional[int] = None
        col: Optional[int] = None
        col_end: Optional[int] = None
        level: str = 'error'

    class _LinterException(Exception):
        def __init__(self, message: str, fixable: bool,
                     diagnostics: Sequence[_Diagnostic]):
            super().__init__(message)
            self.message = message
            self.fixable = fixable
            self.diagnostics = diagnostics

    class _SingleResult:
        def __init__(self, contents: bytes, violations: Sequence[str]):
            self.contents = contents
            self.violations = violations

    class _Linter:
        pass

    class _Options:
        pass

    _linters.Diagnostic = _Diagnostic  # type: ignore
    _linters.LinterException = _LinterException  # type: ignore
    _linters.SingleResult = _SingleResult  # type: ignore
    _linters.Linter = _Linter  # type: ignore
    _linters.Options = _Options  # type: ignore
    _pkg.linters = _linters  # type: ignore

    sys.modules['omegaup_hook_tools'] = _pkg
    sys.modules['omegaup_hook_tools.linters'] = _linters

    linters = _linters  # type: ignore

import translation_strings_linter  # pylint: disable=wrong-import-position

_SAMPLE_EN_LANG = '''
aboutToStart = "You are about to start the contest."
words = "Words"
'''


class TranslationStringsLinterTest(unittest.TestCase):
    '''Test cases for TranslationStringsLinter.'''

    def setUp(self) -> None:
        with mock.patch('builtins.open',
                        mock.mock_open(read_data=_SAMPLE_EN_LANG)):
            self.linter = translation_strings_linter.TranslationStringsLinter()

    def test_linter_name(self) -> None:
        '''Test that the linter returns the expected name.'''
        self.assertEqual(self.linter.name, 'translation_strings')

    def test_valid_utf8_file_without_translation_strings(self) -> None:
        '''Test that valid UTF-8 file with no translation strings passes.'''
        contents = b"const value = 42;\nconsole.log(value);\n"
        result = self.linter.run_one('frontend/www/js/sample.js', contents)
        self.assertEqual(result.contents, contents)
        self.assertEqual(result.violations, ['translation_strings'])

    def test_valid_utf8_file_with_valid_translation_strings(self) -> None:
        '''Test that valid UTF-8 file with valid strings passes.'''
        contents = b"const title = T.aboutToStart;\n"
        result = self.linter.run_one('frontend/www/js/sample.js', contents)
        self.assertEqual(result.contents, contents)
        self.assertEqual(result.violations, ['translation_strings'])

    def test_missing_translation_string_raises_linter_exception(self) -> None:
        '''Test that missing translation string raises LinterException.'''
        contents = b"const title = T.nonExistentKeyInTemplates;\n"
        with self.assertRaises(linters.LinterException) as ctx:
            self.linter.run_one('frontend/www/js/sample.js', contents)

        exc = ctx.exception
        self.assertEqual(exc.message, 'Missing translation strings')
        self.assertFalse(exc.fixable)
        self.assertEqual(len(exc.diagnostics), 1)
        diag = exc.diagnostics[0]
        self.assertEqual(diag.filename, 'frontend/www/js/sample.js')
        self.assertEqual(diag.lineno, 1)
        self.assertIn("Missing translation string 'nonExistentKeyInTemplates'",
                      diag.message)

    def test_tpl_file_missing_translation_string(self) -> None:
        '''Test that .tpl file with missing string uses template regex.'''
        contents = b"<h1>{#missingKeyInTemplates#}</h1>\n"
        with self.assertRaises(linters.LinterException) as ctx:
            self.linter.run_one('frontend/templates/sample.tpl', contents)

        exc = ctx.exception
        self.assertEqual(exc.message, 'Missing translation strings')
        diag = exc.diagnostics[0]
        self.assertEqual(diag.filename, 'frontend/templates/sample.tpl')
        self.assertEqual(diag.lineno, 1)
        self.assertIn("Missing translation string 'missingKeyInTemplates'",
                      diag.message)

    def test_invalid_utf8_on_first_line_raises_diagnostic(self) -> None:
        '''Test that non-UTF-8 bytes on line 1 raise clean diagnostic.'''
        contents = b"const greeting = 'caf\xe9';\n"
        with self.assertRaises(linters.LinterException) as ctx:
            self.linter.run_one('frontend/www/js/sample.js', contents)

        exc = ctx.exception
        self.assertEqual(exc.message, 'Encoding error in translation strings')
        self.assertFalse(exc.fixable)
        self.assertEqual(len(exc.diagnostics), 1)
        diag = exc.diagnostics[0]
        self.assertEqual(diag.filename, 'frontend/www/js/sample.js')
        self.assertEqual(diag.lineno, 1)
        self.assertIn('File is not valid UTF-8:', diag.message)

    def test_invalid_utf8_on_later_line_calculates_lineno(self) -> None:
        '''Test that invalid UTF-8 bytes calculate the exact line number.'''
        contents = (
            b"// line 1\n"
            b"// line 2\n"
            b"// line 3\n"
            b"const greeting = 'caf\xe9';\n"
            b"// line 5\n"
        )
        with self.assertRaises(linters.LinterException) as ctx:
            self.linter.run_one('frontend/www/js/sample.js', contents)

        exc = ctx.exception
        self.assertEqual(exc.message, 'Encoding error in translation strings')
        self.assertFalse(exc.fixable)
        self.assertEqual(len(exc.diagnostics), 1)
        diag = exc.diagnostics[0]
        self.assertEqual(diag.filename, 'frontend/www/js/sample.js')
        self.assertEqual(diag.lineno, 4)
        self.assertIn('File is not valid UTF-8:', diag.message)


if __name__ == '__main__':
    unittest.main()
