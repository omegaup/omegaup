#!/usr/bin/python3

'''Processing contest messages.'''

import argparse
import logging
import os
import sys
import MySQLdb
import MySQLdb.cursors
import pika

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def receive_contest_messages(
        cur: MySQLdb.cursors.BaseCursor,
        # pylint: disable=unused-argument,
        rabbit_user: str,
        rabbit_password: str) -> None:
    '''Receive contest messages'''

    credentials = pika.PlainCredentials(rabbit_user, rabbit_password)
    parameters = pika.ConnectionParameters('rabbitmq', 5672, '/', credentials)
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    channel.exchange_declare(exchange='logs_exchange', exchange_type='direct')
    result = channel.queue_declare(queue='', exclusive=True)
    queue_name = result.method.queue
    assert queue_name is not None
    channel.queue_bind(
        exchange='logs_exchange',
        queue=queue_name,
        routing_key="ContestQueue")
    print('[*] waiting for the messages')

    def callback(channel: pika.adapters.blocking_connection.BlockingChannel,
                 method: pika.spec.Basic.Deliver,
                 properties: pika.spec.BasicProperties,
                 # pylint: disable=unused-argument,
                 body: bytes) -> None:
        print('[x] Alarm:::: %r' % body)

    channel.basic_consume(
        queue=queue_name,
        on_message_callback=callback,
        auto_ack=True)
    channel.start_consuming()


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    parser.add_argument('--user_rabbit')
    parser.add_argument('--password_rabbit')
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    print(args.user_rabbit)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            receive_contest_messages(
                cur, args.user_rabbit,
                args.password_rabbit)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
