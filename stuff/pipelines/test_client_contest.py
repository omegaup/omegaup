#!/usr/bin/python3

'''test verification_code module.'''

import os

import argparse
import logging
import sys
import rabbitmq_connection
import contests_callback
import client_contest
import mysql.connector
import mysql.connector.cursor
import pika
from pytest_mock import MockerFixture
import test_credentials

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


class ContestsCallbackForTesting:
    '''Contests callback'''
    def __init__(self,
                 dbconn: mysql.connector.MySQLConnection,
                 api_token: str,
                 url: str):
        '''Contructor for contest callback for testing'''
        self.dbconn = dbconn
        self.api_token = api_token
        self.url = url

    def __call__(self,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 method: pika.spec.Basic.Deliver,
                 properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Function to call the original callback'''
        callback = contests_callback.contests_callback(self.dbconn,
                                                       self.api_token,
                                                       self.url)
        callback(channel, method, properties, body)
        channel.close()


def test_client_contest() -> None:
    '''Basic test for client contest queue.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    parser.add_argument('--api-token', default=test_credentials.API_TOKEN)
    parser.add_argument('--url', default=test_credentials.OMEGAUP_API_ENDPOINT)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)

    os.system('python3 send_messages_contest_queue.py')
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(args) as channel:
        callback = ContestsCallbackForTesting(dbconn.conn,
                                              args.api_token,
                                              args.url)
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0

        client_contest.process_queue(
            channel=channel,
            exchange_name='certificates',
            queue_name='contest',
            routing_key='ContestQueue',
            callback=callback)
        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] > 0


def test_client_contest_with_duplicated_codes(mocker: MockerFixture) -> None:
    '''Test client contest queue when a code already exists'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    parser.add_argument('--api-token', default=test_credentials.API_TOKEN)
    parser.add_argument('--url', default=test_credentials.OMEGAUP_API_ENDPOINT)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    os.system('python3 send_messages_contest_queue.py')
    with rabbitmq_connection.connect(args) as channel:
        callback = ContestsCallbackForTesting(dbconn.conn,
                                              args.api_token,
                                              args.url)
        codes = ['XMCF384X8X', 'XMCF384X8X', 'XMCF384X8X', 'XMCF384X8M']
        mocker.patch('verification_code.generate_code',
                     side_effect=iter(codes))
        client_contest.process_queue(
            channel=channel,
            exchange_name='certificates',
            queue_name='contest',
            routing_key='ContestQueue',
            callback=callback)
