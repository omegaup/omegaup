#!/usr/bin/python3

'''Send messages to Contest queue in rabbitmq'''

import argparse
import logging
import os
import sys
import json
import datetime
import MySQLdb
import MySQLdb.cursors
import pika
from rabbit_connection import rabbit
import rabbit_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))

import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def send_contest(cur: MySQLdb.cursors.BaseCursor,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 date_lower_limit: datetime.date,
                 date_upper_limit: datetime.date) -> None:
    '''Send messages to contest queue
    date-lower-limit: initial time from which to be taken the finishes contest
    date-upper-limit: Optional finish time from which to be taken
      the finishes contest. By default, the current date will be taken
    '''
    logging.info('Send messages to Contest_Queue')
    cur.execute(
        '''
        SELECT
            contest_id
        FROM
            Contests
        WHERE
            finish_time BETWEEN %s AND %s;
        ''', (date_lower_limit, date_upper_limit)
    )
    for row in cur:
        data = {"contest_id": row['contest_id']}
        message = json.dumps(data)
        body = message.encode()
        channel.basic_publish(
            exchange='certicates',
            routing_key='ContestQueue',
            body=body)


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    rabbit_connection.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(args)
    rabbit_conn = rabbit(args)

    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            send_contest(cur, rabbit_conn.channel,
                         args.date_lower_limit,
                         args.date_upper_limit)
    finally:
        dbconn.close()
        rabbit_conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
