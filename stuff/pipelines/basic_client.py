#!/usr/bin/python3

'''Implementation of a client class to be using with the tests.'''

import argparse
import logging
import os
import sys
import json
from typing import Callable, Dict
import pika
import rabbitmq_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


class ClientCallback():
    '''Client callback'''
    def __init__(self) -> None:
        self.message: Dict[str, str] = {}

    def __call__(
            self,
            channel: pika.adapters.blocking_connection.BlockingChannel,
            method: pika.spec.Basic.Deliver,
            properties: pika.spec.BasicProperties,
            # pylint: disable=unused-argument,
            body: bytes) -> None:
        data = json.loads(body.decode())
        self.message = data
        channel.close()


def receive_messages(
        queue: str, exchange: str, routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        callback: Callable[
            [
                pika.adapters.blocking_connection.BlockingChannel,
                pika.spec.Basic.Deliver,
                pika.spec.BasicProperties,
                bytes,
            ], None]) -> None:
    '''Receive messages from a queue'''

    channel.exchange_declare(exchange=exchange,
                             durable=True,
                             exchange_type='direct')
    channel.queue_bind(exchange=exchange,
                       queue=queue,
                       routing_key=routing_key)

    channel.basic_consume(queue=queue,
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
        with rabbitmq_connection.connect(args) as channel:
            callback = ClientCallback()
            receive_messages('coder_month',
                             'certificates',
                             'CoderOfTheMonthQueue',
                             channel,
                             callback)
            print(callback.message)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
