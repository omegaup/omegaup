#!/usr/bin/python3

'''Send messages to queues in rabbitmq'''

import argparse
import datetime
import logging
import os
import sys
import json
import MySQLdb
import MySQLdb.cursors
import pika

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def send_messages_coder_month(cur: MySQLdb.cursors.BaseCursor,
                              category: str,
                              rabbit_user: str,
                              rabbit_password: str) -> None:
    '''Send messages to queues'''
    today = datetime.date.today()
    first_day_of_current_month = today.replace(day=1)
    credentials = pika.PlainCredentials(rabbit_user, rabbit_password)
    parameters = pika.ConnectionParameters('rabbitmq', 5672, '/', credentials)
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    channel.exchange_declare(exchange='logs_exchange', exchange_type='direct')
    logging.info('Send messages to Coder_Month_Queue')
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
    try:
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
            message = json.dumps(data)
            body = message.encode()
            channel.basic_publish(
                exchange='logs_exchange',
                routing_key='CoderOfTheMonthQueue',
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

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            send_messages_coder_month(cur, 'all', args.user_rabbit,
                                      args.password_rabbit)
            send_messages_coder_month(cur, 'female', args.user_rabbit,
                                      args.password_rabbit)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
