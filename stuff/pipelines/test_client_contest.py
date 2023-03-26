#!/usr/bin/python3

'''test client contest module.'''

import os
import sys

# pylint indicates pytest_mock should be placed before "import mysql.connector"
import contest_callback
import omegaup.api
import pika
import producer_contest
import pytest
import pytest_mock
import rabbitmq_client
import rabbitmq_connection
import test_constants
import test_credentials

import mysql.connector
import mysql.connector.cursor

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


def test_client_contest() -> None:
    '''Basic test for client contest queue.'''
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=test_credentials.MYSQL_USER,
            password=test_credentials.MYSQL_PASSWORD,
            host=test_credentials.MYSQL_HOST,
            database=test_credentials.MYSQL_DATABASE,
            port=test_credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
    ) as channel:
        rabbitmq_connection.initialize_rabbitmq(queue='contest',
                                                exchange='certificates',
                                                routing_key='ContestQueue',
                                                channel=channel)
        producer_contest.send_contest_message_to_client(
            cur=cur,
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT)

        client = omegaup.api.Client(
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT,
        )
        callback = ContestsCallbackForTesting(dbconn=dbconn.conn,
                                              client=client)
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


@pytest.mark.skip(reason="Disabled temporarily because it's flaky")
def test_client_contest_with_mocked_codes(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''Test client contest queue when a code already exists'''
    mocker.patch('contest_callback.generate_contest_code',
                 side_effect=iter(['XMCF384X8X', 'XMCF384X8C', 'XMCF384X8F',
                                   'XMCF384X8M']))
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=test_credentials.MYSQL_USER,
            password=test_credentials.MYSQL_PASSWORD,
            host=test_credentials.MYSQL_HOST,
            database=test_credentials.MYSQL_DATABASE,
            port=test_credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
    ) as channel:
        rabbitmq_connection.initialize_rabbitmq(queue='contest',
                                                exchange='certificates',
                                                routing_key='ContestQueue',
                                                channel=channel)
        producer_contest.send_contest_message_to_client(
            cur=cur,
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT)
        client = omegaup.api.Client(
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT,
        )
        callback = ContestsCallbackForTesting(dbconn=dbconn.conn,
                                              client=client)
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0
        spy = mocker.spy(contest_callback, 'generate_contest_code')
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest',
            routing_key='ContestQueue',
            callback=callback)
        assert spy.call_count == 4


@pytest.mark.skip(reason="Disabled temporarily because it's flaky")
def test_client_contest_with_duplicated_codes(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''Test client contest queue when a code already exists'''
    mocker.patch('contest_callback.generate_contest_code',
                 side_effect=iter(['XMCF384X8X', 'XMCF384X8C', 'XMCF384X8F',
                                   'XMCF384X8C', 'XMDF384X8A', 'XMCF384X8D',
                                   'XMCF384X8E', 'XMCF384X8L', 'XMCF385X8E',
                                   'XMCF384X8P', 'XMCF384X5F', 'XNCF384X8F',
                                   'XMCF384X89', 'XMCF384X8M']))
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=test_credentials.MYSQL_USER,
            password=test_credentials.MYSQL_PASSWORD,
            host=test_credentials.MYSQL_HOST,
            database=test_credentials.MYSQL_DATABASE,
            port=test_credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
    ) as channel:
        rabbitmq_connection.initialize_rabbitmq(queue='contest',
                                                exchange='certificates',
                                                routing_key='ContestQueue',
                                                channel=channel)
        producer_contest.send_contest_message_to_client(
            cur=cur,
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT)
        client = omegaup.api.Client(
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT,
        )
        callback = ContestsCallbackForTesting(dbconn=dbconn.conn,
                                              client=client)
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0
        spy = mocker.spy(contest_callback, 'generate_contest_code')
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest',
            routing_key='ContestQueue',
            callback=callback)

        assert spy.call_count > 4
