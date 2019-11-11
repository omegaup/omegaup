#!/usr/bin/python3

''' Assigns users badges and creates the notifications.'''

import argparse
import json
import logging
import os
import sys
from typing import Set

import MySQLdb

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


BADGES_PATH = os.path.abspath(os.path.join(__file__, '..', '..',
                                           '..', 'frontend/badges'))


def get_all_owners(badge: str, cur: MySQLdb.cursors.DictCursor) -> Set[int]:
    '''Returns a set of ids of users who should receive the badge'''
    with open(os.path.join(BADGES_PATH, badge, 'query.sql')) as fd:
        query = fd.read()
    cur.execute(query)
    results = set()
    for row in cur:
        results.add(row['user_id'])
    return results


def get_current_owners(badge: str,
                       cur: MySQLdb.cursors.DictCursor) -> Set[int]:
    '''Returns a set of ids of current badge owners'''
    cur.execute('''
        SELECT
            ub.user_id
        FROM
            Users_Badges ub
        WHERE
            ub.badge_alias = '%s';''' % badge)
    results = set()
    for row in cur:
        results.add(row['user_id'])
    return results


def save_new_owners(badge: str, users: Set[int],
                    cur: MySQLdb.cursors.DictCursor) -> None:
    '''Adds new badge owners entries to Users_Badges table'''
    badges_tuples = []
    notifications_tuples = []
    for user in users:
        badges_tuples.append((user, badge))
        notifications_tuples.append((
            user, json.dumps({'type': 'badge', 'badge': badge})))
    cur.executemany('''
        INSERT INTO
            Users_Badges (user_id, badge_alias)
        VALUES (%s, %s);''', badges_tuples)
    cur.executemany('''
        INSERT INTO
            Notifications (user_id, contents)
        VALUES (%s, %s)''', notifications_tuples)


def process_badges(cur: MySQLdb.cursors.DictCursor) -> None:
    '''Processes all badges'''
    badges = [f.name for f in os.scandir(BADGES_PATH) if f.is_dir()]
    for badge in badges:
        logging.info('==== Badge %s ====', badge)
        try:
            all_owners = get_all_owners(badge, cur)
            current_owners = get_current_owners(badge, cur)
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

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            process_badges(cur)  # type: ignore
        dbconn.commit()
    except:  # noqa: bare-except
        logging.exception(
            'Failed to assign all badges and create notifications.')
    finally:
        dbconn.close()
        logging.info('Finished')


if __name__ == '__main__':
    main()
