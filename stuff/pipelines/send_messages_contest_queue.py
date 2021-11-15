#!/usr/bin/python3

'''Send messages to queues in rabbitmq'''

import argparse
import logging
import os
import sys
import json
from typing import Optional
import datetime
import MySQLdb
import MySQLdb.cursors
import pika

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def send_messages_contest_queue(cur: MySQLdb.cursors.BaseCursor,
                                rabbit_user: str,
                                rabbit_password: str,
                                low_date: str,
                                upper_date: Optional[str] = None) -> None:
    '''Send messages to contest queue
    low_date: initial time from which to be taken the finishes courses
    upper_date: Optional finish time from which to be taken
      the finishes courses. By default, the current date will be taken
    '''
    if upper_date is None:
        upper_date = str(datetime.date.today())
    credentials = pika.PlainCredentials(rabbit_user, rabbit_password)
    parameters = pika.ConnectionParameters('rabbitmq', 5672, '/', credentials)
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    channel.exchange_declare(exchange='logs_exchange', exchange_type='direct')
    logging.info('Send messages to Contest_Queue')
    cur.execute(
        '''
        SELECT
            contest_id
        FROM
            Contests
        WHERE
            finish_time BETWEEN %s AND %s;
        ''', (low_date, upper_date)
    )
    try:
        for row in cur:
            data = {"contest_id": str(row['contest_id'])}
            message = json.dumps(data)
            body = message.encode()
            channel.basic_publish(
                exchange='logs_exchange',
                routing_key='ContestQueue',
                body=body)
    finally:
        connection.close()


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    parser.add_argument('--user_rabbit')
    parser.add_argument('--password_rabbit')
    parser.add_argument('--date_low_limit')
    parser.add_argument('--date_upper_limit')

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            send_messages_contest_queue(cur, args.user_rabbit,
                                        args.password_rabbit,
                                        args.date_low_limit,
                                        args.date_upper_limit)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
