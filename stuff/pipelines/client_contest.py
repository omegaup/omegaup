#!/usr/bin/python3

'''
Processing contest messages for certificates. Once  the connection with
rabbitmq is established, an exchange is created and then, the queue is created
and declarated, so the queue is binded to the specified exchange. Finally, the
queue is consumed for the consumer_tag to the consumer callback.
'''

import argparse
import logging
import os
import sys

import omegaup.api

import contest_callback
import rabbitmq_connection
import rabbitmq_client
import pika

import mysql.connector

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


class ContestsCallbackForTesting:
    '''Contests callback'''
    def __init__(self,
                 *,
                 dbconn: mysql.connector.MySQLConnection,
                 client: omegaup.api.Client):
        '''Contructor for contest callback for testing'''
        self.dbconn = dbconn
        self.client = client

    def __call__(self,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 method: pika.spec.Basic.Deliver,
                 properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Function to call the original callback'''
        callback = contest_callback.ContestsCallback(dbconn=self.dbconn,
                                                     client=self.client)
        callback(channel, method, properties, body)
        channel.close()


def main() -> None:
    '''
    Main entrypoint for the client contest.

    When API token and URL are given, it is possible to process the messages.
    '''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    parser.add_argument('--api-token', type=str, help='omegaup api token')
    parser.add_argument('--url',
                        type=str,
                        help='omegaup api URL',
                        default='https://omegaup.com')
    parser.add_argument('--test',
                        type=bool,
                        help='it determinates if the client is for a test',
                        default=False)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    try:
        with rabbitmq_connection.connect(
                username=args.rabbitmq_username,
                password=args.rabbitmq_password,
                host=args.rabbitmq_host
        ) as channel:
            client = omegaup.api.Client(api_token=args.api_token, url=args.url)
            if args.test:
                callback_test = ContestsCallbackForTesting(
                    dbconn=dbconn.conn,
                    client=client
                )
                rabbitmq_client.receive_messages(
                    queue='contest',
                    exchange='certificates',
                    routing_key='ContestQueue',
                    channel=channel,
                    callback=callback_test
                )
            else:
                callback = contest_callback.ContestsCallback(
                    dbconn=dbconn.conn,
                    client=client,
                )
                rabbitmq_client.receive_messages(
                    queue='contest',
                    exchange='certificates',
                    routing_key='ContestQueue',
                    channel=channel,
                    callback=callback
                )
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
