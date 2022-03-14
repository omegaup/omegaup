#!/usr/bin/python3

'''test verification_code module.'''

import os

import sys
from pytest_mock import MockerFixture
import rabbitmq_connection
import contests_callback
import rabbitmq_client
import verification_code
import mysql.connector
import mysql.connector.cursor
import pika
import test_constants
import send_messages_contest_queue
import credentials

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def initialize_rabbitmq(
        queue: str,
        exchange: str,
        routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel
) -> None:
    '''initializes the queue and exchange'''
    channel.queue_declare(
        queue=queue, passive=False,
        durable=True, exclusive=False,
        auto_delete=False)
    channel.exchange_declare(
        exchange=exchange,
        auto_delete=False,
        durable=True,
        exchange_type='direct')
    channel.queue_bind(exchange=exchange,
                       queue=queue,
                       routing_key=routing_key)


class ContestsCallbackForTesting:
    '''Contests callback'''
    def __init__(self,
                 *,
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
    args = lib.db.DatabaseConnection(
        user='root',
        password='omegaup',
        host='omegaup',
        database='omegaup',
        mysql_config_file=lib.db.default_config_file_path() or ''
    )
    dbconn = lib.db.connect(args)

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(username=credentials.OMEGAUP_USERNAME,
                                    password=credentials.OMEGAUP_PASSWORD,
                                    host=credentials.RABBITMQ_HOST) as channel:
        initialize_rabbitmq(queue='contest',
                            exchange='certificates',
                            routing_key='ContestQueue',
                            channel=channel)
        send_messages_contest_queue.send_contest(
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
            cur=cur)
        callback = ContestsCallbackForTesting(
            dbconn=dbconn.conn,
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT
        )
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0

        rabbitmq_client.receive_messages(
            queue='contest',
            exchange='certificates',
            routing_key='ContestQueue',
            channel=channel,
            callback=callback)
        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] > 0


def test_client_contest_with_mocked_codes(mocker: MockerFixture) -> None:
    '''Test client contest queue when a code already exists'''
    mocker.patch('contests_callback.generate_code',
                 side_effect=iter(['XMCF384X8X', 'XMCF384X8C', 'XMCF384X8F',
                                   'XMCF384X8M']))
    args = lib.db.DatabaseConnection(
        user='root',
        password='omegaup',
        host='omegaup',
        database='omegaup',
        mysql_config_file=lib.db.default_config_file_path() or ''
    )
    dbconn = lib.db.connect(args)
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(username=credentials.OMEGAUP_USERNAME,
                                    password=credentials.OMEGAUP_PASSWORD,
                                    host=credentials.RABBITMQ_HOST) as channel:
        initialize_rabbitmq(queue='contest',
                            exchange='certificates',
                            routing_key='ContestQueue',
                            channel=channel)
        send_messages_contest_queue.send_contest(
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
            cur=cur)
        callback = ContestsCallbackForTesting(
            dbconn=dbconn.conn,
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT
        )
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest',
            routing_key='ContestQueue',
            callback=callback)
        spy = mocker.spy(verification_code, 'generate_code')
        assert spy.call_count == 4


def test_client_contest_with_duplicated_codes(mocker: MockerFixture) -> None:
    '''Test client contest queue when a code already exists'''
    mocker.patch('contests_callback.generate_code',
                 side_effect=iter(['XMCF384X8X', 'XMCF384X8C', 'XMCF384X8F',
                                   'XMCF384X8C', 'XMCF384X8X', 'XMCF384X8C',
                                   'XMCF384X8C', 'XMCF384X8X', 'XMCF384X8C',
                                   'XMCF384X8C', 'XMCF384X8X', 'XMCF384X8C',
                                   'XMCF384X8X', 'XMCF384X8M']))
    args = lib.db.DatabaseConnection(
        user='root',
        password='omegaup',
        host='omegaup',
        database='omegaup',
        mysql_config_file=lib.db.default_config_file_path() or ''
    )
    dbconn = lib.db.connect(args)
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(username=credentials.OMEGAUP_USERNAME,
                                    password=credentials.OMEGAUP_PASSWORD,
                                    host=credentials.RABBITMQ_HOST) as channel:
        initialize_rabbitmq(queue='contest',
                            exchange='certificates',
                            routing_key='ContestQueue',
                            channel=channel)
        send_messages_contest_queue.send_contest(
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
            cur=cur)
        callback = ContestsCallbackForTesting(
            dbconn=dbconn.conn,
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT
        )
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest',
            routing_key='ContestQueue',
            callback=callback)
        spy = mocker.spy(verification_code, 'generate_code')
        assert spy.call_count > 4
