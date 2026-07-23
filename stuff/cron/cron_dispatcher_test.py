'''Unittests for the cron_dispatcher script.

These use in-memory fakes so neither a real database nor a subprocess is
required. The script under test is injected with a fake run command, so the
tests exercise the request lifecycle (claim, run, record, notify) in isolation.
'''

import argparse
import json
import unittest

from typing import Any, Dict, List, Optional, Tuple, cast

import cron_dispatcher

RunResult = Tuple[int, Optional[str]]
Params = Optional[Tuple[Any, ...]]


class _FakeCursor:
    '''Answers dispatcher queries from scripted data and records calls.'''

    def __init__(self, connection: '_FakeConnection') -> None:
        self._connection = connection
        self._mode: Optional[str] = None

    def execute(self, query: str, params: Any = None) -> None:
        '''Records the call and remembers what the next fetch should return.'''
        self._connection.calls.append((query, params))
        normalized = ' '.join(query.lower().split())
        if 'from cron_run_requests' in normalized:
            self._mode = 'pending'
        elif 'max(run_id)' in normalized:
            self._mode = 'run_id'
        else:
            self._mode = None

    def fetchall(self) -> List[Dict[str, Any]]:
        '''Returns the scripted pending rows for the pending query.'''
        if self._mode == 'pending':
            return self._connection.pending
        return []

    def fetchone(self) -> Optional[Dict[str, Any]]:
        '''Returns the scripted run_id for the linkage query.'''
        if self._mode == 'run_id':
            return {'run_id': self._connection.run_id}
        return None


class _FakeConnection:
    '''Minimal stand-in for lib.db.Connection.'''

    def __init__(self, pending: List[Dict[str, Any]],
                 run_id: Optional[int] = 7) -> None:
        self.pending = pending
        self.run_id = run_id
        self.calls: List[Tuple[str, Any]] = []
        self.commits = 0
        self.conn = self

    def commit(self) -> None:
        '''Counts commits.'''
        self.commits += 1


def _final_update(calls: List[Tuple[str, Any]]) -> Params:
    '''Returns the params of the done/failed UPDATE, if any.'''
    for query, params in calls:
        normalized = ' '.join(query.lower().split())
        if normalized.startswith('update cron_run_requests set status = %s'):
            return cast(Params, params)
    return None


def _notifications(calls: List[Tuple[str, Any]]) -> List[Tuple[Any, ...]]:
    '''Returns the params of every Notifications insert.'''
    return [
        params for query, params in calls
        if 'insert into notifications' in ' '.join(query.lower().split())
    ]


class CronDispatcherTest(unittest.TestCase):
    '''Tests for cron_dispatcher.process_requests.'''

    def _run(
        self,
        pending: List[Dict[str, Any]],
        run_command: cron_dispatcher.RunCommand,
        run_id: Optional[int] = 7,
    ) -> Tuple[int, _FakeConnection]:
        connection = _FakeConnection(pending, run_id=run_id)
        cursor = _FakeCursor(connection)
        processed = cron_dispatcher.process_requests(
            connection, cursor, argparse.Namespace(),  # type: ignore
            run_command=run_command)
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
        self.assertEqual(params[0], 'done')
        self.assertEqual(params[1], 7)
        notifications = _notifications(connection.calls)
        self.assertEqual(len(notifications), 1)
        user_id, contents = notifications[0]
        self.assertEqual(user_id, 5)
        self.assertEqual(json.loads(contents)['status'], 'done')

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
        self.assertEqual(params[0], 'failed')
        self.assertEqual(params[2], 'boom')
        notifications = _notifications(connection.calls)
        self.assertEqual(json.loads(notifications[0][1])['status'], 'failed')

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
        normalized = [
            ' '.join(query.lower().split()) for query, _ in connection.calls
        ]
        self.assertTrue(
            any('failed' in call and 'error_text' in call
                for call in normalized))
        self.assertEqual(_notifications(connection.calls), [])

    def test_no_notification_without_requester(self) -> None:
        '''No notification is created when the requester is unknown.'''
        def fake_run(args: argparse.Namespace, name: str) -> RunResult:
            del args, name
            return (0, None)

        _, connection = self._run(
            [{
                'request_id': 4,
                'name': 'aggregate_feedback.py',
                'requested_by': None,
            }],
            fake_run)

        self.assertEqual(_notifications(connection.calls), [])


if __name__ == '__main__':
    unittest.main()
