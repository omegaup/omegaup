#!/usr/bin/python3

'''Send messages to Contest queue in rabbitmq'''

import argparse
import logging
import os
import sys
import json
import datetime
import mysql.connector
import mysql.connector.cursor
import pika
import rabbitmq_connection
import credentials

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))

import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def send_contest(cur: mysql.connector.cursor.MySQLCursorDict,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 date_lower_limit: datetime.date,
                 date_upper_limit: datetime.date) -> None:
    '''Send messages to contest queue.
     date-lower-limit: initial time from which to be taken the finish contests.
     By default. the 2005/01/01 date will be taken.
     date-upper-limit: finish time from which to be taken the finish contests.
     By default, the current date will be taken.
    '''
    channel.queue_declare(
        'client_contest',
        passive=False,
        durable=False,
        exclusive=False,
        auto_delete=False,
    )
    channel.exchange_declare(
        exchange='certificates',
        auto_delete=False,
        durable=True,
        exchange_type='direct',
    )
    logging.info('Send messages to Contest_Queue')
    cur.execute(
        '''
        SELECT
            certificate_cutoff,
            c.contest_id,
            alias,
            scoreboard_url
        FROM
            Contests c
        INNER JOIN
            Problemsets p
        ON
            c.problemset_id = p.problemset_id
        WHERE
            finish_time BETWEEN %s AND %s;
        ''', (date_lower_limit, date_upper_limit)
    )
    for row in cur:
        data = {
            'certificate_cutoff': row['certificate_cutoff'],
            'alias': row['alias'],
            'scoreboard_url': row['scoreboard_url'],
            'contest_id': row['contest_id'],
        }
        message = json.dumps(data)
        body = message.encode()
        channel.basic_publish(
            exchange='certificates',
            routing_key='ContestQueue',
            body=body)


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    rabbitmq_connection.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(
        host=credentials.MYSQL_HOST,
        user=credentials.MYSQL_USERNAME,
        password=credentials.MYSQL_PASSWORD,
        database=credentials.MYSQL_DATABASE
    )

    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur, \
            rabbitmq_connection.connect(username=credentials.OMEGAUP_USERNAME,
                                        password=credentials.OMEGAUP_PASSWORD,
                                        host=credentials.RABBITMQ_HOST
                                        ) as channel:
            send_contest(cur, channel,
                         args.date_lower_limit,
                         args.date_upper_limit)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
