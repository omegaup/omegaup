'''Unittests for the cron_dispatcher script.

These use in-memory fakes so neither a real database nor a subprocess is
required. The script under test is injected with a fake run command, so the
tests exercise the request lifecycle (claim, run, record, notify) in isolation.
The fake connection models InnoDB's REPEATABLE READ snapshot, so a lookup
inside an open read view keeps answering with what the view captured.
'''

import argparse
import json
import os
import unittest

from typing import Any, Dict, List, Optional, Tuple, cast

from cron import cron_dispatcher
from cron.cron_dispatcher import RequestStatus

RunResult = Tuple[int, Optional[str]]
Params = Optional[Tuple[Any, ...]]

_REGISTERED = ('update_ranks.py', 'assign_badges.py', 'aggregate_feedback.py')

_NO_READ_VIEW = object()

# Recorded in `calls` so a test can assert what happened between two queries.
_COMMIT = 'COMMIT'
_LAUNCH = 'LAUNCH'


def _normalize(query: str) -> str:
    '''Collapses a query to a single lowercase line.'''
    return ' '.join(query.lower().split())


class _FakeCursor:
    '''Answers dispatcher queries from scripted data and records calls.'''

    def __init__(self, connection: '_FakeConnection') -> None:
        self._connection = connection
        self._mode: Optional[str] = None
        self.rowcount = -1

    def execute(self, query: str, params: Any = None) -> None:
        '''Records the call and remembers what the next fetch should return.'''
        self._connection.calls.append((query, params))
        normalized = _normalize(query)
        self.rowcount = 1
        if 'from cron_run_requests' in normalized:
            self._mode = 'pending'
        elif 'from cron_jobs' in normalized:
            self._mode = 'jobs'
        elif 'max(run_id)' in normalized:
            self._mode = 'run_id'
        elif 'picked_at = now()' in normalized:
            self._mode = None
            self.rowcount = self._connection.rowcount_for('claim')
        elif 'run_id = %s, error_text = %s' in normalized:
            self._mode = None
            self.rowcount = self._connection.rowcount_for('finish')
        else:
            self._mode = None

    def fetchall(self) -> List[Dict[str, Any]]:
        '''Returns the scripted rows for the query that was just run.'''
        if self._mode == 'pending':
            return self._connection.pending
        if self._mode == 'jobs':
            return [{'name': name} for name in self._connection.registered]
        return []

    def fetchone(self) -> Optional[Dict[str, Any]]:
        '''Returns the scripted answer for the query that was just run.'''
        if self._mode == 'run_id':
            return {'run_id': self._connection.select_max_run_id()}
        return None


class _FakeConnection:
    '''Stand-in for lib.db.Connection with REPEATABLE READ semantics.

    `Cron_Runs` starts at `previous_run_id`. The child cron commits `run_id`
    from its own connection, so a read view opened before it started keeps
    reporting the old value until this connection commits.
    '''

    registered = _REGISTERED

    def __init__(self, pending: List[Dict[str, Any]],
                 run_id: Optional[int] = 7,
                 previous_run_id: Optional[int] = None,
                 losing_updates: Tuple[str, ...] = ()) -> None:
        self.pending = pending
        self.run_id = run_id
        self.losing_updates = losing_updates
        self.committed_run_id = previous_run_id
        self.calls: List[Tuple[str, Any]] = []
        self._read_view: Any = _NO_READ_VIEW
        self.conn = self

    def rowcount_for(self, update: str) -> int:
        '''Whether a conditional UPDATE matched, as MySQL would report it.'''
        return 0 if update in self.losing_updates else 1

    def child_committed_a_run(self) -> None:
        '''The child cron inserted its `Cron_Runs` row on its own session.'''
        self.committed_run_id = self.run_id

    def select_max_run_id(self) -> Optional[int]:
        '''Answers a MAX(run_id) lookup from the open read view.'''
        if self._read_view is _NO_READ_VIEW:
            self._read_view = self.committed_run_id
        return cast(Optional[int], self._read_view)

    def commit(self) -> None:
        '''Ends the transaction, and with it the read view.'''
        self.calls.append((_COMMIT, None))
        self._read_view = _NO_READ_VIEW


def _matching(calls: List[Tuple[str, Any]], needle: str) -> List[Params]:
    '''Returns the params of every query whose text contains `needle`.'''
    return [
        cast(Params, params) for query, params in calls
        if needle in _normalize(query)
    ]


def _final_update(calls: List[Tuple[str, Any]]) -> Params:
    '''Returns the params of the done/failed UPDATE, if any.'''
    matches = _matching(calls, 'run_id = %s, error_text = %s')
    return matches[0] if matches else None


def _claim_update(calls: List[Tuple[str, Any]]) -> Params:
    '''Returns the params of the conditional claim UPDATE, if any.'''
    matches = _matching(calls, 'picked_at = now()')
    return matches[0] if matches else None


def _notifications(calls: List[Tuple[str, Any]]) -> List[Tuple[Any, ...]]:
    '''Returns the params of every Notifications insert.'''
    return [
        params for query, params in calls
        if 'insert into notifications' in _normalize(query)
    ]


def _fake_run(args: argparse.Namespace, name: str) -> RunResult:
    '''A run command that always succeeds.'''
    del args, name
    return (0, None)


class CronDispatcherTest(unittest.TestCase):
    '''Tests for cron_dispatcher.process_requests.'''

    def _run(
        self,
        pending: List[Dict[str, Any]],
        run_command: cron_dispatcher.RunCommand,
        **kwargs: Any,
    ) -> Tuple[int, _FakeConnection]:
        produces_run = kwargs.pop('produces_run', True)
        connection = _FakeConnection(pending, **kwargs)
        cursor = _FakeCursor(connection)

        def instrumented(args: argparse.Namespace, name: str) -> RunResult:
            connection.calls.append((_LAUNCH, name))
            try:
                return run_command(args, name)
            finally:
                if produces_run:
                    connection.child_committed_a_run()

        processed = cron_dispatcher.process_requests(
            connection, cursor, argparse.Namespace(),  # type: ignore
            run_command=instrumented)
        return processed, connection

    def test_records_successful_rerun(self) -> None:
        '''A successful run is marked done, linked and notified.'''
        launched: List[str] = []

        def fake_run(args: argparse.Namespace, name: str) -> RunResult:
            del args
            launched.append(name)
            return (0, None)

        processed, connection = self._run(
            [{'request_id': 1, 'name': 'update_ranks.py', 'requested_by': 5}],
            fake_run)

        self.assertEqual(processed, 1)
        self.assertEqual(launched, ['update_ranks.py'])
        params = _final_update(connection.calls)
        assert params is not None
        self.assertEqual(params[0], RequestStatus.DONE.value)
        self.assertEqual(params[1], 7)
        notifications = _notifications(connection.calls)
        self.assertEqual(len(notifications), 1)
        user_id, contents = notifications[0]
        self.assertEqual(user_id, 5)
        self.assertEqual(
            json.loads(contents)['status'], RequestStatus.DONE.value)

    def test_ends_the_read_view_before_the_child_runs(self) -> None:
        '''Without a commit the second lookup re-reads the same snapshot.

        The child commits its `Cron_Runs` row on its own connection, so under
        REPEATABLE READ the dispatcher only sees it if it ended the read view
        the first lookup opened.
        '''
        _, connection = self._run(
            [{'request_id': 1, 'name': 'update_ranks.py', 'requested_by': 5}],
            _fake_run)

        texts = [query for query, _ in connection.calls]
        first_lookup = next(i for i, text in enumerate(texts)
                            if 'MAX(run_id)' in text)
        launched = texts.index(_LAUNCH)
        self.assertIn(_COMMIT, texts[first_lookup:launched])

    def test_records_failed_rerun_with_error(self) -> None:
        '''A non-zero exit marks the request failed and stores the error.'''
        def fake_run(args: argparse.Namespace, name: str) -> RunResult:
            del args, name
            return (1, 'boom')

        processed, connection = self._run(
            [{'request_id': 2, 'name': 'assign_badges.py', 'requested_by': 9}],
            fake_run)

        self.assertEqual(processed, 1)
        params = _final_update(connection.calls)
        assert params is not None
        self.assertEqual(params[0], RequestStatus.FAILED.value)
        self.assertEqual(params[2], 'boom')
        notifications = _notifications(connection.calls)
        self.assertEqual(
            json.loads(notifications[0][1])['status'],
            RequestStatus.FAILED.value)

    def test_records_failed_rerun_when_the_run_raises(self) -> None:
        '''An exception leaves the request failed instead of stuck picked.'''
        def fake_run(args: argparse.Namespace, name: str) -> RunResult:
            del args, name
            raise OSError('no such file')

        processed, connection = self._run(
            [{'request_id': 8, 'name': 'update_ranks.py', 'requested_by': 5}],
            fake_run, produces_run=False)

        self.assertEqual(processed, 1)
        params = _final_update(connection.calls)
        assert params is not None
        self.assertEqual(params[0], RequestStatus.FAILED.value)
        self.assertEqual(params[2], 'no such file')

    def test_skips_unregistered_job(self) -> None:
        '''A request for an unknown script is never launched.'''
        launched: List[str] = []

        def fake_run(args: argparse.Namespace, name: str) -> RunResult:
            del args
            launched.append(name)
            return (0, None)

        processed, connection = self._run(
            [{'request_id': 3, 'name': 'rm_rf.py', 'requested_by': 1}],
            fake_run)

        self.assertEqual(processed, 1)
        self.assertEqual(launched, [])
        self.assertEqual(
            _matching(connection.calls, 'error_text = %s where request_id'),
            [(RequestStatus.FAILED.value, 'job is not registered', 3,
              RequestStatus.PENDING.value)])

    def test_tells_the_requester_the_job_was_rejected(self) -> None:
        '''A rejection is the only feedback the admin gets, so it is sent.'''
        _, connection = self._run(
            [{'request_id': 3, 'name': 'rm_rf.py', 'requested_by': 1}],
            _fake_run)

        notifications = _notifications(connection.calls)
        self.assertEqual(len(notifications), 1)
        user_id, contents = notifications[0]
        self.assertEqual(user_id, 1)
        self.assertEqual(
            json.loads(contents)['status'], RequestStatus.FAILED.value)

    def test_claims_only_a_request_that_is_still_pending(self) -> None:
        '''The claim names the status it expects, so two cannot both win.'''
        _, connection = self._run(
            [{'request_id': 9, 'name': 'update_ranks.py', 'requested_by': 5}],
            _fake_run)

        self.assertEqual(
            _claim_update(connection.calls),
            (RequestStatus.PICKED.value, 9, RequestStatus.PENDING.value))

    def test_finishes_only_a_request_that_is_still_picked(self) -> None:
        '''The terminal UPDATE names the status it expects, like the claim.'''
        _, connection = self._run(
            [{'request_id': 9, 'name': 'update_ranks.py', 'requested_by': 5}],
            _fake_run)

        params = _final_update(connection.calls)
        assert params is not None
        self.assertEqual(params[3], 9)
        self.assertEqual(params[4], RequestStatus.PICKED.value)

    def test_does_not_notify_over_the_stale_reaper(self) -> None:
        '''A request the reaper already failed keeps the reaper's verdict.'''
        _, connection = self._run(
            [{'request_id': 9, 'name': 'update_ranks.py', 'requested_by': 5}],
            _fake_run, losing_updates=('finish',))

        self.assertEqual(_notifications(connection.calls), [])

    def test_skips_request_claimed_by_another_dispatcher(self) -> None:
        '''A request another dispatcher already took is left alone.'''
        launched: List[str] = []

        def fake_run(args: argparse.Namespace, name: str) -> RunResult:
            del args
            launched.append(name)
            return (0, None)

        processed, connection = self._run(
            [{'request_id': 9, 'name': 'update_ranks.py', 'requested_by': 5}],
            fake_run, losing_updates=('claim',))

        self.assertEqual(processed, 0)
        self.assertEqual(launched, [])
        self.assertIsNone(_final_update(connection.calls))
        self.assertEqual(_notifications(connection.calls), [])

    def test_fails_requests_left_behind_by_a_dead_dispatcher(self) -> None:
        '''Requests stuck in picked are released before anything else runs.'''
        _, connection = self._run([], _fake_run)

        self.assertEqual(
            _matching(connection.calls, 'date_sub'),
            [(RequestStatus.FAILED.value,
              'the dispatcher did not finish this run',
              RequestStatus.PICKED.value,
              cron_dispatcher.STALE_PICK_HOURS)])

    def test_a_job_that_never_ran_is_not_recorded_done(self) -> None:
        '''lib.runner exits 0 when it skips, which is not a successful run.'''
        _, connection = self._run(
            [{'request_id': 5, 'name': 'update_ranks.py', 'requested_by': 5}],
            _fake_run, produces_run=False, run_id=7, previous_run_id=7)

        params = _final_update(connection.calls)
        assert params is not None
        self.assertEqual(params[0], RequestStatus.FAILED.value)
        self.assertIsNone(params[1])
        self.assertEqual(
            params[2],
            'the job did not run: it is disabled or already running')
        self.assertEqual(
            json.loads(_notifications(connection.calls)[0][1])['status'],
            RequestStatus.FAILED.value)

    def test_only_pending_requests_are_picked_up(self) -> None:
        '''The queue query asks for the pending status by name.'''
        _, connection = self._run([], _fake_run)

        self.assertEqual(
            _matching(connection.calls, 'from cron_run_requests'),
            [(RequestStatus.PENDING.value,)])

    def test_notification_carries_a_renderable_body(self) -> None:
        '''The notification uses the generic body the UI knows how to show.'''
        _, connection = self._run(
            [{'request_id': 6, 'name': 'update_ranks.py', 'requested_by': 5}],
            _fake_run)

        contents = json.loads(_notifications(connection.calls)[0][1])
        self.assertEqual(
            contents['body']['localizationString'],
            'notificationCronRerunSucceeded')
        self.assertEqual(
            contents['body']['localizationParams'],
            {'jobName': 'update_ranks.py'})

    def test_no_notification_without_requester(self) -> None:
        '''No notification is created when the requester is unknown.'''
        _, connection = self._run(
            [{
                'request_id': 4,
                'name': 'aggregate_feedback.py',
                'requested_by': None,
            }],
            _fake_run)

        self.assertEqual(_notifications(connection.calls), [])


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

    def test_rejects_a_registered_name_with_no_file(self) -> None:
        '''A registry row for a job this checkout does not have is skipped.'''
        self.assertFalse(
            cron_dispatcher.is_launchable('not_here.py', {'not_here.py'}))

    def test_rejects_a_path_that_escapes_the_cron_directory(self) -> None:
        '''A tampered row cannot reach anything outside stuff/cron.'''
        # stuff/update-dao.py really exists, so only the basename check
        # keeps this from being launched.
        escaping = '../update-dao.py'
        self.assertFalse(
            cron_dispatcher.is_launchable(escaping, {escaping}))

    def test_rejects_a_name_that_is_not_a_script(self) -> None:
        '''Only python scripts are ever launched.'''
        self.assertFalse(
            cron_dispatcher.is_launchable('testdata.db', {'testdata.db'}))


class RequestStatusTest(unittest.TestCase):
    '''Tests for the status enum.'''

    def test_matches_the_column(self) -> None:
        '''The enum mirrors the `Cron_Run_Requests.status` column exactly.'''
        self.assertEqual(
            [status.value for status in RequestStatus],
            ['pending', 'picked', 'done', 'failed'])


class DbCommandArgsTest(unittest.TestCase):
    '''Tests for the flags handed down to the child script.'''

    @staticmethod
    def _args(password: Optional[str]) -> argparse.Namespace:
        return argparse.Namespace(host='localhost', port=13306,
                                  database='omegaup', user='omegaup',
                                  password=password, mysql_config_file=None)

    def test_forwards_the_config_file_when_there_is_no_password(self) -> None:
        '''Without a password there is nothing to hide.'''
        args = self._args(None)
        args.mysql_config_file = '/home/omegaup/.my.cnf'
        with cron_dispatcher.db_command_args(args) as command:
            self.assertIn('--mysql-config-file', command)
            self.assertIn('/home/omegaup/.my.cnf', command)
            self.assertIn('--user', command)

    def test_keeps_the_password_out_of_the_command_line(self) -> None:
        '''The password reaches the child through a file, not through argv.'''
        with cron_dispatcher.db_command_args(self._args('hunter2')) as command:
            self.assertNotIn('--password', command)
            self.assertNotIn('hunter2', command)
            config_file = command[command.index('--mysql-config-file') + 1]
            with open(config_file, encoding='utf-8') as f:
                contents = f.read()
            self.assertIn('password=hunter2', contents)
            self.assertEqual(os.stat(config_file).st_mode & 0o077, 0)

    def test_the_child_reads_the_config_file_instead_of_the_flags(
            self) -> None:
        '''lib.db only reads the config file when --user is absent.'''
        with cron_dispatcher.db_command_args(self._args('hunter2')) as command:
            self.assertNotIn('--user', command)


if __name__ == '__main__':
    unittest.main()
