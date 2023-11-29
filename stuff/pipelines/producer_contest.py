#!/usr/bin/env python3

'''Send messages to Contest queue in rabbitmq'''

import argparse
import datetime
import json
import logging
import os
import sys

from typing import List
import mysql.connector
import mysql.connector.cursor
import omegaup.api
import pika

import database.contest
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
        date_lower_limit: datetime.datetime = datetime.datetime(2005, 1, 1),
        date_upper_limit: datetime.datetime = datetime.datetime.now(),
        client: omegaup.api.Client,
) -> None:
    '''Send messages to contest queue.
     date-lower-limit: initial time from which to be taken the finish contests.
     By default. the 2005/01/01 date will be taken.
     date-upper-limit: finish time from which to be taken the finish contests.
     By default, the current date will be taken.

     When API token and URL are given, it is possible to send the messages.
    '''
    contest_producer = rabbitmq_producer.RabbitmqProducer(
        queue='client_contest',
        exchange='certificates',
        routing_key='ContestQueue',
        channel=channel
    )

    contestants = get_contests_from_db(
        cur=cur,
        date_lower_limit=date_lower_limit,
        date_upper_limit=date_upper_limit,
        client=client
    )

    for data in contestants:
        message = json.dumps(data._asdict())
        contest_producer.send_message(message)


def get_contests_from_db(
    *,
    cur: mysql.connector.cursor.MySQLCursorDict,
    date_lower_limit: datetime.datetime,
    date_upper_limit: datetime.datetime,
    client: omegaup.api.Client
) -> List[database.contest.ContestCertificate]:
    ''''A intermediate function in order to mock the original one'''
    certificates = database.contest.get_all_certificates_for_contests(cur=cur)

    return database.contest.get_contests(
        cur=cur,
        date_lower_limit=date_lower_limit,
        date_upper_limit=date_upper_limit,
        certificates=certificates,
        client=client,
    )


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--api-token', type=str, help='omegaup api token')
    parser.add_argument('--url',
                        type=str,
                        help='omegaup api URL',
                        default='https://omegaup.com')
    parser.add_argument('--date-lower-limit',
                        type=lambda s:
                        datetime.datetime.strptime(s, '%Y-%m-%d'),
                        help='date lower limit',
                        default=datetime.datetime(2005, 1, 1))
    parser.add_argument('--date-upper-limit',
                        type=lambda s:
                        datetime.datetime.strptime(s, '%Y-%m-%d'),
                        help='date upper limit',
                        default=datetime.datetime.today())
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    rabbitmq_connection.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    client = omegaup.api.Client(api_token=args.api_token, url=args.url)
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur, \
            rabbitmq_connection.connect(username=args.rabbitmq_username,
                                        password=args.rabbitmq_password,
                                        host=args.rabbitmq_host) as channel:
            send_contest_message_to_client(
                cur=cur,
                channel=channel,
                date_lower_limit=args.date_lower_limit,
                date_upper_limit=args.date_upper_limit,
                client=client)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
