#!/usr/bin/env python3
'''Unit tests for the cron runner library.

These use an in-memory fake connection so no real database is required.
'''

import argparse
import json
import os
import sys

from typing import Any, List, Optional, Tuple, cast

import pytest

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db  # pylint: disable=wrong-import-position
import lib.runner  # pylint: disable=wrong-import-position


class _FakeCursor:
    '''Records executed statements and answers GET_LOCK deterministically.'''

    def __init__(self, connection: '_FakeConnection') -> None:
        self._connection = connection
        self.lastrowid = connection.next_run_id

    def execute(self, query: str, params: Any = None) -> None:
        '''Records the statement and scripts the next GET_LOCK answer.'''
        self._connection.calls.append((query, params))
        normalized = ' '.join(query.lower().split())
        if 'get_lock' in normalized:
            self._connection.last_fetchone = (
                (1,) if self._connection.lock_acquired else (0,))
        else:
            self._connection.last_fetchone = None

    def fetchone(self) -> Optional[Tuple[int]]:
        '''Returns the scripted row for the last statement.'''
        return self._connection.last_fetchone

    def fetchall(self) -> List[Any]:
        '''Returns an empty result set.'''
        return []

    def __enter__(self) -> '_FakeCursor':
        return self

    def __exit__(self, exc_type: Any, exc: Any, traceback: Any) -> None:
        del exc_type, exc, traceback


class _FakeConnection:
    '''Minimal stand-in for lib.db.Connection.'''

    def __init__(self, lock_acquired: bool = True) -> None:
        self.lock_acquired = lock_acquired
        self.calls: List[Tuple[str, Any]] = []
        self.commits = 0
        self.closed = False
        self.next_run_id = 42
        self.last_fetchone: Optional[Tuple[int]] = None
        self.conn = self

    def cursor(
            self,
            buffered: bool = False,
            dictionary: bool = False) -> _FakeCursor:
        '''Returns a fake cursor bound to this connection.'''
        del buffered, dictionary
        return _FakeCursor(self)

    def commit(self) -> None:
        '''Counts commits.'''
        self.commits += 1

    def close(self) -> None:
        '''Marks the connection closed.'''
        self.closed = True


def _args(no_track: bool = False, lock_timeout: int = 0) -> argparse.Namespace:
    return argparse.Namespace(no_track=no_track, lock_timeout=lock_timeout)


def _run(
    program: str,
    args: argparse.Namespace,
    conn: _FakeConnection,
) -> lib.runner.CronRun:
    '''Starts the runner against a fake connection.'''
    return lib.runner.run(
        program, args, connection=cast(lib.db.Connection, conn))


def _matching(calls: List[Tuple[str, Any]], needle: str) -> List[Any]:
    return [
        params for query, params in calls
        if needle in ' '.join(query.lower().split())
    ]


def test_records_successful_run() -> None:
    '''A completed run is recorded as success with its phases and rows.'''
    conn = _FakeConnection(lock_acquired=True)
    with _run('update_ranks.py', _args(), conn) as run:
        with run.phase('update_users_stats'):
            pass
        run.set_rows_affected(5)

    assert _matching(conn.calls, 'get_lock')
    assert _matching(conn.calls, 'insert into `cron_runs`')
    assert _matching(conn.calls, 'release_lock')
    updates = _matching(conn.calls, 'update `cron_runs`')
    assert len(updates) == 1
    status, _duration, rows_affected, phases, error_text, _run_id = updates[0]
    assert status == 'success'
    assert rows_affected == 5
    assert error_text is None
    decoded = json.loads(phases)
    assert decoded[0]['phase'] == 'update_users_stats'
    assert decoded[0]['status'] == 'success'


def test_records_failure_on_exception() -> None:
    '''An exception in the body is recorded as a failure and re-raised.'''
    conn = _FakeConnection(lock_acquired=True)
    with pytest.raises(ValueError):
        with _run('update_ranks.py', _args(), conn) as run:
            with run.phase('update_users_stats'):
                raise ValueError('boom')

    updates = _matching(conn.calls, 'update `cron_runs`')
    assert len(updates) == 1
    status, _duration, _rows, phases, error_text, _run_id = updates[0]
    assert status == 'failure'
    assert error_text == 'ValueError: boom'
    assert json.loads(phases)[0]['status'] == 'failure'


def test_skips_when_lock_is_held() -> None:
    '''A run whose lock is held exits cleanly without recording.'''
    conn = _FakeConnection(lock_acquired=False)
    body_ran = False
    with pytest.raises(SystemExit) as excinfo:
        with _run('update_ranks.py', _args(), conn):
            body_ran = True

    assert excinfo.value.code == 0
    assert body_ran is False
    assert _matching(conn.calls, 'get_lock')
    assert not _matching(conn.calls, 'insert into `cron_runs`')


def test_no_track_bypasses_all_database_access() -> None:
    '''With --no-track the runner touches neither the lock nor the table.'''
    conn = _FakeConnection(lock_acquired=True)
    body_ran = False
    with _run('update_ranks.py', _args(no_track=True), conn) as run:
        body_ran = True
        with run.phase('update_users_stats'):
            pass

    assert body_ran is True
    assert not conn.calls
    assert conn.commits == 0


def test_records_every_phase_in_order() -> None:
    '''Phases are recorded in the order they ran.'''
    conn = _FakeConnection(lock_acquired=True)
    with _run('update_ranks.py', _args(), conn) as run:
        with run.phase('first'):
            pass
        with run.phase('second'):
            pass

    phases = json.loads(_matching(conn.calls, 'update `cron_runs`')[0][3])
    assert [entry['phase'] for entry in phases] == ['first', 'second']
