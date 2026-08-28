'''Unit tests for `lib/runner.py`.'''
import argparse
import unittest
from typing import Any, Optional, Tuple, cast

from cron.tests.fixtures.mock_cursor import MockConnection, MockCursor
import lib.db
import lib.runner


def _as_conn(conn: MockConnection) -> lib.db.Connection:
    '''Type a MockConnection as the lib.db connection the runner expects.'''
    return cast(lib.db.Connection, conn)


def _args() -> argparse.Namespace:
    return argparse.Namespace(no_track=False, lock_timeout=0)


def _scripted_cursor() -> MockCursor:
    cur = MockCursor(script=[
        ('select `enabled`', []),
        ('get_lock', [(1,)]),
        ('insert into `cron_runs`', []),
    ])
    cur.lastrowid = 42
    return cur


def _finish_call(cur: MockCursor) -> Tuple[str, Optional[str]]:
    '''Returns the (status, error_text) bound to the final UPDATE.'''
    for sql, params in reversed(cur.calls):
        if 'update `cron_runs`' in ' '.join(sql.lower().split()):
            assert params is not None
            return params[0], params[4]
    raise AssertionError('no Cron_Runs UPDATE was executed')


class RunOutcomeTest(unittest.TestCase):
    '''Tests for the status recorded in `Cron_Runs` by the runner.'''

    def test_clean_exit_records_success(self) -> None:
        '''A body that finishes without raising is recorded as success.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)

        with lib.runner.run('test_job', _args(), connection=_as_conn(conn)):
            pass

        status, error_text = _finish_call(cur)
        self.assertEqual(status, 'success')
        self.assertIsNone(error_text)

    def test_system_exit_zero_records_success(self) -> None:
        '''sys.exit(0) inside the body is recorded as success.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)

        with self.assertRaises(SystemExit):
            with lib.runner.run(
                    'test_job', _args(), connection=_as_conn(conn)):
                raise SystemExit(0)

        status, error_text = _finish_call(cur)
        self.assertEqual(status, 'success')
        self.assertIsNone(error_text)

    def test_system_exit_none_records_success(self) -> None:
        '''A bare sys.exit() inside the body is recorded as success.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)

        with self.assertRaises(SystemExit):
            with lib.runner.run(
                    'test_job', _args(), connection=_as_conn(conn)):
                raise SystemExit()

        status, error_text = _finish_call(cur)
        self.assertEqual(status, 'success')
        self.assertIsNone(error_text)

    def test_system_exit_nonzero_records_failure(self) -> None:
        '''sys.exit(1) inside the body is recorded as failure.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)

        with self.assertRaises(SystemExit):
            with lib.runner.run(
                    'test_job', _args(), connection=_as_conn(conn)):
                raise SystemExit(1)

        status, error_text = _finish_call(cur)
        self.assertEqual(status, 'failure')
        self.assertEqual(error_text, 'SystemExit: 1')

    def test_system_exit_with_message_records_failure(self) -> None:
        '''sys.exit with a message inside the body is recorded as failure.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)

        with self.assertRaises(SystemExit):
            with lib.runner.run(
                    'test_job', _args(), connection=_as_conn(conn)):
                raise SystemExit('boom')

        status, error_text = _finish_call(cur)
        self.assertEqual(status, 'failure')
        self.assertEqual(error_text, 'SystemExit: boom')

    def test_exception_records_failure(self) -> None:
        '''Any other exception raised in the body is recorded as failure.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)

        with self.assertRaises(RuntimeError):
            with lib.runner.run(
                    'test_job', _args(), connection=_as_conn(conn)):
                raise RuntimeError('boom')

        status, error_text = _finish_call(cur)
        self.assertEqual(status, 'failure')
        self.assertEqual(error_text, 'RuntimeError: boom')

    def test_mark_failure_records_failure_without_exception(self) -> None:
        '''mark_failure forces a failure status even on a clean exit.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)

        with lib.runner.run('test_job', _args(), connection=_as_conn(conn)) \
                as cron_run:
            cron_run.mark_failure()

        status, error_text = _finish_call(cur)
        self.assertEqual(status, 'failure')
        self.assertIsNone(error_text)

    def test_system_exit_zero_still_propagates(self) -> None:
        '''The SystemExit is not swallowed; the process still exits.'''
        cur = _scripted_cursor()
        conn = MockConnection(cur)
        exit_code: Any = None

        try:
            with lib.runner.run(
                    'test_job', _args(), connection=_as_conn(conn)):
                raise SystemExit(0)
        except SystemExit as exc:
            exit_code = exc.code

        self.assertEqual(exit_code, 0)


if __name__ == '__main__':
    unittest.main()
