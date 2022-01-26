#!/usr/bin/python3

'''Send messages to queues in rabbitmq'''

import argparse
import datetime
import logging
import os
import sys
import json
from typing import Any, Dict
import mysql.connector
import mysql.connector.cursor
import pika
import rabbitmq_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def get_coder_of_the_month(cur: mysql.connector.cursor.MySQLCursorDict,
                           category: str) -> Dict[str, Any]:
    '''Get coder of the month'''
    today = datetime.date.today()
    first_day_of_current_month = today.replace(day=1)
    if first_day_of_current_month.month == 12:
        first_day_of_next_month = datetime.date(
            first_day_of_current_month.year + 1,
            1,
            1)
    else:
        first_day_of_next_month = datetime.date(
            first_day_of_current_month.year,
            first_day_of_current_month.month + 1,
            1)
    cur.execute(
        '''
                SELECT
                    user_id, time, category
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = %s AND
                    `selected_by` IS NOT NULL AND
                    `category` = %s;
        ''', (first_day_of_next_month, category))
    for row in cur:
        data = {"user_id": row['user_id'],
                "time": row['time'],
                "category": row['category']}
    return data


def send_coder_month(cur: mysql.connector.cursor.MySQLCursorDict,
                     channel:
                     pika.adapters.blocking_connection.BlockingChannel,
                     category: str) -> None:
    '''Send messages to coder of the month queue'''
    channel.queue_declare("coder_month", passive=False,
                          durable=False, exclusive=False,
                          auto_delete=False)
    channel.exchange_declare(exchange='certificates',
                             auto_delete=False,
                             durable=True,
                             exchange_type='direct')
    logging.info('Send messages to Coder_Month_Queue')
    data = get_coder_of_the_month(cur, category)
    message = json.dumps(data)
    body = message.encode()
    channel.basic_publish(exchange='certificates',
                          routing_key='CoderOfTheMonthQueue',
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
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur, \
            rabbitmq_connection.connect(args) as channel:
            send_coder_month(cur, channel, 'all')
            send_coder_month(cur, channel, 'female')
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
