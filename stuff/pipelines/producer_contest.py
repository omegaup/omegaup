#!/usr/bin/env python3

'''Send messages to Contest queue in rabbitmq'''

import argparse
import datetime
import logging
import os
import sys
import json

import mysql.connector
import mysql.connector.cursor
import pika

from database.contest import get_contests
import rabbitmq_connection
import rabbitmq_producer

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def send_contest_message_to_client(
        *,
        cur: mysql.connector.cursor.MySQLCursorDict,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        args: argparse.Namespace,
) -> None:
    '''Send messages to contest queue.
     date-lower-limit: initial time from which to be taken the finish contests.
     By default. the 2005/01/01 date will be taken.
     date-upper-limit: finish time from which to be taken the finish contests.
     By default, the current date will be taken.
    '''
    contest_producer = rabbitmq_producer.RabbitmqProducer(
        queue='client_contest',
        exchange='certificates',
        routing_key='ContestQueue',
        channel=channel
    )

    contestants = get_contests(
        cur=cur,
        date_lower_limit=args.date_lower_limit,
        date_upper_limit=args.date_upper_limit,
    )

    for data in contestants:
        message = json.dumps(data)
        contest_producer.send_message(message)


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    rabbitmq_connection.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur, \
            rabbitmq_connection.connect(username=args.rabbitmq_username,
                                        password=args.rabbitmq_password,
                                        host=args.rabbitmq_host,
                                        ) as channel:
            send_contest_message_to_client(
                cur=cur,
                channel=channel,
                args=args)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
