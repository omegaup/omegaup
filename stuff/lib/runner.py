#!/usr/bin/env python3
'''A reusable runner for cron scripts.

Records each execution into the `Cron_Runs` table (start, end, status,
per-phase timings and errors) and holds a MySQL advisory lock so that two
runs of the same job cannot overlap. A cron wraps the body of its `main()`
with `lib.runner.run(...)`:

    with lib.runner.run(parser.prog, args) as cron_run:
        with cron_run.phase('update_users_stats'):
            update_users_stats(...)
        cron_run.set_rows_affected(rows)
'''

import argparse
import contextlib
import json
import logging
import socket
import time

from typing import Any, Dict, Iterator, List, Optional

import lib.db


def configure_parser(parser: argparse.ArgumentParser) -> None:
    '''Adds the cron runner options to an argument parser.'''
    group = parser.add_argument_group('Cron runner')
    group.add_argument(
        '--no-track',
        action='store_true',
        help='Do not record this execution or take the overlap lock. '
        'Intended for local or ad-hoc runs.')
    group.add_argument(
        '--lock-timeout',
        type=int,
        default=0,
        help='Seconds to wait for the overlap lock before giving up. '
        '0 means do not wait.')


class CronRun:  # pylint: disable=too-many-instance-attributes
    '''Tracks a single cron execution and guards against overlap.'''

    def __init__(
            self,
            program: str,
            args: argparse.Namespace,
            connection: Optional[lib.db.Connection] = None) -> None:
        self._program = program
        self._args = args
        self._enabled = not getattr(args, 'no_track', False)
        self._external_connection = connection
        self._connection: Optional[lib.db.Connection] = None
        self._owns_connection = False
        self._run_id: Optional[int] = None
        self._phases: List[Dict[str, Any]] = []
        self._rows_affected: Optional[int] = None
        self._start_monotonic = 0.0

    @property
    def _lock_name(self) -> str:
        return f'cron:{self._program}'[:64]

    def __enter__(self) -> 'CronRun':
        if not self._enabled:
            return self
        self._connection = (
            self._external_connection
            or lib.db.connect(
                lib.db.DatabaseConnectionArguments.from_args(self._args)))
        self._owns_connection = self._external_connection is None
        if not self._acquire_lock():
            logging.info(
                'cron job %s is already running, skipping', self._program)
            self._close_connection()
            raise SystemExit(0)
        self._insert_running_row()
        return self

    def __exit__(
            self,
            exc_type: Any,
            exc_value: Any,
            traceback: Any) -> None:
        if not self._enabled:
            return
        try:
            self._finish(exc_value)
            self._release_lock()
        finally:
            self._close_connection()

    @contextlib.contextmanager
    def phase(self, name: str) -> Iterator[None]:
        '''Times a phase of the job and records its outcome.'''
        start = time.monotonic()
        status = 'success'
        error_class: Optional[str] = None
        try:
            yield
        except BaseException as exc:  # noqa: bare-except
            status = 'failure'
            error_class = type(exc).__name__
            raise
        finally:
            duration = round(time.monotonic() - start, 3)
            self._phases.append({
                'phase': name,
                'status': status,
                'duration': duration,
                'error_class': error_class,
            })
            logging.info(
                'cron phase %s %s in %.3fs', name, status, duration,
                extra={
                    'phase': name,
                    'status': status,
                    'duration': duration,
                })

    def set_rows_affected(self, rows: int) -> None:
        '''Records how many rows the job wrote, for reporting.'''
        self._rows_affected = rows

    def _acquire_lock(self) -> bool:
        assert self._connection is not None
        timeout = getattr(self._args, 'lock_timeout', 0)
        with self._connection.cursor() as cur:
            cur.execute(
                'SELECT GET_LOCK(%s, %s)', (self._lock_name, timeout))
            row = cur.fetchone()
        return bool(row is not None and row[0] == 1)

    def _release_lock(self) -> None:
        if self._connection is None:
            return
        with self._connection.cursor() as cur:
            cur.execute('SELECT RELEASE_LOCK(%s)', (self._lock_name,))
            cur.fetchall()

    def _insert_running_row(self) -> None:
        assert self._connection is not None
        self._start_monotonic = time.monotonic()
        with self._connection.cursor() as cur:
            cur.execute(
                '''
                INSERT INTO `Cron_Runs`
                    (`name`, `hostname`, `status`, `started_at`)
                VALUES (%s, %s, 'running', NOW());''',
                (self._program, socket.gethostname()))
            self._run_id = cur.lastrowid
        self._connection.conn.commit()

    def _finish(self, exc_value: Any) -> None:
        if self._connection is None or self._run_id is None:
            return
        status = 'failure' if exc_value is not None else 'success'
        error_text: Optional[str] = None
        if exc_value is not None:
            error_text = f'{type(exc_value).__name__}: {exc_value}'
        duration = round(time.monotonic() - self._start_monotonic, 3)
        phases = json.dumps(self._phases) if self._phases else None
        with self._connection.cursor() as cur:
            cur.execute(
                '''
                UPDATE `Cron_Runs`
                SET `status` = %s,
                    `finished_at` = NOW(),
                    `duration_seconds` = %s,
                    `rows_affected` = %s,
                    `phases` = %s,
                    `error_text` = %s
                WHERE `run_id` = %s;''',
                (status, duration, self._rows_affected, phases,
                 error_text, self._run_id))
        self._connection.conn.commit()

    def _close_connection(self) -> None:
        if self._owns_connection and self._connection is not None:
            self._connection.conn.close()
        self._connection = None


def run(
        program: str,
        args: argparse.Namespace,
        *,
        connection: Optional[lib.db.Connection] = None) -> CronRun:
    '''Returns a context manager that records a cron execution.

    If `--no-track` is set the runner is a no-op that still runs the job.
    '''
    return CronRun(program, args, connection=connection)
