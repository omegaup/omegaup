#!/usr/bin/env python3
'''Unit tests for the problem health check script.

The real SQL is executed against an in-memory SQLite database, so a malformed
query fails the test instead of passing a string comparison. Only the dialect
differences these statements actually hit are translated.

What SQLite cannot check, and is verified against MySQL by hand instead: the
query plans, and the `enum` columns, which SQLite stores as plain text.
'''

import datetime
import os
import re
import sqlite3
import sys
import unittest

from unittest import mock

from typing import Any, Dict, Iterable, List, Optional, Tuple, cast

import mysql.connector.cursor

sys.path.insert(0,
                os.path.dirname(os.path.dirname(os.path.realpath(__file__))))
sys.path.insert(0, os.path.dirname(os.path.realpath(__file__)))
import lib.db  # pylint: disable=wrong-import-position
import problem_health_check  # pylint: disable=wrong-import-position

_TIMESTAMP_FORMAT = '%Y-%m-%d %H:%M:%S'
# `ON DUPLICATE KEY UPDATE x = VALUES(x)` is `DO UPDATE SET x = excluded.x`.
_UPSERT_VALUE_RE = re.compile(r'VALUES\((`\w+`)\)')

_SCHEMA = '''
CREATE TABLE `Problems` (
    `problem_id` INTEGER PRIMARY KEY,
    `visibility` INTEGER NOT NULL DEFAULT 2,
    `deprecated` INTEGER NOT NULL DEFAULT 0,
    `languages` TEXT NOT NULL DEFAULT 'cpp17-gcc',
    `submissions` INTEGER NOT NULL DEFAULT 0,
    `accepted` INTEGER NOT NULL DEFAULT 0,
    `creation_date` TEXT NOT NULL
);
CREATE TABLE `Submissions` (
    `submission_id` INTEGER PRIMARY KEY,
    `problem_id` INTEGER NOT NULL,
    `verdict` TEXT NOT NULL,
    `status` TEXT NOT NULL DEFAULT 'ready',
    `time` TEXT NOT NULL
);
CREATE TABLE `Problem_Health_Checks` (
    `check_id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `problem_id` INTEGER NOT NULL,
    `check_type` TEXT NOT NULL,
    `severity` TEXT NOT NULL DEFAULT 'warning',
    `detail` TEXT,
    `first_detected_at` TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_seen_at` TEXT NOT NULL,
    `resolved_at` TEXT,
    UNIQUE (`problem_id`, `check_type`)
);
'''

_NOW = datetime.datetime(2026, 7, 29, 10, 0, 0)
_IN_WINDOW = _NOW - datetime.timedelta(hours=1)
_BEFORE_WINDOW = _NOW - datetime.timedelta(hours=48)
_OLDER_IN_WINDOW = _NOW - datetime.timedelta(hours=8)
_CREATED_BEFORE = _NOW - datetime.timedelta(days=7)
_ESTABLISHED = _NOW - datetime.timedelta(days=365)
_BRAND_NEW = _NOW - datetime.timedelta(days=1)

sqlite3.register_adapter(datetime.datetime,
                         lambda value: value.strftime(_TIMESTAMP_FORMAT))


def _translate(sql: str) -> str:
    '''Rewrites the MySQL-only constructs these queries use.'''
    sql = sql.replace('%s', '?').replace('NOW()', 'CURRENT_TIMESTAMP')
    sql = sql.replace('ON DUPLICATE KEY UPDATE', 'ON CONFLICT DO UPDATE SET')
    return _UPSERT_VALUE_RE.sub(r'excluded.\1', sql)


def _convert(value: Any) -> Any:
    '''SQLite has no DATETIME, so timestamps come back as strings.'''
    if not isinstance(value, str):
        return value
    try:
        return datetime.datetime.strptime(value, _TIMESTAMP_FORMAT)
    except ValueError:
        return value


class _SqliteCursor:
    '''Dict cursor that runs the translated statements on SQLite.'''

    def __init__(self, conn: sqlite3.Connection) -> None:
        self._cur = conn.cursor()

    @property
    def rowcount(self) -> int:
        '''How many rows the last statement touched.'''
        return self._cur.rowcount

    def execute(self, sql: str, params: Any = None) -> None:
        '''Runs one translated statement.'''
        self._cur.execute(_translate(sql), tuple(params or ()))

    def executemany(self, sql: str, seq_of_params: Iterable[Any]) -> None:
        '''Runs one translated statement once per parameter row.'''
        self._cur.executemany(_translate(sql),
                              [tuple(params) for params in seq_of_params])

    def _row(self, raw: Tuple[Any, ...]) -> Dict[str, Any]:
        names = [column[0] for column in self._cur.description]
        return dict(zip(names, (_convert(value) for value in raw)))

    def fetchall(self) -> List[Dict[str, Any]]:
        '''Returns the remaining rows of the last statement.'''
        return [self._row(raw) for raw in self._cur.fetchall()]

    def fetchone(self) -> Optional[Dict[str, Any]]:
        '''Returns the next row of the last statement, or None.'''
        raw = self._cur.fetchone()
        return None if raw is None else self._row(raw)

    def __enter__(self) -> '_SqliteCursor':
        return self

    def __exit__(self, exc_type: Any, exc: Any, traceback: Any) -> None:
        del exc_type, exc, traceback


class _SqliteConnection:
    '''Stands in for `lib.db.Connection` over an in-memory database.'''

    def __init__(self) -> None:
        self.conn = sqlite3.connect(':memory:')
        self.conn.executescript(_SCHEMA)

    def cursor(self, **_kwargs: Any) -> _SqliteCursor:
        '''Returns a cursor bound to this connection.'''
        return _SqliteCursor(self.conn)


class _Fixture(unittest.TestCase):
    '''Seeds rows and runs the checks against them.'''

    def setUp(self) -> None:
        self.db = _SqliteConnection()

    @property
    def dbconn(self) -> lib.db.Connection:
        '''The database, typed as the connection the script expects.'''
        return cast(lib.db.Connection, self.db)

    @property
    def cursor(self) -> mysql.connector.cursor.MySQLCursorBufferedDict:
        '''A cursor, typed as the one the checks expect.'''
        return cast(mysql.connector.cursor.MySQLCursorBufferedDict,
                    self.db.cursor())

    # pylint: disable=too-many-arguments
    def add_problem(self,
                    problem_id: int,
                    visibility: int = 2,
                    deprecated: int = 0,
                    languages: str = 'cpp17-gcc',
                    submissions: int = 0,
                    accepted: int = 0,
                    creation_date: datetime.datetime = _ESTABLISHED) -> None:
        '''Seeds one row in `Problems`.'''
        self.db.conn.execute(
            'INSERT INTO `Problems` VALUES (?, ?, ?, ?, ?, ?, ?)',
            (problem_id, visibility, deprecated, languages, submissions,
             accepted, creation_date))
        self.db.conn.commit()

    def add_submissions(self,
                        problem_id: int,
                        verdict: str,
                        count: int,
                        when: datetime.datetime = _IN_WINDOW,
                        status: str = 'ready') -> None:
        '''Seeds `count` submissions on one problem.'''
        for _ in range(count):
            self.db.conn.execute(
                'INSERT INTO `Submissions` '
                '(`problem_id`, `verdict`, `status`, `time`) '
                'VALUES (?, ?, ?, ?)',
                (problem_id, verdict, status, when))
        self.db.conn.commit()

    def open_findings(self) -> List[Dict[str, Any]]:
        '''Returns every stored finding, oldest first.'''
        cur = self.db.cursor()
        cur.execute('SELECT * FROM `Problem_Health_Checks` '
                    'ORDER BY `check_id`')
        return cur.fetchall()


class TestJudgeErrors(_Fixture):
    '''Test the judge error check against seeded submissions.'''

    def test_errors_inside_the_window_are_reported(self) -> None:
        '''Enough failures on one problem raise an error finding.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 3)
        self.add_submissions(7, 'AC', 2)

        findings = problem_health_check.find_judge_errors(
            self.cursor, _NOW)

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].problem_id, 7)
        self.assertEqual(findings[0].check_type, 'judge_errors')
        self.assertEqual(findings[0].severity, 'error')
        self.assertEqual(findings[0].detail,
                         '3 of 5 submissions in the last 24h ended in a '
                         'judge or validator error')

    def test_errors_before_the_window_are_ignored(self) -> None:
        '''Failures older than the window are not this run's problem.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 8, when=_BEFORE_WINDOW)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_too_few_errors_are_ignored(self) -> None:
        '''A couple of failures is not enough to raise a finding.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 2)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_a_small_share_of_errors_is_ignored(self) -> None:
        '''Failures the problem's own traffic dwarfs are flakiness.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 3)
        self.add_submissions(7, 'AC', 27)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_a_quiet_problem_failing_every_time_is_reported(self) -> None:
        '''Three submissions that all failed still count.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 3)
        self.add_problem(8)
        self.add_submissions(8, 'AC', 500)

        findings = problem_health_check.find_judge_errors(
            self.cursor, _NOW)

        self.assertEqual([finding.problem_id for finding in findings], [7])

    def test_validator_errors_count_as_well(self) -> None:
        '''`VE` means the problem's own validator broke.'''
        self.add_problem(7)
        self.add_submissions(7, 'VE', 2)
        self.add_submissions(7, 'JE', 1)

        findings = problem_health_check.find_judge_errors(
            self.cursor, _NOW)

        self.assertEqual(len(findings), 1)
        self.assertIn('3 of 3 submissions', findings[0].detail)

    def test_ordinary_verdicts_are_not_errors(self) -> None:
        '''Wrong answers are the student's problem, not the site's.'''
        self.add_problem(7)
        self.add_submissions(7, 'WA', 10)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_deprecated_problems_are_excluded(self) -> None:
        '''Nobody is being served by a deprecated problem.'''
        self.add_problem(7, deprecated=1)
        self.add_submissions(7, 'JE', 8)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_private_problems_are_excluded(self) -> None:
        '''A private problem is not on offer to students.'''
        self.add_problem(7, visibility=0)
        self.add_submissions(7, 'JE', 8)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_a_problem_under_a_visibility_warning_is_excluded(self) -> None:
        '''Visibility 1 is not in the catalog, so nobody is served it.'''
        self.add_problem(7, visibility=1)
        self.add_submissions(7, 'JE', 3)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_a_promoted_problem_is_included(self) -> None:
        '''Visibility 3 is above the catalog threshold, not below it.'''
        self.add_problem(7, visibility=3)
        self.add_submissions(7, 'JE', 3)

        self.assertEqual(
            len(problem_health_check.find_judge_errors(self.cursor, _NOW)),
            1)

    def test_the_thresholds_are_honoured(self) -> None:
        '''A caller that lowers both thresholds sees the finding.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 1)
        self.add_submissions(7, 'AC', 9)

        findings = problem_health_check.find_judge_errors(self.cursor,
                                                          _NOW,
                                                          min_errors=1,
                                                          min_ratio=0.1)

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].detail,
                         '1 of 10 submissions in the last 24h ended in a '
                         'judge or validator error')

    def test_each_problem_is_counted_on_its_own(self) -> None:
        '''Failures spread over the site do not add up to a finding.'''
        for problem_id in range(1, 6):
            self.add_problem(problem_id)
            self.add_submissions(problem_id, 'JE', 1)
            self.add_submissions(problem_id, 'AC', 9)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_unjudged_submissions_are_not_errors(self) -> None:
        '''A submission still waiting for a runner was never judged.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 8, status='new')

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor, _NOW), [])

    def test_unjudged_submissions_do_not_pad_the_ratio(self) -> None:
        '''Counting them in the denominator would hide a broken problem.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 3)
        self.add_submissions(7, 'AC', 27, status='new')

        findings = problem_health_check.find_judge_errors(self.cursor, _NOW)

        self.assertEqual(len(findings), 1)
        self.assertIn('3 of 3 submissions', findings[0].detail)

    def test_a_shorter_window_excludes_older_errors(self) -> None:
        '''The window the caller asks for is the one the query uses.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 8, when=_OLDER_IN_WINDOW)

        self.assertEqual(
            problem_health_check.find_judge_errors(self.cursor,
                                                   _NOW,
                                                   window_hours=6), [])

    def test_the_detail_names_the_window_it_used(self) -> None:
        '''The counts come from the window, so the text has to say so.'''
        self.add_problem(7)
        self.add_submissions(7, 'JE', 3)

        findings = problem_health_check.find_judge_errors(self.cursor,
                                                          _NOW,
                                                          window_hours=6)

        self.assertEqual(findings[0].detail,
                         '3 of 3 submissions in the last 6h ended in a '
                         'judge or validator error')


class TestNoLanguages(_Fixture):
    '''Test the enabled languages check.'''

    def test_an_empty_language_list_is_reported(self) -> None:
        '''A public problem nobody can submit to is an error.'''
        self.add_problem(3, languages='')

        findings = problem_health_check.find_problems_without_languages(
            self.cursor)

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].problem_id, 3)
        self.assertEqual(findings[0].check_type, 'no_languages')
        self.assertEqual(findings[0].severity, 'error')

    def test_a_problem_with_languages_is_healthy(self) -> None:
        '''The usual case reports nothing.'''
        self.add_problem(3, languages='cpp17-gcc,py3')

        self.assertEqual(
            problem_health_check.find_problems_without_languages(self.cursor),
            [])

    def test_private_and_deprecated_problems_are_excluded(self) -> None:
        '''Only problems on offer to users are reported.'''
        self.add_problem(3, visibility=0, languages='')
        self.add_problem(4, deprecated=1, languages='')

        self.assertEqual(
            problem_health_check.find_problems_without_languages(self.cursor),
            [])


class TestNeverSolved(_Fixture):
    '''Test the never solved check.'''

    def test_many_attempts_and_no_success_is_reported(self) -> None:
        '''A problem nobody solved is reported with how many tried.'''
        self.add_problem(5, submissions=42, accepted=0)

        findings = problem_health_check.find_never_solved(
            self.cursor, _CREATED_BEFORE)

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].problem_id, 5)
        self.assertEqual(findings[0].check_type, 'never_solved')
        self.assertEqual(findings[0].severity, 'warning')
        self.assertIn('42 submissions', findings[0].detail)

    def test_too_few_attempts_are_ignored(self) -> None:
        '''Below the threshold nobody has really tried yet.'''
        self.add_problem(5, submissions=19, accepted=0)

        self.assertEqual(
            problem_health_check.find_never_solved(self.cursor,
                                                   _CREATED_BEFORE), [])

    def test_a_solved_problem_is_healthy(self) -> None:
        '''One accepted solution proves the problem works.'''
        self.add_problem(5, submissions=42, accepted=1)

        self.assertEqual(
            problem_health_check.find_never_solved(self.cursor,
                                                   _CREATED_BEFORE), [])

    def test_a_brand_new_problem_is_given_time(self) -> None:
        '''A hard problem published yesterday is not yet a finding.'''
        self.add_problem(5,
                         submissions=42,
                         accepted=0,
                         creation_date=_BRAND_NEW)

        self.assertEqual(
            problem_health_check.find_never_solved(self.cursor,
                                                   _CREATED_BEFORE), [])

    def test_a_problem_with_no_languages_is_left_to_that_check(self) -> None:
        '''Nobody can solve it, and `no_languages` already reports it.'''
        self.add_problem(5, submissions=42, accepted=0, languages='')

        self.assertEqual(
            problem_health_check.find_never_solved(self.cursor,
                                                   _CREATED_BEFORE), [])

    def test_private_and_deprecated_problems_are_excluded(self) -> None:
        '''Only problems on offer to users are reported.'''
        self.add_problem(5, visibility=0, submissions=42)
        self.add_problem(6, deprecated=1, submissions=42)

        self.assertEqual(
            problem_health_check.find_never_solved(self.cursor,
                                                   _CREATED_BEFORE), [])

    def test_the_threshold_is_honoured(self) -> None:
        '''A caller that lowers the threshold sees the finding.'''
        self.add_problem(5, submissions=7, accepted=0)

        self.assertEqual(
            len(
                problem_health_check.find_never_solved(self.cursor,
                                                       _CREATED_BEFORE,
                                                       min_submissions=7)), 1)


class TestDeprecatedPublic(_Fixture):
    '''Test the deprecated but public check.'''

    def test_deprecated_and_public_is_reported(self) -> None:
        '''Both halves together are the finding.'''
        self.add_problem(11, deprecated=1, visibility=2)

        findings = problem_health_check.find_deprecated_but_public(self.cursor)

        self.assertEqual(len(findings), 1)
        self.assertEqual(findings[0].problem_id, 11)
        self.assertEqual(findings[0].check_type, 'deprecated_public')

    def test_deprecated_and_private_is_healthy(self) -> None:
        '''Retiring a problem properly is not a finding.'''
        self.add_problem(11, deprecated=1, visibility=0)

        self.assertEqual(
            problem_health_check.find_deprecated_but_public(self.cursor), [])

    def test_public_and_current_is_healthy(self) -> None:
        '''An ordinary public problem is not a finding.'''
        self.add_problem(11, deprecated=0, visibility=2)

        self.assertEqual(
            problem_health_check.find_deprecated_but_public(self.cursor), [])


class TestRecording(_Fixture):
    '''Test how findings are stored and resolved.'''

    def setUp(self) -> None:
        super().setUp()
        self.add_problem(4)
        self.finding = problem_health_check.Finding(
            problem_id=4,
            check_type='never_solved',
            severity='warning',
            detail='30 submissions and no accepted solution yet')

    def test_a_finding_is_stored(self) -> None:
        '''One row per finding, carrying this run's timestamp.'''
        problem_health_check.apply_findings(self.dbconn, [self.finding], _NOW)

        rows = self.open_findings()
        self.assertEqual(len(rows), 1)
        self.assertEqual(rows[0]['problem_id'], 4)
        self.assertEqual(rows[0]['check_type'], 'never_solved')
        self.assertEqual(rows[0]['severity'], 'warning')
        self.assertEqual(rows[0]['detail'], self.finding.detail)
        self.assertEqual(rows[0]['last_seen_at'], _NOW)
        self.assertIsNone(rows[0]['resolved_at'])

    def test_nothing_to_record_writes_nothing(self) -> None:
        '''A healthy run leaves the table alone.'''
        problem_health_check.record_findings(self.dbconn, [], _NOW)

        self.assertEqual(self.open_findings(), [])

    def test_a_second_run_keeps_the_first_detected_date(self) -> None:
        '''Seeing the same finding again must not reset its age.'''
        problem_health_check.record_findings(self.dbconn, [self.finding], _NOW)
        self.db.conn.execute('UPDATE `Problem_Health_Checks` '
                             'SET `first_detected_at` = ?', (_ESTABLISHED,))
        self.db.conn.commit()
        later = _NOW + datetime.timedelta(days=1)
        worse = self.finding._replace(severity='error', detail='31 now')

        problem_health_check.record_findings(self.dbconn, [worse], later)

        rows = self.open_findings()
        self.assertEqual(len(rows), 1)
        self.assertEqual(rows[0]['first_detected_at'], _ESTABLISHED)
        self.assertEqual(rows[0]['last_seen_at'], later)
        self.assertEqual(rows[0]['severity'], 'error')
        self.assertEqual(rows[0]['detail'], '31 now')

    def test_a_recurrence_is_dated_from_the_new_incident(self) -> None:
        '''Detect, resolve, detect again: the age is of this incident.'''
        january = _NOW - datetime.timedelta(days=200)
        february = _NOW - datetime.timedelta(days=170)
        problem_health_check.apply_findings(self.dbconn, [self.finding],
                                            january)
        problem_health_check.apply_findings(self.dbconn, [], february)
        self.assertEqual(self.open_findings()[0]['resolved_at'], february)

        problem_health_check.apply_findings(self.dbconn, [self.finding], _NOW)

        row = self.open_findings()[0]
        self.assertEqual(row['first_detected_at'], _NOW)
        self.assertEqual(row['last_seen_at'], _NOW)
        self.assertIsNone(row['resolved_at'])

    def test_an_uninterrupted_finding_keeps_its_original_date(self) -> None:
        '''Only a resolve starts a new incident, not an ordinary rerun.'''
        january = _NOW - datetime.timedelta(days=200)
        problem_health_check.apply_findings(self.dbconn, [self.finding],
                                            january)

        problem_health_check.apply_findings(self.dbconn, [self.finding], _NOW)

        row = self.open_findings()[0]
        self.assertEqual(row['first_detected_at'], january)
        self.assertEqual(row['last_seen_at'], _NOW)

    def test_a_finding_that_comes_back_is_reopened(self) -> None:
        '''Recording a resolved finding again clears `resolved_at`.'''
        problem_health_check.record_findings(self.dbconn, [self.finding], _NOW)
        self.db.conn.execute('UPDATE `Problem_Health_Checks` '
                             'SET `resolved_at` = ?', (_NOW,))
        self.db.conn.commit()

        later = _NOW + datetime.timedelta(days=1)
        problem_health_check.record_findings(self.dbconn, [self.finding],
                                             later)

        self.assertIsNone(self.open_findings()[0]['resolved_at'])

    def test_only_findings_not_seen_again_are_resolved(self) -> None:
        '''What this run still detects stays open.'''
        self.add_problem(5)
        gone = self.finding._replace(problem_id=5, check_type='no_languages')
        problem_health_check.record_findings(self.dbconn,
                                             [self.finding, gone], _NOW)

        later = _NOW + datetime.timedelta(days=1)
        problem_health_check.record_findings(self.dbconn, [self.finding],
                                             later)
        resolved = problem_health_check.resolve_missing_findings(
            self.dbconn, later)

        self.assertEqual(resolved, 1)
        rows = {row['problem_id']: row for row in self.open_findings()}
        self.assertIsNone(rows[4]['resolved_at'])
        self.assertEqual(rows[5]['resolved_at'], later)

    def test_an_already_resolved_finding_is_not_resolved_twice(self) -> None:
        '''The count only reports what this run actually closed.'''
        problem_health_check.record_findings(self.dbconn, [self.finding], _NOW)
        later = _NOW + datetime.timedelta(days=1)
        self.assertEqual(
            problem_health_check.resolve_missing_findings(self.dbconn, later),
            1)

        later_still = later + datetime.timedelta(days=1)
        self.assertEqual(
            problem_health_check.resolve_missing_findings(
                self.dbconn, later_still), 0)

    def test_apply_findings_records_and_resolves_in_one_step(self) -> None:
        '''The two writes share the timestamp they are given.'''
        self.add_problem(5)
        gone = self.finding._replace(problem_id=5, check_type='no_languages')
        problem_health_check.record_findings(self.dbconn, [gone], _NOW)

        later = _NOW + datetime.timedelta(days=1)
        resolved = problem_health_check.apply_findings(self.dbconn,
                                                       [self.finding], later)

        self.assertEqual(resolved, 1)
        rows = {row['problem_id']: row for row in self.open_findings()}
        self.assertEqual(rows[4]['last_seen_at'], later)
        self.assertIsNone(rows[4]['resolved_at'])
        self.assertEqual(rows[5]['resolved_at'], later)

    def test_a_finding_stored_now_is_not_resolved_by_the_same_run(
            self) -> None:
        '''The run timestamp is the same on both sides of the comparison.'''
        resolved = problem_health_check.apply_findings(self.dbconn,
                                                       [self.finding], _NOW)

        self.assertEqual(resolved, 0)
        self.assertIsNone(self.open_findings()[0]['resolved_at'])

    def test_a_finding_seen_again_is_not_counted_as_resolved(self) -> None:
        '''Recording has to happen before resolving, not after.'''
        earlier = _NOW - datetime.timedelta(days=1)
        problem_health_check.record_findings(self.dbconn, [self.finding],
                                             earlier)

        resolved = problem_health_check.apply_findings(self.dbconn,
                                                       [self.finding], _NOW)

        self.assertEqual(resolved, 0)
        self.assertIsNone(self.open_findings()[0]['resolved_at'])

    def test_a_failed_resolve_takes_the_findings_with_it(self) -> None:
        '''Half-written state would misreport which findings are open.'''
        with mock.patch.object(problem_health_check,
                               'resolve_missing_findings',
                               side_effect=RuntimeError('boom')):
            with self.assertRaises(RuntimeError):
                problem_health_check.apply_findings(self.dbconn,
                                                    [self.finding], _NOW)

        self.assertEqual(self.open_findings(), [])

    def test_the_clock_comes_from_the_database(self) -> None:
        '''The run timestamp is a datetime, not a formatted string.'''
        self.assertIsInstance(
            problem_health_check.current_timestamp(self.dbconn),
            datetime.datetime)


class TestParser(unittest.TestCase):
    '''Test the command line interface.'''

    def test_the_defaults_match_the_module(self) -> None:
        '''Every threshold is reachable from the command line.'''
        args = problem_health_check.build_parser().parse_args([])

        self.assertEqual(args.judge_error_window_hours, 24)
        self.assertEqual(args.min_judge_errors, 3)
        self.assertEqual(args.min_judge_error_ratio, 0.3)
        self.assertEqual(args.min_submissions_never_solved, 20)
        self.assertEqual(args.min_age_days_never_solved, 7)

    def test_the_thresholds_can_be_overridden(self) -> None:
        '''The window and the ratio are tunable without a code change.'''
        args = problem_health_check.build_parser().parse_args(
            ['--judge-error-window-hours', '6', '--min-judge-error-ratio',
             '0.75'])

        self.assertEqual(args.judge_error_window_hours, 6)
        self.assertEqual(args.min_judge_error_ratio, 0.75)


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
