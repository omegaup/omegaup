#!/usr/bin/python3

'''test verification_code module.'''

import os

import argparse
import logging
import sys
from unittest import mock
import rabbitmq_connection
import contests_callback
import client_contest
import mysql.connector
import mysql.connector.cursor
import pika
import test_constants
import send_messages_contest_queue

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
        callback = contests_callback.ContestsCallback(self.dbconn,
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

    parser.add_argument('--api-token', default=test_constants.API_TOKEN)
    parser.add_argument('--url', default=test_constants.OMEGAUP_API_ENDPOINT)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(args) as channel:
        send_messages_contest_queue.send_contest(
            cur,
            channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT)
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


@mock.patch('contests_callback.generate_code',
            side_effect=iter(['XMCF384X8X', 'XMCF384X8C', 'XMCF384X8F',
                              'XMCF384X8M']), autospec=True)
def test_client_contest_with_mocked_codes(mock_gen_code: mock.Mock) -> None:
    '''Test client contest queue when a code already exists'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    parser.add_argument('--api-token', default=test_constants.API_TOKEN)
    parser.add_argument('--url', default=test_constants.OMEGAUP_API_ENDPOINT)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(args) as channel:
        send_messages_contest_queue.send_contest(
            cur,
            channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT)
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

        mock_gen_code.assert_called()
        assert mock_gen_code.call_args_list == [(), (), (), ()]


@mock.patch('contests_callback.generate_code',
            side_effect=iter(['XMCF384X8X', 'XMCF384X8C', 'XMCF384X8F',
                              'XMCF384X8C', 'XMCF384X8X', 'XMCF384X8C',
                              'XMCF384X8C', 'XMCF384X8X', 'XMCF384X8C',
                              'XMCF384X8C', 'XMCF384X8X', 'XMCF384X8C',
                              'XMCF384X8X', 'XMCF384X8M']), autospec=True)
def test_client_contest_with_duplicated_codes(
        mock_gen_code: mock.Mock
) -> None:
    '''Test client contest queue when a code already exists'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    parser.add_argument('--api-token', default=test_constants.API_TOKEN)
    parser.add_argument('--url', default=test_constants.OMEGAUP_API_ENDPOINT)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(args) as channel:
        send_messages_contest_queue.send_contest(
            cur,
            channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT)
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

        mock_gen_code.assert_called()
        assert mock_gen_code.call_args_list == [(), (), (), (), (), (), (),
                                                (), (), (), (), (), (), ()]
