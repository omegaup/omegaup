#!/usr/bin/env python3
'''Dispatches manual cron rerun requests.

The web layer only enqueues rows in `Cron_Run_Requests`; this trusted worker is
the single place that actually launches a job.
'''

import argparse
import contextlib
import json
import logging
import os
import subprocess
import sys
import tempfile

import enum
from typing import Callable, Iterator, List, NamedTuple, Optional, Set, Tuple

import mysql.connector.cursor

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

_CRON_DIR = os.path.dirname(os.path.realpath(__file__))

_MAX_ERROR_LENGTH = 1000

# A dispatcher that dies mid-run would otherwise leave the job unrunnable.
STALE_PICK_HOURS = 6

_STALE_PICK_ERROR = 'the dispatcher did not finish this run'
_UNREGISTERED_ERROR = 'job is not registered'


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
    '''Whether a request names a registered script installed in this checkout.

    `Cron_Jobs` is the registry `\\OmegaUp\\CronJobName` mirrors, so this is
    the same allowlist the API validates against, and the filename check
    keeps a tampered row from reaching anything else.
    '''
    return (name in registered
            and name.endswith('.py')
            and os.path.basename(name) == name
            and os.path.isfile(os.path.join(_CRON_DIR, name)))


def fail_stale_requests(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
) -> None:
    '''Releases requests left behind by a dispatcher that died mid-run.'''
    cur.execute(
        '''
        UPDATE
            Cron_Run_Requests
        SET
            status = %s, finished_at = NOW(), error_text = %s
        WHERE
            status = %s
            AND picked_at < DATE_SUB(NOW(), INTERVAL %s HOUR);''',
        (RequestStatus.FAILED.value, _STALE_PICK_ERROR,
         RequestStatus.PICKED.value, STALE_PICK_HOURS))
    dbconn.conn.commit()


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
    '''Runs a registered cron script as a subprocess.'''
    with db_command_args(args) as db_args:
        command = [sys.executable, os.path.join(_CRON_DIR, name)] + db_args
        result = subprocess.run(command, check=False, capture_output=True,
                                text=True)
    if result.returncode == 0:
        return (0, None)
    return (result.returncode, (result.stderr or '')[-_MAX_ERROR_LENGTH:])


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
             'type': 'cron_rerun',
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
    request_id: int,
    error_text: str,
) -> None:
    '''Fails a request that must not be launched at all.'''
    cur.execute(
        '''
        UPDATE Cron_Run_Requests
        SET status = %s, finished_at = NOW(), error_text = %s
        WHERE request_id = %s AND status = %s;''',
        (RequestStatus.FAILED.value, error_text, request_id,
         RequestStatus.PENDING.value))
    dbconn.conn.commit()


def process_requests(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    args: argparse.Namespace,
    run_command: RunCommand = run_script,
) -> int:
    '''Claims and runs every pending request. Returns the number processed.'''
    fail_stale_requests(dbconn, cur)
    registered = get_registered_jobs(cur)
    processed = 0
    for request in get_pending_requests(cur):
        if not is_launchable(request.name, registered):
            _reject_request(dbconn, cur, request.request_id,
                            _UNREGISTERED_ERROR)
            logging.warning('Skipped unregistered job %s', request.name)
            processed += 1
            continue

        if not _claim_request(dbconn, cur, request.request_id):
            logging.info('Request %d was already claimed', request.request_id)
            continue

        previous_run_id = _latest_run_id(cur, request.name)
        logging.info('Running rerun of %s', request.name)
        try:
            returncode, error_text = run_command(args, request.name)
        except Exception as exc:  # pylint: disable=broad-except
            logging.exception('Rerun of %s raised', request.name)
            returncode, error_text = (1, str(exc)[-_MAX_ERROR_LENGTH:])
        status = (RequestStatus.DONE
                  if returncode == 0 else RequestStatus.FAILED)
        run_id = _latest_run_id(cur, request.name)
        if run_id == previous_run_id:
            # The job was disabled or already running, so it produced no run.
            run_id = None

        cur.execute(
            '''
            UPDATE Cron_Run_Requests
            SET status = %s, finished_at = NOW(), run_id = %s, error_text = %s
            WHERE request_id = %s;''',
            (status.value, run_id, error_text, request.request_id))
        _notify_requester(cur, request.requested_by, request.name, status)
        dbconn.conn.commit()
        processed += 1

    return processed


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(
        description='Dispatch manual cron rerun requests.')
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments.from_args(args))
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur:
            processed = process_requests(dbconn, cur, args)
        logging.info('Processed %d rerun request(s)', processed)
    finally:
        dbconn.conn.close()
        logging.info('Finished')


if __name__ == '__main__':
    main()
