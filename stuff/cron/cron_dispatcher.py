#!/usr/bin/env python3
'''Dispatches manual cron rerun requests.

The web layer only enqueues rows in `Cron_Run_Requests`; this trusted worker is
the single place that actually launches a job. It claims each pending request,
runs the requested (registered) script, records the outcome and notifies
whoever asked for the rerun.
'''

import argparse
import json
import logging
import os
import subprocess
import sys

from typing import Callable, List, NamedTuple, Optional, Tuple

import mysql.connector.cursor

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

_CRON_DIR = os.path.dirname(os.path.realpath(__file__))

# Only registered scripts may be launched, so a tampered request row can never
# run an arbitrary program.
_ALLOWED_JOBS = frozenset((
    'update_ranks.py',
    'assign_badges.py',
    'aggregate_feedback.py',
))

_MAX_ERROR_LENGTH = 1000


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
    cur.execute('''
        SELECT
            request_id, name, requested_by
        FROM
            Cron_Run_Requests
        WHERE
            status = 'pending'
        ORDER BY
            requested_at ASC;''')
    return [
        RerunRequest(row['request_id'], row['name'], row['requested_by'])
        for row in cur.fetchall()
    ]


def _latest_run_id(
    cur: mysql.connector.cursor.MySQLCursorDict,
    name: str,
) -> Optional[int]:
    '''Best-effort link to the run the rerun produced.'''
    cur.execute(
        'SELECT MAX(run_id) AS run_id FROM Cron_Runs WHERE name = %s;',
        (name,))
    row = cur.fetchone()
    if row is None or row['run_id'] is None:
        return None
    return int(row['run_id'])


def _db_command_args(args: argparse.Namespace) -> List[str]:
    '''Rebuilds the DB connection flags to hand down to the child script.'''
    command = ['--host', args.host, '--port', str(args.port),
               '--database', args.database]
    if args.mysql_config_file:
        command.extend(['--mysql-config-file', args.mysql_config_file])
    if args.user:
        command.extend(['--user', args.user])
    if args.password is not None:
        command.extend(['--password', args.password])
    return command


def run_script(
    args: argparse.Namespace,
    name: str,
) -> Tuple[int, Optional[str]]:
    '''Runs a registered cron script as a subprocess.'''
    command = [sys.executable, os.path.join(_CRON_DIR, name)]
    command.extend(_db_command_args(args))
    result = subprocess.run(command, check=False, capture_output=True,
                            text=True)
    if result.returncode == 0:
        return (0, None)
    return (result.returncode, (result.stderr or '')[-_MAX_ERROR_LENGTH:])


def _notify_requester(
    cur: mysql.connector.cursor.MySQLCursorDict,
    user_id: Optional[int],
    name: str,
    succeeded: bool,
) -> None:
    '''Notifies the admin who requested the rerun of its outcome.'''
    if user_id is None:
        return
    cur.execute(
        '''
        INSERT INTO
            Notifications (user_id, contents)
        VALUES (%s, %s);''',
        (user_id,
         json.dumps({
             'type': 'cron_rerun',
             'job': name,
             'status': 'done' if succeeded else 'failed',
         })))


def process_requests(
    dbconn: lib.db.Connection,
    cur: mysql.connector.cursor.MySQLCursorDict,
    args: argparse.Namespace,
    run_command: RunCommand = run_script,
) -> int:
    '''Claims and runs every pending request. Returns the number processed.'''
    requests = get_pending_requests(cur)
    for request in requests:
        if request.name not in _ALLOWED_JOBS:
            cur.execute(
                '''
                UPDATE Cron_Run_Requests
                SET status = 'failed', finished_at = NOW(), error_text = %s
                WHERE request_id = %s;''',
                ('job is not registered', request.request_id))
            dbconn.conn.commit()
            logging.warning('Skipped unregistered job %s', request.name)
            continue

        cur.execute(
            '''
            UPDATE Cron_Run_Requests
            SET status = 'picked', picked_at = NOW()
            WHERE request_id = %s;''',
            (request.request_id,))
        dbconn.conn.commit()

        logging.info('Running rerun of %s', request.name)
        returncode, error_text = run_command(args, request.name)
        succeeded = returncode == 0
        run_id = _latest_run_id(cur, request.name)

        cur.execute(
            '''
            UPDATE Cron_Run_Requests
            SET status = %s, finished_at = NOW(), run_id = %s, error_text = %s
            WHERE request_id = %s;''',
            ('done' if succeeded else 'failed', run_id, error_text,
             request.request_id))
        _notify_requester(cur, request.requested_by, request.name, succeeded)
        dbconn.conn.commit()

    return len(requests)


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
