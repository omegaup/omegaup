#!/usr/bin/python3

'''Implementation of a client class to be using with the tests.'''

import argparse
import logging
import os
import sys
import json
import random
import MySQLdb
import MySQLdb.cursors
import pika
import rabbitmq_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

class BasicClient:
    def __init__(self, queue, exchange, routing_key) -> None:
        self.message = ''
        self.queue = queue
        self.exchange = exchange
        self.routing_key = routing_key
    def receive_messages(
            self,
            cur: MySQLdb.cursors.BaseCursor,
            dbconn: MySQLdb.connections.Connection,
            channel: pika.adapters.blocking_connection.BlockingChannel,
        ) -> None:
        '''Receive messages from a queue'''

        channel.exchange_declare(exchange=self.exchange,
                                 durable=True,
                                 exchange_type='direct')
        channel.queue_bind(
            exchange=self.exchange,
            queue=self.queue,
            routing_key=self.routing_key)
        def callback(channel: pika.adapters.blocking_connection.BlockingChannel,
                    method: pika.spec.Basic.Deliver,
                    properties: pika.spec.BasicProperties,
                    # pylint: disable=unused-argument,
                    body: bytes) -> None:
            data = json.loads(body.decode())
            self.message = data
            channel.close()
        channel.basic_consume(
            queue=self.queue,
            on_message_callback=callback,
            auto_ack=True)
        channel.start_consuming()


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
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur, \
            rabbitmq_connection.connect(args) as channel:
            client = BasicClient('coder_month',
                                 'certificates',
                                 'CoderOfTheMonthQueue')
            client.receive_messages(cur, dbconn, channel)
            print(client.message)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()