#!/usr/bin/python3

'''Client coder of month'''


import argparse
import logging
import os
import sys
import json
from typing import Optional
import rabbitmq_database
import rabbitmq_client
import mysql.connector
import mysql.connector.cursor
import pika
import rabbitmq_connection
from rabbitmq_connection import initialize_rabbitmq


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def client(
        channel: pika.adapters.blocking_connection.BlockingChannel,
        cur: Optional[mysql.connector.cursor.MySQLCursorDict] = None,
        dbconn: Optional[mysql.connector.MySQLConnection] = None) -> None:  
    '''Client function'''
    

    def callback(channel: pika.adapters.blocking_connection.BlockingChannel,
             method: pika.spec.Basic.Deliver,
             properties: pika.spec.BasicProperties,
             # pylint: disable=unused-argument,
             body: bytes) -> None:
        '''Callback function'''
        data = json.loads(body.decode())
        rabbitmq_database.insert_coder_of_the_month(data, cur, dbconn)
        if cur is None or dbconn is None:
            channel.close()

    rabbitmq_client.receive_messages('coder_month', 'certificates', 
                                     'CoderOfTheMonthQueue', channel, callback)


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
            initialize_rabbitmq('coder_month',
                                'certificates',
                                'CoderOfTheMonthQueue',
                                channel)
            client(channel, cur, dbconn.conn)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
