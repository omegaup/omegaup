#!/usr/bin/env python3
'''Dispatches manual cron rerun requests.

The web layer only enqueues rows in `Cron_Run_Requests`; this trusted worker is
the single place that actually launches a job.
'''

import argparse
import contextlib
import enum
import json
import logging
import os
import signal
import subprocess
import sys
import tempfile
import types

from typing import (Callable, Iterator, List, NamedTuple, Optional, Set,
                    Tuple)

import mysql.connector.cursor

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

_CRON_DIR = os.path.dirname(os.path.realpath(__file__))

_MAX_ERROR_LENGTH = 1000

DEFAULT_RUN_TIMEOUT_SECONDS = 6 * 3600

# A picked request is only abandoned once its run can no longer be alive.
REAP_MARGIN_SECONDS = 600

# A request nothing claimed means no dispatcher was running when it was made.
STALE_PENDING_HOURS = 1

_STALE_PICK_ERROR = 'the dispatcher did not finish this run'
_INTERRUPTED_ERROR = 'the dispatcher stopped before this run finished'
_STALE_PENDING_ERROR = 'no dispatcher picked up this run'
_UNREGISTERED_ERROR = 'job is not registered'
_UNCONFIGURED_ERROR = 'the dispatcher was not told where this job writes'
_SKIPPED_ERROR = 'the job did not run: it is disabled or already running'

# Mirrors `\OmegaUp\CronJobName`; the test fails if the two ever drift.
KNOWN_JOBS = frozenset({
    'aggregate_feedback.py',
    'assign_badges.py',
    'build_problem_rec_model.py',
    'problem_health_check.py',
    'update_ranks.py',
})


# The training job has no default for where the model goes.
_MODEL_JOB = 'build_problem_rec_model.py'


class RequestStatus(enum.Enum):
    '''The states a rerun request can be in, as stored in `status`.

    Mirrors `\\OmegaUp\\CronRunRequestStatus` on the PHP side.
    '''
    PENDING = 'pending'
    PICKED = 'picked'
    DONE = 'done'
    FAILED = 'failed'


class RerunRequest(NamedTuple):
    '''A queued manual rerun request.'''
    request_id: int
    name: str
    requested_by: Optional[int]


class Outcome(NamedTuple):
    '''How a claimed request ended.'''
    status: RequestStatus
    run_id: Optional[int]
    error_text: Optional[str]


class Summary(NamedTuple):
    '''What one pass over the queue did.'''
    processed: int
    failed: int


RunCommand = Callable[[argparse.Namespace, str], Tuple[int, Optional[str]]]


def get_pending_requests(
    cur: mysql.connector.cursor.MySQLCursorDict,
) -> List[RerunRequest]:
    '''Returns the queued rerun requests, oldest first.'''
    cur.execute(
        '''
        SELECT
            request_id, name, requested_by
        FROM
            Cron_Run_Requests
        WHERE
            status = %s
        ORDER BY
            requested_at ASC;''',
        (RequestStatus.PENDING.value,))
    return [
        RerunRequest(row['request_id'], row['name'], row['requested_by'])
        for row in cur.fetchall()
    ]


def get_registered_jobs(
    cur: mysql.connector.cursor.MySQLCursorDict,
) -> Set[str]:
    '''Returns the job names the registry knows about.'''
    cur.execute('SELECT name FROM Cron_Jobs;')
    return {row['name'] for row in cur.fetchall()}


def is_launchable(name: str, registered: Set[str]) -> bool:
    '''Whether a request names a known script installed in this checkout.

    A launchable name has to be in the compiled-in allowlist, in the registry
    and on disk, so a tampered `Cron_Jobs` row cannot reach anything else.
    '''
    return (name in KNOWN_JOBS
            and name in registered
            and os.path.isfile(os.path.join(_CRON_DIR, name)))


def job_args(args: argparse.Namespace, name: str) -> Optional[List[str]]:
    '''Returns the flags a job needs beyond the database ones.

    None means the job cannot run with what this dispatcher was given.
    '''
    if name != _MODEL_JOB:
        return []
    if not args.rec_model_output:
        return None
    return ['--output', args.rec_model_output]


def _stale_requests(
    cur: mysql.connector.cursor.MySQLCursorDict,
    stale_pick_seconds: int,
) -> List[Tuple[RerunRequest, RequestStatus, str]]:
    '''Returns the requests no dispatcher is going to finish.'''
    stale = []
    cur.execute(
        '''
        SELECT
            request_id, name, requested_by
        FROM
            Cron_Run_Requests
        WHERE
            status = %s
            AND picked_at < DATE_SUB(NOW(), INTERVAL %s SECOND);''',
        (RequestStatus.PICKED.value, stale_pick_seconds))
    for row in cur.fetchall():
        stale.append((
            RerunRequest(row['request_id'], row['name'], row['requested_by']),
            RequestStatus.PICKED, _STALE_PICK_ERROR))
    cur.execute(
        '''
        SELECT
            request_id, name, requested_by
        FROM
            Cron_Run_Requests
        WHERE
            status = %s
            AND requested_at < DATE_SUB(NOW(), INTERVAL %s HOUR);''',
        (RequestStatus.PENDING.value, STALE_PENDING_HOURS))
    for row in cur.fetchall():
        stale.append((
            RerunRequest(row['request_id'], row['name'], row['requested_by']),
            RequestStatus.PENDING, _STALE_PENDING_ERROR))
    return stale


def fail_stale_requests(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    stale_pick_seconds: int,
) -> int:
    '''Fails the requests nothing is going to finish, and says so.

    Either status blocks `getActiveByName`, so an abandoned row leaves the
    job's Rerun button dead until something resolves it. Returns how many
    were failed.
    '''
    failed = 0
    for request, status, error_text in _stale_requests(cur,
                                                       stale_pick_seconds):
        cur.execute(
            '''
            UPDATE Cron_Run_Requests
            SET status = %s, finished_at = NOW(), error_text = %s
            WHERE request_id = %s AND status = %s;''',
            (RequestStatus.FAILED.value, error_text, request.request_id,
             status.value))
        if cur.rowcount != 1:
            continue
        logging.warning('Request %d for %s was abandoned: %s',
                        request.request_id, request.name, error_text)
        _notify_requester(cur, request.requested_by, request.name,
                          RequestStatus.FAILED)
        failed += 1
    dbconn.conn.commit()
    return failed


def _claim_request(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    request_id: int,
) -> bool:
    '''Takes a request, or returns False if another dispatcher got it first.

    The status the row is expected to be in is part of the UPDATE, so only the
    dispatcher whose UPDATE changed the row goes on to run the job.
    '''
    cur.execute(
        '''
        UPDATE Cron_Run_Requests
        SET status = %s, picked_at = NOW()
        WHERE request_id = %s AND status = %s;''',
        (RequestStatus.PICKED.value, request_id, RequestStatus.PENDING.value))
    claimed = cur.rowcount == 1
    dbconn.conn.commit()
    return claimed


def _latest_run_id(
    cur: mysql.connector.cursor.MySQLCursorDict,
    name: str,
) -> Optional[int]:
    '''Returns the newest run recorded for a job, if any.'''
    cur.execute(
        'SELECT MAX(run_id) AS run_id FROM Cron_Runs WHERE name = %s;',
        (name,))
    row = cur.fetchone()
    if row is None or row['run_id'] is None:
        return None
    return int(row['run_id'])


def _is_still_running(
    cur: mysql.connector.cursor.MySQLCursorDict,
    run_id: int,
) -> bool:
    '''Whether a run has not recorded how it ended.'''
    cur.execute('SELECT status FROM Cron_Runs WHERE run_id = %s;', (run_id,))
    row = cur.fetchone()
    return bool(row is not None and row['status'] == 'running')


@contextlib.contextmanager
def db_command_args(args: argparse.Namespace) -> Iterator[List[str]]:
    '''Yields the DB flags to hand down to the child script.'''
    command = ['--host', args.host, '--port', str(args.port),
               '--database', args.database]
    if args.password is None or args.user is None:
        if args.mysql_config_file:
            command.extend(['--mysql-config-file', args.mysql_config_file])
        if args.user:
            command.extend(['--user', args.user])
        yield command
        return
    # Anyone on the machine can read a process' command line, so the password
    # travels down in a file only this user can open.
    with tempfile.NamedTemporaryFile(mode='w', suffix='.cnf') as config_file:
        config_file.write(f'[client]\n'
                          f'host={args.host}\n'
                          f'port={args.port}\n'
                          f'user={args.user}\n'
                          f'password={args.password}\n')
        config_file.flush()
        yield command + ['--mysql-config-file', config_file.name]


def run_script(
    args: argparse.Namespace,
    name: str,
) -> Tuple[int, Optional[str]]:
    '''Runs a registered cron script as a subprocess.

    stderr goes to a file rather than a pipe and only its tail is read back,
    so a job that writes gigabytes cannot grow the dispatcher.
    '''
    timeout = args.run_timeout_seconds
    with db_command_args(args) as db_args:
        command = ([sys.executable, os.path.join(_CRON_DIR, name)] + db_args
                   + (job_args(args, name) or []))
        with tempfile.TemporaryFile() as stderr_file:
            try:
                result = subprocess.run(command, check=False,
                                        stdin=subprocess.DEVNULL,
                                        stdout=subprocess.DEVNULL,
                                        stderr=stderr_file,
                                        timeout=timeout)
            except subprocess.TimeoutExpired:
                logging.error('Rerun of %s timed out after %ds', name, timeout)
                return (1, f'timed out after {timeout}s')
            if result.returncode == 0:
                return (0, None)
            end = stderr_file.seek(0, os.SEEK_END)
            stderr_file.seek(max(0, end - 4 * _MAX_ERROR_LENGTH))
            tail = stderr_file.read().decode('utf-8', errors='replace')
    return (result.returncode, tail[-_MAX_ERROR_LENGTH:])


def _notify_requester(
    cur: mysql.connector.cursor.MySQLCursorDict,
    user_id: Optional[int],
    name: str,
    status: RequestStatus,
) -> None:
    '''Notifies the admin who requested the rerun of its outcome.'''
    if user_id is None:
        return
    succeeded = status is RequestStatus.DONE
    localization_string = (
        'notificationCronRerunSucceeded'
        if succeeded else 'notificationCronRerunFailed')
    cur.execute(
        '''
        INSERT INTO
            Notifications (user_id, contents)
        VALUES (%s, %s);''',
        (user_id,
         json.dumps({
             'type': 'cron-rerun',
             'status': status.value,
             'body': {
                 'localizationString': localization_string,
                 'localizationParams': {'jobName': name},
                 'url': '/admin/crons/',
                 'iconUrl': '/media/info.png',
             },
         })))


def _reject_request(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    request: RerunRequest,
    error_text: str,
) -> None:
    '''Fails a request that must not be launched at all, and says so.'''
    cur.execute(
        '''
        UPDATE Cron_Run_Requests
        SET status = %s, finished_at = NOW(), error_text = %s
        WHERE request_id = %s AND status = %s;''',
        (RequestStatus.FAILED.value, error_text, request.request_id,
         RequestStatus.PENDING.value))
    if cur.rowcount == 1:
        _notify_requester(cur, request.requested_by, request.name,
                          RequestStatus.FAILED)
    dbconn.conn.commit()


def _run_claimed(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    args: argparse.Namespace,
    request: RerunRequest,
    run_command: RunCommand,
) -> Outcome:
    '''Runs a claimed request and works out how it ended.'''
    previous_run_id = _latest_run_id(cur, request.name)
    # Ends the read view, which REPEATABLE READ would otherwise reuse.
    dbconn.conn.commit()
    logging.info('Running rerun of %s', request.name)
    try:
        returncode, error_text = run_command(args, request.name)
    except Exception as exc:  # pylint: disable=broad-except
        logging.exception('Rerun of %s raised', request.name)
        returncode, error_text = (1, str(exc)[-_MAX_ERROR_LENGTH:])
    status = RequestStatus.DONE if returncode == 0 else RequestStatus.FAILED
    # The connection sat idle for the whole run and may have been dropped.
    dbconn.conn.ping(reconnect=True, attempts=3, delay=1)
    run_id = _latest_run_id(cur, request.name)
    if run_id == previous_run_id:
        run_id = None
    elif (run_id is not None and status is RequestStatus.DONE
          and _is_still_running(cur, run_id)):
        # A scheduled run of the same job took the lock first. It is not ours.
        run_id = None
    if run_id is None and status is RequestStatus.DONE:
        # lib.runner exits 0 when it skips, so this never ran.
        status = RequestStatus.FAILED
        error_text = _SKIPPED_ERROR
    return Outcome(status, run_id, error_text)


def _finish_request(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    request: RerunRequest,
    outcome: Outcome,
) -> None:
    '''Records how a claimed request ended and tells whoever asked.'''
    cur.execute(
        '''
        UPDATE Cron_Run_Requests
        SET status = %s, finished_at = NOW(), run_id = %s, error_text = %s
        WHERE request_id = %s AND status = %s;''',
        (outcome.status.value, outcome.run_id, outcome.error_text,
         request.request_id, RequestStatus.PICKED.value))
    if cur.rowcount == 1:
        _notify_requester(cur, request.requested_by, request.name,
                          outcome.status)
    else:
        logging.warning('Request %d was resolved by someone else',
                        request.request_id)
    dbconn.conn.commit()


def process_requests(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    args: argparse.Namespace,
    run_command: RunCommand = run_script,
) -> Summary:
    '''Claims and runs every pending request.'''
    failed = fail_stale_requests(
        dbconn, cur, args.run_timeout_seconds + REAP_MARGIN_SECONDS)
    registered = get_registered_jobs(cur)
    processed = 0
    for request in get_pending_requests(cur):
        rejection = None
        if not is_launchable(request.name, registered):
            rejection = _UNREGISTERED_ERROR
        elif job_args(args, request.name) is None:
            rejection = _UNCONFIGURED_ERROR
        if rejection is not None:
            _reject_request(dbconn, cur, request, rejection)
            logging.warning('Rejected rerun of %s: %s', request.name,
                            rejection)
            processed += 1
            failed += 1
            continue

        if not _claim_request(dbconn, cur, request.request_id):
            logging.info('Request %d was already claimed', request.request_id)
            continue

        # Whatever stops the run, the request must not stay picked.
        outcome = Outcome(RequestStatus.FAILED, None, _INTERRUPTED_ERROR)
        try:
            outcome = _run_claimed(dbconn, cur, args, request, run_command)
        finally:
            _finish_request(dbconn, cur, request, outcome)
        processed += 1
        if outcome.status is RequestStatus.FAILED:
            failed += 1

    return Summary(processed, failed)


def install_signal_handlers() -> None:
    '''Turns a stop signal into SystemExit so the credential file is removed.

    The default SIGTERM disposition kills the process without unwinding, which
    leaves the temporary `.cnf` holding the database password on disk.
    '''
    def _exit(signum: int, frame: Optional[types.FrameType]) -> None:
        del frame
        sys.exit(128 + signum)

    for sig in (signal.SIGTERM, signal.SIGINT):
        signal.signal(sig, _exit)


def _positive_int(value: str) -> int:
    '''argparse type for a number that has to be at least one.'''
    number = int(value)
    if number < 1:
        raise argparse.ArgumentTypeError(f'{value} is not a positive integer')
    return number


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(
        description='Dispatch manual cron rerun requests.')
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    parser.add_argument('--run-timeout-seconds',
                        type=_positive_int,
                        default=DEFAULT_RUN_TIMEOUT_SECONDS,
                        help=('Seconds a single rerun may take before it is '
                              'killed and recorded as failed'))
    parser.add_argument('--rec-model-output',
                        type=str,
                        help=('Where a rerun of build_problem_rec_model.py '
                              'writes the model. Without it that job cannot '
                              'be rerun'))
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    install_signal_handlers()

    logging.info('Started')
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments.from_args(args))
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur:
            summary = process_requests(dbconn, cur, args)
        logging.info('Processed %d rerun request(s), %d failed',
                     summary.processed, summary.failed)
    finally:
        dbconn.conn.close()
        logging.info('Finished')
    if summary.failed:
        sys.exit(1)


if __name__ == '__main__':
    main()
