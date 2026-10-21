'''Unittests for how the cron_dispatcher runs and stops its children.

`RunScriptTest` and `SignalHandlerTest` start real processes. The request
lifecycle itself runs against SQLite in cron_dispatcher_test.py; the two tests
here that need InnoDB's REPEATABLE READ snapshot use a fake connection that
models it, since SQLite has no such read view.
'''

import argparse
import io
import os
import shutil
import signal
import subprocess
import sys
import tempfile
import textwrap
import time
import unittest

from typing import Any, Callable, Dict, List, Optional, Tuple, cast
from unittest import mock

import mysql.connector.cursor

import lib.db
from cron import cron_dispatcher

RunResult = Tuple[int, Optional[str]]

_STUFF_DIR = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))

_COMMIT = 'COMMIT'
_LAUNCH = 'LAUNCH'
_PING = 'PING'


def _args(**overrides: Any) -> argparse.Namespace:
    '''The arguments main() would parse, with nothing set.'''
    values: Dict[str, Any] = {
        'host': 'localhost', 'port': 13306, 'database': 'omegaup',
        'user': None, 'password': None, 'mysql_config_file': None,
        'readonly_host': None, 'readonly_port': 3306,
        'readonly_user': None, 'readonly_password': None,
        'readonly_database': 'omegaup',
        'log_json': False, 'verbose': False, 'quiet': False,
        'logfile': None,
        'run_timeout_seconds': cron_dispatcher.DEFAULT_RUN_TIMEOUT_SECONDS,
        'rec_model_output': None,
    }
    values.update(overrides)
    return argparse.Namespace(**values)


class _FakeCursor:
    '''Answers the run lookup from the connection's read view.'''

    def __init__(self, connection: '_FakeConnection') -> None:
        self._connection = connection
        self._query = ''
        self.rowcount = 1

    def execute(self, query: str, params: Any = None) -> None:
        '''Records the call.'''
        self._connection.calls.append(query)
        self._query = ' '.join(query.lower().split())
        del params

    def fetchall(self) -> List[Dict[str, Any]]:
        '''One pending request for a registered, enabled job.'''
        if 'from cron_jobs' in self._query:
            return [{'name': 'update_ranks.py', 'enabled': 1}]
        if 'from cron_run_requests' in self._query and 'picked_at <' not in (
                self._query):
            return [{'request_id': 1, 'name': 'update_ranks.py',
                     'requested_by': 5}]
        return []

    def fetchone(self) -> Optional[Dict[str, Any]]:
        '''The run lookups, answered from the open read view.'''
        if 'max(run_id)' in self._query:
            return {'run_id': self._connection.read_run_id()}
        if 'hostname = %s' in self._query:
            run_id = self._connection.read_run_id()
            return None if run_id == 0 else {'run_id': run_id}
        return None


class _FakeConnection:
    '''Stand-in for lib.db.Connection with REPEATABLE READ semantics.

    The child commits its `Cron_Runs` row on its own session, so a read view
    opened before it started keeps answering with the old value until this
    connection commits.
    '''

    def __init__(self) -> None:
        self.calls: List[str] = []
        self.committed_run_id = 0
        self._read_view: Optional[int] = None
        self.conn = self

    def read_run_id(self) -> int:
        '''Answers from the read view, opening one if there is none.'''
        if self._read_view is None:
            self._read_view = self.committed_run_id
        return self._read_view

    def ping(self, **kwargs: Any) -> None:
        '''Records that the dispatcher checked the connection was alive.'''
        del kwargs
        self.calls.append(_PING)

    def commit(self) -> None:
        '''Ends the transaction, and with it the read view.'''
        self.calls.append(_COMMIT)
        self._read_view = None


class ReadViewTest(unittest.TestCase):
    '''Tests for what the dispatcher does around the child's run.'''

    def _process(self) -> Tuple[_FakeConnection, Optional[int]]:
        connection = _FakeConnection()
        finished: List[Optional[int]] = []

        def child(args: argparse.Namespace, name: str) -> RunResult:
            del args, name
            connection.calls.append(_LAUNCH)
            connection.committed_run_id = 7
            return (0, None)

        def finish(*args: Any) -> None:
            finished.append(args[3].run_id)

        with mock.patch.object(cron_dispatcher, '_finish_request', finish):
            cron_dispatcher.process_requests(
                cast(lib.db.Connection, connection),
                cast(mysql.connector.cursor.MySQLCursorDict,
                     _FakeCursor(connection)),
                _args(), run_command=child)
        return connection, finished[0]

    def test_ends_the_read_view_before_the_child_runs(self) -> None:
        '''Without the commit the child's run would never be seen.'''
        connection, run_id = self._process()

        launched = connection.calls.index(_LAUNCH)
        self.assertIn(_COMMIT, connection.calls[:launched])
        self.assertEqual(run_id, 7)

    def test_checks_the_connection_survived_the_run(self) -> None:
        '''The connection idles for as long as the child takes.'''
        connection, _ = self._process()

        self.assertGreater(connection.calls.index(_PING),
                           connection.calls.index(_LAUNCH))


class DbCommandArgsTest(unittest.TestCase):
    '''Tests for the database flags handed down to the child script.'''

    def setUp(self) -> None:
        self._tmpdir = tempfile.mkdtemp(prefix='cron_dispatcher_test_')
        self.addCleanup(shutil.rmtree, self._tmpdir, True)

    def _config_file(self, args: argparse.Namespace) -> str:
        with cron_dispatcher.db_command_args(args) as command:
            self.assertNotIn('--user', command)
            path = command[command.index('--mysql-config-file') + 1]
            with open(path, encoding='utf-8') as f:
                return f.read()

    def test_forwards_the_config_file_when_there_is_no_password(self) -> None:
        '''Without a password there is nothing to hide.'''
        args = _args(user='omegaup', mysql_config_file='/home/u/.my.cnf')
        with cron_dispatcher.db_command_args(args) as command:
            self.assertEqual(
                command[command.index('--mysql-config-file') + 1],
                '/home/u/.my.cnf')
            self.assertEqual(command[command.index('--user') + 1], 'omegaup')

    def test_keeps_the_password_out_of_the_command_line(self) -> None:
        '''The password reaches the child through a file only we can read.'''
        args = _args(user='omegaup', password='hunter2')
        with cron_dispatcher.db_command_args(args) as command:
            self.assertNotIn('hunter2', command)
            path = command[command.index('--mysql-config-file') + 1]
            self.assertEqual(os.stat(path).st_mode & 0o077, 0)
        self.assertIn('password=hunter2\n', self._config_file(args))

    def test_hands_down_the_replica_flags(self) -> None:
        '''Otherwise the child's heavy reads land on the primary.'''
        args = _args(user='omegaup', password='hunter2',
                     readonly_host='replica', readonly_user='reader',
                     readonly_password='s3cret')

        contents = self._config_file(args)

        self.assertIn('[clientreadonly]\nhost=replica\nport=3306\n'
                      'user=reader\npassword=s3cret\ndatabase=omegaup\n',
                      contents)

    def test_hands_down_the_replica_from_the_config_file(self) -> None:
        '''A replica set up in my.cnf is kept along with the password.'''
        my_cnf = os.path.join(self._tmpdir, 'my.cnf')
        with open(my_cnf, 'w', encoding='utf-8') as f:
            f.write('[client]\nuser=omegaup\n'
                    '[clientreadonly]\nhost=replica\nuser=reader\n')
        args = _args(user='omegaup', password='hunter2',
                     mysql_config_file=my_cnf)

        contents = self._config_file(args)

        self.assertIn('[clientreadonly]\nhost=replica\nuser=reader\n',
                      contents)


class RunScriptTest(unittest.TestCase):
    '''Tests that launch a real child process.'''

    def setUp(self) -> None:
        self._tmpdir = tempfile.mkdtemp(prefix='cron_dispatcher_test_')
        self.addCleanup(shutil.rmtree, self._tmpdir, True)
        patcher = mock.patch.object(cron_dispatcher, '_CRON_DIR', self._tmpdir)
        patcher.start()
        self.addCleanup(patcher.stop)
        stderr = mock.patch('sys.stderr', new_callable=io.StringIO)
        self.stderr = stderr.start()
        self.addCleanup(stderr.stop)

    def _script(self, body: str) -> str:
        name = 'child.py'
        with open(os.path.join(self._tmpdir, name), 'w',
                  encoding='utf-8') as f:
            f.write(textwrap.dedent(body))
        return name

    def _pid_file(self) -> str:
        return os.path.join(self._tmpdir, 'pid')

    def _child_is_gone(self) -> bool:
        with open(self._pid_file(), encoding='utf-8') as f:
            pid = int(f.read())
        try:
            os.kill(pid, 0)
        except ProcessLookupError:
            return True
        return False

    def test_interrupts_a_child_that_never_finishes(self) -> None:
        '''SIGINT lets lib.runner record the failure and release its lock.'''
        name = self._script('''
            import sys, time
            try:
                time.sleep(120)
            except KeyboardInterrupt:
                sys.stderr.write('recorded the failure')
                sys.exit(1)
        ''')

        returncode, error_text = cron_dispatcher.run_script(
            _args(run_timeout_seconds=1), name)

        self.assertEqual(returncode, 1)
        self.assertEqual(error_text,
                         'timed out after 1s\nrecorded the failure')

    def test_kills_a_child_that_ignores_the_interrupt(self) -> None:
        '''A child stuck past the grace period does not hold the request.'''
        name = self._script(f'''
            import os, signal, time
            signal.signal(signal.SIGINT, signal.SIG_IGN)
            with open({self._pid_file()!r}, 'w') as f:
                f.write(str(os.getpid()))
            time.sleep(120)
        ''')

        start = time.monotonic()
        with mock.patch.object(cron_dispatcher, '_STOP_GRACE_SECONDS', 1):
            returncode, error_text = cron_dispatcher.run_script(
                _args(run_timeout_seconds=1), name)

        self.assertLess(time.monotonic() - start, 30)
        self.assertEqual((returncode, error_text), (1, 'timed out after 1s'))
        self.assertTrue(self._child_is_gone())

    def test_stops_the_child_when_the_dispatcher_is_stopped(self) -> None:
        '''A SIGTERM to the dispatcher must not leave the job running.'''
        name = self._script(f'''
            import os, time
            with open({self._pid_file()!r}, 'w') as f:
                f.write(str(os.getpid()))
            time.sleep(120)
        ''')

        def stop(signum: int, frame: Any) -> None:
            del signum, frame
            raise SystemExit(143)

        previous = signal.signal(signal.SIGALRM, stop)
        signal.setitimer(signal.ITIMER_REAL, 1)
        start = time.monotonic()
        try:
            with self.assertRaises(SystemExit):
                cron_dispatcher.run_script(_args(), name)
        finally:
            signal.setitimer(signal.ITIMER_REAL, 0)
            signal.signal(signal.SIGALRM, previous)

        self.assertLess(time.monotonic() - start, 30)
        self.assertTrue(self._child_is_gone())

    def test_logs_the_way_a_scheduled_run_does(self) -> None:
        '''Production runs every cron with --log-json.'''
        name = self._script('''
            import sys
            sys.stderr.write(' '.join(sys.argv[1:]))
            sys.exit(1)
        ''')

        _, error_text = cron_dispatcher.run_script(
            _args(log_json=True, verbose=True, logfile='/var/log/cron.log'),
            name)

        assert error_text is not None
        self.assertIn('--log-json --verbose --logfile /var/log/cron.log',
                      error_text)

    def test_copies_the_child_log_to_ours(self) -> None:
        '''A successful rerun still shows up in the cluster logs.'''
        name = self._script('''
            import sys
            sys.stderr.write('ranked 1200 users')
        ''')

        returncode, error_text = cron_dispatcher.run_script(_args(), name)

        self.assertEqual((returncode, error_text), (0, None))
        self.assertEqual(self.stderr.getvalue(), 'ranked 1200 users')

    def test_gives_the_child_no_stdin(self) -> None:
        '''An inherited stdin lets a child block on a prompt forever.'''
        name = self._script('''
            import sys
            sys.stderr.write('STDIN=%r' % sys.stdin.read())
            sys.exit(1)
        ''')

        _, error_text = cron_dispatcher.run_script(_args(), name)

        self.assertEqual(error_text, "STDIN=''")

    def test_keeps_only_the_tail_of_a_noisy_child(self) -> None:
        '''The error stored in the row is bounded, and keeps the end.'''
        name = self._script('''
            import sys
            sys.stderr.write('x' * 200000)
            sys.stderr.write('THE-VERY-END')
            sys.exit(3)
        ''')

        returncode, error_text = cron_dispatcher.run_script(_args(), name)

        assert error_text is not None
        self.assertEqual(returncode, 3)
        self.assertEqual(len(error_text), 1000)
        self.assertTrue(error_text.endswith('THE-VERY-END'))

    def test_survives_invalid_utf8_on_stderr(self) -> None:
        '''Undecodable bytes must not turn into a spurious failure.'''
        name = self._script(r'''
            import sys
            sys.stderr.buffer.write(b'bad \xff\xfe bytes')
            sys.exit(4)
        ''')

        returncode, error_text = cron_dispatcher.run_script(_args(), name)

        self.assertEqual((returncode, error_text), (4, 'bad �� bytes'))

    def test_discards_child_stdout(self) -> None:
        '''Only stderr carries the logs, stdout goes nowhere.'''
        name = self._script('''
            import sys
            sys.stdout.write('y' * 100000)
            sys.stderr.write('only this')
            sys.exit(5)
        ''')

        returncode, error_text = cron_dispatcher.run_script(_args(), name)

        self.assertEqual((returncode, error_text), (5, 'only this'))


_SIGTERM_CHILD = '''
import argparse, os, sys, time
sys.path.insert(0, {stuff!r})
from cron import cron_dispatcher

cron_dispatcher.install_signal_handlers()
args = argparse.Namespace(host='localhost', port=13306, database='omegaup',
                          user='omegaup', password='hunter2',
                          mysql_config_file=None, readonly_user=None,
                          readonly_password=None)
with cron_dispatcher.db_command_args(args) as command:
    print(command[command.index('--mysql-config-file') + 1], flush=True)
    time.sleep(120)
'''


class SignalHandlerTest(unittest.TestCase):
    '''Tests that the credential file does not outlive the dispatcher.'''

    def test_sigterm_removes_the_credential_file(self) -> None:
        '''SIGTERM is how kubernetes stops a pod.'''
        with subprocess.Popen(
                [sys.executable, '-c',
                 _SIGTERM_CHILD.format(stuff=_STUFF_DIR)],
                stdout=subprocess.PIPE, text=True) as child:
            try:
                assert child.stdout is not None
                config_file = child.stdout.readline().strip()
                self.assertTrue(os.path.exists(config_file), config_file)

                child.send_signal(signal.SIGTERM)
                child.wait(timeout=10)

                self.assertFalse(os.path.exists(config_file), config_file)
            finally:
                if child.poll() is None:
                    child.kill()

    def test_installs_handlers_for_both_stop_signals(self) -> None:
        '''SIGINT gets the same treatment as SIGTERM.'''
        previous = [signal.getsignal(signal.SIGTERM),
                    signal.getsignal(signal.SIGINT)]
        try:
            cron_dispatcher.install_signal_handlers()
            for sig in (signal.SIGTERM, signal.SIGINT):
                handler = signal.getsignal(sig)
                self.assertTrue(callable(handler), sig)
                with self.assertRaises(SystemExit):
                    cast(Callable[[int, None], None], handler)(sig, None)
        finally:
            signal.signal(signal.SIGTERM, previous[0])
            signal.signal(signal.SIGINT, previous[1])


if __name__ == '__main__':
    unittest.main()
