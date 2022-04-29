#!/usr/bin/python3

'''Send message to coder_month_client'''


import argparse
import logging
import os
import sys
import json
from typing import Optional
from rabbitmq_database import get_coder_of_the_month
from rabbitmq_producer import RabbitmqProducer
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


def send_message_client(
        channel: pika.adapters.blocking_connection.BlockingChannel,
        cur: Optional[mysql.connector.cursor.MySQLCursorDict] = None
) -> None:
    '''Function to send message to client'''
    coder_month_producer = RabbitmqProducer('coder_month',
                                            'certificates',
                                            'CoderOfTheMonthQueue',
                                            channel)
    data_all = get_coder_of_the_month('all', cur)
    message_all = json.dumps(data_all)
    data_female = get_coder_of_the_month('female', cur)
    message_female = json.dumps(data_female)
    coder_month_producer.send_message(message_all)
    coder_month_producer.send_message(message_female)


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
            rabbitmq_connection.connect(username='omegaup',
                                        password='omegaup',
                                        host='rabbitmq') as channel:
            send_message_client(channel, cur)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
