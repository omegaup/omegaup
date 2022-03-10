#!/usr/bin/python3

'''Send messages to Contest queue in rabbitmq'''

import argparse
import logging
import os
import sys
import json
from typing import Optional
import datetime
from rabbitmq_producer import RabbitmqProducer
import mysql.connector
import mysql.connector.cursor
import pika
import rabbitmq_connection
import test_constants
import credentials
from rabbitmq_database import get_contest_contestants

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def send_contest(
        channel: pika.adapters.blocking_connection.BlockingChannel,
        date_lower_limit: datetime.date = test_constants.DATE_LOWER_LIMIT,
        date_upper_limit: datetime.date = test_constants.DATE_UPPER_LIMIT,
        cur: Optional[mysql.connector.cursor.MySQLCursorDict] = None
) -> None:
    '''Send messages to contest queue.
     date-lower-limit: initial time from which to be taken the finish contests.
     By default. the 2005/01/01 date will be taken.
     date-upper-limit: finish time from which to be taken the finish contests.
     By default, the current date will be taken.
    '''
    contest_producer = RabbitmqProducer(queue='client_contest',
                                        exchange='certificates',
                                        routing_key='ContestQueue',
                                        channel=channel)

    contestants = get_contest_contestants(date_lower_limit=date_lower_limit,
                                          date_upper_limit=date_upper_limit,
                                          cur=cur)

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
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur, \
            rabbitmq_connection.connect(username=credentials.OMEGAUP_USERNAME,
                                        password=credentials.OMEGAUP_PASSWORD,
                                        host=credentials.RABBITMQ_HOST
                                        ) as channel:
            send_contest(channel,
                         args.date_lower_limit,
                         args.date_upper_limit,
                         cur)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
