#!/usr/bin/python3

'''Processing contest messages.'''

import argparse
import logging
import os
import sys
from typing import Callable
import pika
import contests_callback
import rabbitmq_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def process_queue(
        *,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        exchange_name: str,
        queue_name: str,
        routing_key: str,
        callback: Callable[
            [
                pika.adapters.blocking_connection.BlockingChannel,
                pika.spec.Basic.Deliver,
                pika.spec.BasicProperties,
                bytes,
            ], None],
) -> None:
    '''Receive contest messages from a queue'''
    channel.exchange_declare(exchange=exchange_name,
                             durable=True,
                             exchange_type='direct')
    channel.queue_declare(queue=queue_name,
                          durable=True,
                          exclusive=False)
    channel.queue_bind(
        exchange=exchange_name,
        queue=queue_name,
        routing_key=routing_key)
    logging.info('waiting for the messages')
    channel.basic_consume(
        queue=queue_name,
        on_message_callback=callback,
        auto_ack=False)
    try:
        channel.start_consuming()
    except KeyboardInterrupt:
        channel.stop_consuming()


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    parser.add_argument('--api-token', type=str, help='omegaup api token')
    parser.add_argument('--url',
                        type=str,
                        help='omegaup api URL',
                        default='https://omegaup.com')

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with rabbitmq_connection.connect(args) as channel:
            callback = contests_callback.contests_callback(dbconn.conn,
                                                           args.api_token,
                                                           args.url)
            process_queue(channel=channel,
                          exchange_name='certificates',
                          queue_name='contest',
                          routing_key='ContestQueue',
                          callback=callback)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
