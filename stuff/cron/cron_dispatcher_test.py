'''Unittests for the cron_dispatcher script.

The request lifecycle runs against SQLite, so the statements are executed
and not only matched by their text.
'''

import argparse
import os
import re
import sqlite3
import sys
import unittest

from typing import Any, Dict, List, Optional, Tuple, cast
from unittest import mock

import mysql.connector.cursor

import lib.db
from cron import cron_dispatcher
from cron.cron_dispatcher import RequestStatus
from cron.tests.fixtures import sqlite_cursor

RunResult = Tuple[int, Optional[str]]

_REGISTERED = ('update_ranks.py', 'assign_badges.py', 'aggregate_feedback.py',
               'build_problem_rec_model.py')

_OMEGAUP_ROOT = os.path.dirname(os.path.dirname(os.path.dirname(
    os.path.realpath(__file__))))

_ARGS = argparse.Namespace(
    run_timeout_seconds=cron_dispatcher.DEFAULT_RUN_TIMEOUT_SECONDS,
    rec_model_output=None)


def _dbconn(connection: Any) -> lib.db.Connection:
    '''Hands a test double to code typed for the real connection.'''
    return cast(lib.db.Connection, connection)


def _dbcur(cursor: Any) -> mysql.connector.cursor.MySQLCursorDict:
    '''Hands a test double to code typed for the real cursor.'''
    return cast(mysql.connector.cursor.MySQLCursorDict, cursor)


def _fake_run(args: argparse.Namespace, name: str) -> RunResult:
    '''A run command that always succeeds.'''
    del args, name
    return (0, None)


class _PingableConnection(sqlite3.Connection):
    '''SQLite has no connection to lose, so the liveness check is a no-op.'''

    def ping(self, **kwargs: Any) -> None:
        '''Accepts what mysql.connector's ping accepts.'''
        del kwargs


class _SqliteConnection:
    '''Just enough of lib.db.Connection for the dispatcher.'''

    def __init__(self) -> None:
        self.conn = sqlite3.connect(':memory:', factory=_PingableConnection)
        for statement in (
            'CREATE TABLE Cron_Jobs (name TEXT);',
            '''
            CREATE TABLE Cron_Runs (
                run_id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT,
                status TEXT DEFAULT 'success');''',
            '''
            CREATE TABLE Cron_Run_Requests (
                request_id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                requested_by INTEGER,
                status TEXT DEFAULT 'pending',
                requested_at TEXT DEFAULT (datetime('now')),
                picked_at TEXT,
                finished_at TEXT,
                run_id INTEGER,
                error_text TEXT);''',
            'CREATE TABLE Notifications (user_id INTEGER, contents TEXT);',
        ):
            self.conn.execute(statement)
        self.conn.executemany('INSERT INTO Cron_Jobs (name) VALUES (?);',
                              [(name,) for name in _REGISTERED])


class RealSqlTest(unittest.TestCase):
    '''Tests for the request lifecycle with the statements executed.'''

    def setUp(self) -> None:
        self.dbconn = _SqliteConnection()
        self.cur = sqlite_cursor.SqliteCursor(self.dbconn.conn)

    def _queue(self, name: str = 'update_ranks.py', **columns: Any) -> int:
        columns = {'name': name, 'requested_by': 5, **columns}
        names = ', '.join(columns)
        marks = ', '.join('?' for _ in columns)
        cursor = self.dbconn.conn.execute(
            f'INSERT INTO Cron_Run_Requests ({names}) VALUES ({marks});',
            tuple(columns.values()))
        assert cursor.lastrowid is not None
        return cursor.lastrowid

    def _ago(self, hours: int) -> str:
        '''A timestamp in the database's own clock and format.'''
        row = self.dbconn.conn.execute(
            "SELECT datetime('now', ?);", (f'-{hours} hours',)).fetchone()
        return str(row[0])

    def _request(self, request_id: int) -> Dict[str, Any]:
        self.cur.execute(
            'SELECT * FROM Cron_Run_Requests WHERE request_id = %s;',
            (request_id,))
        row = self.cur.fetchone()
        assert row is not None
        return row

    def _process(
        self,
        run_command: cron_dispatcher.RunCommand,
    ) -> cron_dispatcher.Summary:
        return cron_dispatcher.process_requests(
            _dbconn(self.dbconn), _dbcur(self.cur), _ARGS,
            run_command=run_command)

    def _child_records_a_run(self, args: argparse.Namespace,
                             name: str) -> RunResult:
        del args
        self.dbconn.conn.execute(
            'INSERT INTO Cron_Runs (name) VALUES (?);', (name,))
        return (0, None)

    def test_only_one_dispatcher_claims_a_request(self) -> None:
        '''The second UPDATE finds the row no longer pending.'''
        request_id = self._queue()

        # pylint: disable=protected-access
        first = cron_dispatcher._claim_request(
            _dbconn(self.dbconn), _dbcur(self.cur), request_id)
        second = cron_dispatcher._claim_request(
            _dbconn(self.dbconn), _dbcur(self.cur), request_id)

        self.assertTrue(first)
        self.assertFalse(second)
        self.assertEqual(
            self._request(request_id)['status'], RequestStatus.PICKED.value)

    def test_a_finished_rerun_is_linked_to_its_run(self) -> None:
        '''The request ends done and points at the row the child wrote.'''
        self.dbconn.conn.execute(
            "INSERT INTO Cron_Runs (name) VALUES ('update_ranks.py');")
        request_id = self._queue()

        summary = self._process(self._child_records_a_run)

        row = self._request(request_id)
        self.assertEqual(summary, cron_dispatcher.Summary(1, 0))
        self.assertEqual(row['status'], RequestStatus.DONE.value)
        self.assertEqual(row['run_id'], 2)
        self.assertIsNotNone(row['finished_at'])
        self.cur.execute('SELECT user_id, contents FROM Notifications;')
        notifications = self.cur.fetchall()
        self.assertEqual(len(notifications), 1)
        self.assertEqual(notifications[0]['user_id'], 5)

    def test_a_run_that_left_no_row_is_a_failure(self) -> None:
        '''An older run of the same job is not mistaken for this one.'''
        self.dbconn.conn.execute(
            "INSERT INTO Cron_Runs (name) VALUES ('update_ranks.py');")
        request_id = self._queue()

        summary = self._process(_fake_run)

        row = self._request(request_id)
        self.assertEqual(summary, cron_dispatcher.Summary(1, 1))
        self.assertEqual(row['status'], RequestStatus.FAILED.value)
        self.assertIsNone(row['run_id'])

    def test_a_scheduled_run_still_in_flight_is_not_ours(self) -> None:
        '''The child skipped because the schedule holds the lock.'''
        request_id = self._queue()

        def scheduled_run_started(args: argparse.Namespace,
                                  name: str) -> RunResult:
            del args
            self.dbconn.conn.execute(
                "INSERT INTO Cron_Runs (name, status) VALUES (?, 'running');",
                (name,))
            return (0, None)

        summary = self._process(scheduled_run_started)

        row = self._request(request_id)
        self.assertEqual(summary, cron_dispatcher.Summary(1, 1))
        self.assertEqual(row['status'], RequestStatus.FAILED.value)
        self.assertIsNone(row['run_id'])

    def test_an_interrupted_run_does_not_stay_picked(self) -> None:
        '''A stop signal in the middle of a run still resolves the request.'''
        request_id = self._queue()

        def interrupted(args: argparse.Namespace, name: str) -> RunResult:
            del args, name
            raise SystemExit(143)

        with self.assertRaises(SystemExit):
            self._process(interrupted)

        row = self._request(request_id)
        self.assertEqual(row['status'], RequestStatus.FAILED.value)
        self.assertEqual(
            row['error_text'],
            'the dispatcher stopped before this run finished')

    def test_an_unregistered_job_is_rejected_without_running(self) -> None:
        '''A name outside the registry never reaches the run command.'''
        request_id = self._queue('rm_everything.py')
        launched: List[str] = []

        def recording(args: argparse.Namespace, name: str) -> RunResult:
            del args
            launched.append(name)
            return (0, None)

        summary = self._process(recording)

        self.assertEqual(launched, [])
        self.assertEqual(summary, cron_dispatcher.Summary(1, 1))
        self.assertEqual(
            self._request(request_id)['status'], RequestStatus.FAILED.value)

    def test_requests_run_oldest_first(self) -> None:
        '''The queue is drained in the order it was filled.'''
        self._queue('assign_badges.py', requested_at='2026-01-02 00:00:00')
        self._queue('update_ranks.py', requested_at='2026-01-01 00:00:00')

        pending = cron_dispatcher.get_pending_requests(_dbcur(self.cur))

        self.assertEqual([request.name for request in pending],
                         ['update_ranks.py', 'assign_badges.py'])

    def test_the_reaper_only_takes_abandoned_requests(self) -> None:
        '''A run that can still be alive is left alone.'''
        abandoned = self._queue(status='picked', picked_at=self._ago(8))
        running = self._queue(status='picked', picked_at=self._ago(1))
        forgotten = self._queue(requested_at=self._ago(8))
        waiting = self._queue()

        failed = cron_dispatcher.fail_stale_requests(
            _dbconn(self.dbconn), _dbcur(self.cur),
            cron_dispatcher.DEFAULT_RUN_TIMEOUT_SECONDS
            + cron_dispatcher.REAP_MARGIN_SECONDS)

        self.assertEqual(failed, 2)
        self.cur.execute('SELECT COUNT(*) AS n FROM Notifications;')
        row = self.cur.fetchone()
        assert row is not None
        self.assertEqual(row['n'], 2)
        self.assertEqual(
            [self._request(request_id)['status'] for request_id in
             (abandoned, running, forgotten, waiting)],
            [RequestStatus.FAILED.value, RequestStatus.PICKED.value,
             RequestStatus.FAILED.value, RequestStatus.PENDING.value])

    def test_the_model_job_is_rejected_without_an_output(self) -> None:
        '''That job has no default for where the model goes.'''
        request_id = self._queue('build_problem_rec_model.py')

        summary = self._process(_fake_run)

        row = self._request(request_id)
        self.assertEqual(summary, cron_dispatcher.Summary(1, 1))
        self.assertEqual(row['status'], RequestStatus.FAILED.value)
        self.assertEqual(
            row['error_text'],
            'the dispatcher was not told where this job writes')


class IsLaunchableTest(unittest.TestCase):
    '''Tests for the check that guards what can be launched.'''

    def test_accepts_a_registered_script_that_exists(self) -> None:
        '''The happy path is a registry entry with a file behind it.'''
        self.assertTrue(
            cron_dispatcher.is_launchable('update_ranks.py',
                                          set(_REGISTERED)))

    def test_rejects_a_script_that_is_not_registered(self) -> None:
        '''A file in stuff/cron is not enough on its own.'''
        self.assertFalse(
            cron_dispatcher.is_launchable('update_ranks.py', set()))

    def test_rejects_a_known_job_this_checkout_does_not_have(self) -> None:
        '''The allowlist can name a script an older checkout lacks.'''
        with mock.patch.object(cron_dispatcher, 'KNOWN_JOBS',
                               frozenset({'not_here.py'})):
            self.assertFalse(
                cron_dispatcher.is_launchable('not_here.py', {'not_here.py'}))

    def test_rejects_a_tampered_registry_row(self) -> None:
        '''The registry is not the allowlist, so a row alone is not enough.'''
        for name in ('cron_dispatcher.py', 'conftest.py',
                     'update_ranks_test.py', '../update-dao.py'):
            self.assertFalse(
                cron_dispatcher.is_launchable(name, {name}), name)


class KnownJobsTest(unittest.TestCase):
    '''Tests that the python allowlist matches the PHP enum.'''

    def test_matches_the_php_enum(self) -> None:
        '''`CronJobName` is the source of truth; this fails when they drift.'''
        path = os.path.join(
            _OMEGAUP_ROOT, 'frontend/server/src/CronJobName.php')
        with open(path, encoding='utf-8') as f:
            source = f.read()
        cases = set(re.findall(r"case\s+\w+\s*=\s*'([^']*)';", source))

        self.assertEqual(cases, set(cron_dispatcher.KNOWN_JOBS))


class RequestStatusTest(unittest.TestCase):
    '''Tests for the status enum.'''

    def test_matches_the_column(self) -> None:
        '''The enum mirrors the `Cron_Run_Requests.status` column exactly.'''
        self.assertEqual(
            [status.value for status in RequestStatus],
            ['pending', 'picked', 'done', 'failed'])


class JobArgsTest(unittest.TestCase):
    '''Tests for the flags a job needs beyond the database ones.'''

    def test_most_jobs_need_nothing_more(self) -> None:
        '''Production runs them with the database flags alone.'''
        self.assertEqual(cron_dispatcher.job_args(_ARGS, 'update_ranks.py'),
                         [])

    def test_the_model_job_needs_somewhere_to_write(self) -> None:
        '''Its --output has no default, so it comes from the dispatcher.'''
        name = 'build_problem_rec_model.py'
        configured = argparse.Namespace(rec_model_output='/var/model.db')

        self.assertIsNone(cron_dispatcher.job_args(_ARGS, name))
        self.assertEqual(cron_dispatcher.job_args(configured, name),
                         ['--output', '/var/model.db'])


class MainTest(unittest.TestCase):
    '''Tests for the entrypoint.'''

    def _main(self, summary: cron_dispatcher.Summary, *argv: str) -> None:
        with mock.patch.object(sys, 'argv', ['cron_dispatcher.py', *argv]), \
                mock.patch('lib.db.connect'), \
                mock.patch('lib.logs.init'), \
                mock.patch.object(cron_dispatcher,
                                  'install_signal_handlers') as install, \
                mock.patch.object(cron_dispatcher, 'process_requests',
                                  return_value=summary):
            try:
                cron_dispatcher.main()
            finally:
                self.installed = install.called

    def setUp(self) -> None:
        self.installed = False

    def test_installs_the_stop_signal_handlers(self) -> None:
        '''Without them a stop leaves the credential file behind.'''
        self._main(cron_dispatcher.Summary(processed=0, failed=0))

        self.assertTrue(self.installed)

    def test_exits_non_zero_when_a_rerun_failed(self) -> None:
        '''Whatever watches the process has to see the failure.'''
        with self.assertRaises(SystemExit) as raised:
            self._main(cron_dispatcher.Summary(processed=2, failed=1))

        self.assertEqual(raised.exception.code, 1)

    def test_returns_normally_when_every_rerun_succeeded(self) -> None:
        '''An empty or clean pass is a success.'''
        self._main(cron_dispatcher.Summary(processed=2, failed=0))
        self._main(cron_dispatcher.Summary(processed=0, failed=0))

    def test_rejects_a_timeout_that_is_not_positive(self) -> None:
        '''A typo must not turn into a run that is killed at once.'''
        for value in ('0', '-5', 'soon'):
            with self.assertRaises(SystemExit) as raised:
                self._main(cron_dispatcher.Summary(0, 0),
                           '--run-timeout-seconds', value)
            self.assertEqual(raised.exception.code, 2)


if __name__ == '__main__':
    unittest.main()
