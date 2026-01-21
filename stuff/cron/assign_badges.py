#!/usr/bin/env python3
''' Assigns users badges and creates the notifications.'''

import argparse
import datetime
import json
import logging
import os
import sys
from typing import Optional, Set

import mysql.connector.cursor

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

BADGES_PATH = os.path.abspath(
    os.path.join(__file__, '..', '..', '..', 'frontend/badges'))


def get_all_owners(
    badge: str,
    current_timestamp: Optional[datetime.datetime],
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
) -> Set[int]:
    '''Returns a set of ids of users who should receive the badge'''
    with open(os.path.join(BADGES_PATH, badge, 'query.sql'),
              encoding='utf-8') as fd:
        query = fd.read()
    if current_timestamp is not None:
        query = query.replace(
            'NOW()', f"'{current_timestamp.strftime('%Y-%m-%d %H:%M:%S')}'")
    cur_readonly.execute(query)
    return set(row['user_id'] for row in cur_readonly)


def get_current_owners(
    badge: str,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
) -> Set[int]:
    '''Returns a set of ids of current badge owners'''
    cur_readonly.execute(f'''
        SELECT
            ub.user_id
        FROM
            Users_Badges ub
        WHERE
            ub.badge_alias = '{badge}';''')
    return set(row['user_id'] for row in cur_readonly)


def save_new_owners(badge: str, users: Set[int],
                    cur: mysql.connector.cursor.MySQLCursorDict) -> None:
    '''Adds new badge owners entries to Users_Badges table'''
    badges_tuples = []
    notifications_tuples = []
    for user in users:
        badges_tuples.append((user, badge))
        notifications_tuples.append(
            (user, json.dumps({
                'type': 'badge',
                'badge': badge,
            })))
    cur.executemany(
        '''
        INSERT INTO
            Users_Badges (user_id, badge_alias)
        VALUES (%s, %s);''', badges_tuples)
    cur.executemany(
        '''
        INSERT INTO
            Notifications (user_id, contents)
        VALUES (%s, %s)''', notifications_tuples)


def process_badges(
    current_timestamp: Optional[datetime.datetime],
    cur: mysql.connector.cursor.MySQLCursorDict,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
) -> None:
    '''Processes all badges'''
    badges = [f.name for f in os.scandir(BADGES_PATH) if f.is_dir()]
    for badge in badges:
        logging.info('==== Badge %s ====', badge)
        try:
            all_owners = get_all_owners(badge, current_timestamp, cur_readonly)
            current_owners = get_current_owners(badge, cur_readonly)
            new_owners = all_owners - current_owners
            logging.info('New owners: %s', new_owners)
            if new_owners:
                save_new_owners(badge, new_owners, cur)
        except:  # noqa: bare-except
            logging.exception('Something went wrong with badge: %s.', badge)
            raise


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(
        description='Assign badges and create notifications.')

    parser.add_argument(
        '--current-timestamp',
        type=lambda s: datetime.datetime.strptime(s, '%Y-%m-%d %H:%M:%S'))

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    dbconn_readonly = lib.db.connect_readonly(
        lib.db.DatabaseConnectionArguments.from_args_readonly(args)) or dbconn
    try:
        with dbconn.cursor(buffered=True,
                           dictionary=True) as cur, dbconn_readonly.cursor(
                               buffered=True, dictionary=True) as cur_readonly:
            process_badges(args.current_timestamp, cur, cur_readonly)
        dbconn.conn.commit()
    finally:
        dbconn.conn.close()
        logging.info('Finished')


if __name__ == '__main__':
    main()
