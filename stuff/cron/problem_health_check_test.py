#!/usr/bin/env python3
'''Unit tests for the problem health check script.

These use an in-memory fake connection so no real database is required.
'''

import os
import sys
import unittest

from typing import Any, Dict, List, Sequence, Tuple, cast

import mysql.connector.cursor

sys.path.insert(0,
                os.path.dirname(os.path.dirname(os.path.realpath(__file__))))
sys.path.insert(0, os.path.dirname(os.path.realpath(__file__)))
import lib.db  # pylint: disable=wrong-import-position
import problem_health_check  # pylint: disable=wrong-import-position


class _FakeCursor:
    '''Returns scripted rows and records the statements it was given.'''

    def __init__(self, connection: '_FakeConnection') -> None:
        self._connection = connection

    def execute(self, query: str, params: Any = None) -> None:
        '''Records the statement and its params.'''
        self._connection.calls.append((query, params))

    def fetchall(self) -> Sequence[Dict[str, Any]]:
        '''Returns the scripted result set.'''
        return self._connection.rows

    def fetchone(self) -> Any:
        '''Returns the scripted single row.'''
        if self._connection.calls and 'NOW()' in self._connection.calls[-1][0]:
            return (self._connection.now(),)
        return self._connection.single_row

    def __enter__(self) -> '_FakeCursor':
        return self

    def __exit__(self, exc_type: Any, exc: Any, traceback: Any) -> None:
        del exc_type, exc, traceback


class _FakeConnection:
    '''Minimal stand-in for lib.db.Connection.'''

    def __init__(self, rows: Sequence[Dict[str, Any]] = ()) -> None:
        self.rows: Sequence[Dict[str, Any]] = rows
        self.single_row: Any = None
        self.calls: List[Tuple[str, Any]] = []
        self.commits = 0
        self.clock_reads = 0
        self.conn = self

    def now(self) -> str:
        '''Advances the fake clock, so two reads never agree.'''
        self.clock_reads += 1
        return f'2026-07-29 10:00:{self.clock_reads:02d}'

    def cursor(self, **_kwargs: Any) -> _FakeCursor:
        '''Returns a fake cursor bound to this connection.'''
        return _FakeCursor(self)

    def commit(self) -> None:
        '''Counts commits so a test can assert one happened.'''
        self.commits += 1


def _cursor(rows: Sequence[Dict[str, Any]]) -> Any:
    '''Builds a fake cursor that will answer with the given rows.'''
    return cast(mysql.connector.cursor.MySQLCursorBufferedDict,
                _FakeConnection(rows).cursor())


def _cursor_of(conn: _FakeConnection) -> Any:
    '''Builds a fake cursor whose statements the test can inspect.'''
    return cast(mysql.connector.cursor.MySQLCursorBufferedDict, conn.cursor())


class TestChecks(unittest.TestCase):
    '''Test that each check turns database rows into findings.'''

    def test_judge_errors_reports_broken_judging(self) -> None:
        '''Runs that fail to be judged are reported as an error.'''
        findings = problem_health_check.find_judge_errors(
            _cursor([{'problem_id': 7, 'error_count': 9}]))

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].problem_id, 7)
        self.assertEqual(findings[0].check_type, 'judge_errors')
        self.assertEqual(findings[0].severity, 'error')
        self.assertIn('9 of the last', findings[0].detail)

    def test_no_languages_reports_unusable_problem(self) -> None:
        '''A public problem with no language is reported as an error.'''
        findings = problem_health_check.find_problems_without_languages(
            _cursor([{'problem_id': 3}]))

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].check_type, 'no_languages')
        self.assertEqual(findings[0].severity, 'error')

    def test_never_solved_reports_submission_count(self) -> None:
        '''A problem nobody solved is reported with how many tried.'''
        findings = problem_health_check.find_never_solved(
            _cursor([{'problem_id': 5, 'submissions': 42}]))

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].check_type, 'never_solved')
        self.assertEqual(findings[0].severity, 'warning')
        self.assertIn('42 submissions', findings[0].detail)

    def test_deprecated_public_is_reported(self) -> None:
        '''A deprecated problem that is still public is reported.'''
        findings = problem_health_check.find_deprecated_but_public(
            _cursor([{'problem_id': 11}]))

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].check_type, 'deprecated_public')

    def test_judge_errors_passes_its_thresholds_to_the_query(self) -> None:
        '''The sample size and the minimum both reach the statement.'''
        conn = _FakeConnection([])

        problem_health_check.find_judge_errors(_cursor_of(conn),
                                               min_errors=3,
                                               sample=200)

        self.assertEqual(conn.calls[0][1], (200, 3))

    def test_never_solved_passes_its_threshold_to_the_query(self) -> None:
        '''The submission count threshold reaches the statement.'''
        conn = _FakeConnection([])

        problem_health_check.find_never_solved(_cursor_of(conn),
                                               min_submissions=7)

        self.assertEqual(conn.calls[0][1], (7,))

    def test_no_languages_looks_at_the_languages_column(self) -> None:
        '''`Problems_Languages` holds statement translations, not code.'''
        conn = _FakeConnection([])

        problem_health_check.find_problems_without_languages(_cursor_of(conn))

        query = conn.calls[0][0]
        self.assertIn("`languages` = ''", query)
        self.assertNotIn('Problems_Languages', query)

    def test_checks_return_nothing_when_healthy(self) -> None:
        '''No rows from the database means no findings.'''
        self.assertEqual(
            problem_health_check.find_deprecated_but_public(_cursor([])), [])
        self.assertEqual(
            problem_health_check.find_problems_without_languages(_cursor([])),
            [])


class TestRecording(unittest.TestCase):
    '''Test how findings are stored and resolved.'''

    def test_record_findings_upserts_with_the_run_timestamp(self) -> None:
        '''Each finding is written with this run's timestamp.'''
        conn = _FakeConnection()
        finding = problem_health_check.Finding(
            problem_id=4,
            check_type='never_solved',
            severity='warning',
            detail='30 submissions and no accepted solution yet')

        problem_health_check.record_findings(cast(lib.db.Connection, conn),
                                             [finding], '2026-07-29 10:00:00')

        self.assertEqual(len(conn.calls), 1)
        query, params = conn.calls[0]
        self.assertIn('ON DUPLICATE KEY UPDATE', query)
        self.assertEqual(
            params,
            (4, 'never_solved', 'warning', finding.detail,
             '2026-07-29 10:00:00'))
        self.assertEqual(conn.commits, 1)

    def test_record_findings_with_nothing_to_record(self) -> None:
        '''A healthy run writes no rows.'''
        conn = _FakeConnection()

        problem_health_check.record_findings(cast(lib.db.Connection, conn), [],
                                             '2026-07-29 10:00:00')

        self.assertEqual(conn.calls, [])

    def test_resolve_uses_the_same_run_timestamp(self) -> None:
        '''Findings not seen in this run are closed with the run timestamp.'''
        conn = _FakeConnection()
        conn.single_row = {'resolved': 2}

        resolved = problem_health_check.resolve_missing_findings(
            cast(lib.db.Connection, conn), '2026-07-29 10:00:00')

        self.assertEqual(resolved, 2)
        query, params = conn.calls[0]
        self.assertIn('resolved_at', query)
        self.assertEqual(params, ('2026-07-29 10:00:00',
                                  '2026-07-29 10:00:00'))

    def test_record_findings_keeps_the_first_detected_date(self) -> None:
        '''The upsert never writes `first_detected_at`, so it is kept.'''
        conn = _FakeConnection()
        finding = problem_health_check.Finding(
            problem_id=4,
            check_type='never_solved',
            severity='warning',
            detail='30 submissions and no accepted solution yet')

        problem_health_check.record_findings(cast(lib.db.Connection, conn),
                                             [finding], '2026-07-29 10:00:00')

        query, params = conn.calls[0]
        self.assertNotIn('first_detected_at', query)
        self.assertEqual(params[:2], (4, 'never_solved'))

    def test_clock_is_read_once_before_any_write(self) -> None:
        '''A finding written now must not be closed by the same run.'''
        conn = _FakeConnection()
        conn.single_row = {'resolved': 1}
        finding = problem_health_check.Finding(
            problem_id=4,
            check_type='never_solved',
            severity='warning',
            detail='30 submissions and no accepted solution yet')

        resolved = problem_health_check.apply_findings(
            cast(lib.db.Connection, conn), [finding])

        queries = [query for query, _ in conn.calls]
        self.assertIn('NOW()', queries[0])
        self.assertIn('ON DUPLICATE KEY UPDATE', queries[1])
        self.assertIn('`resolved_at` = %s', queries[2])
        self.assertEqual(conn.clock_reads, 1)
        self.assertEqual(conn.calls[1][1][-1], '2026-07-29 10:00:01')
        self.assertEqual(conn.calls[2][1],
                         ('2026-07-29 10:00:01', '2026-07-29 10:00:01'))
        self.assertEqual(resolved, 1)


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
