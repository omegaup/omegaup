#!/usr/bin/python3

'''test client contest module.'''

import os
import sys

# pylint indicates pytest_mock should be placed before "import mysql.connector"
import contest_callback
import database
import pika
import producer_contest
import pytest
import pytest_mock
import mysql.connector
import mysql.connector.cursor
import omegaup.api

import rabbitmq_connection
import rabbitmq_client
import test_constants
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
                 *,
                 dbconn: mysql.connector.MySQLConnection):
        '''Contructor for contest callback for testing'''
        self.dbconn = dbconn

    def __call__(self,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 method: pika.spec.Basic.Deliver,
                 properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Function to call the original callback'''
        callback = contest_callback.ContestsCallback(dbconn=self.dbconn)
        callback(channel, method, properties, body)


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
            host=test_credentials.RABBITMQ_HOST) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='contest-test',
            exchange='certificates',
            routing_key='ContestTestQueue',
            channel=channel)
        channel.queue_purge(queue='contest-test')
        cur.execute('''
            SELECT
                c.contest_id,
                c.finish_time,
                c.certificates_status,
                p.scoreboard_url
            FROM Contests c
            INNER JOIN Problemsets p
                ON c.problemset_id = p.problemset_id
            WHERE p.scoreboard_url IS NOT NULL
            ORDER BY c.contest_id
            LIMIT 1;
        ''')
        contest = cur.fetchone()
        if contest is None:
            pytest.skip('No contest with a scoreboard is available')
        assert contest is not None
        contest_id = contest['contest_id']
        original_finish_time = contest['finish_time']
        original_certificates_status = contest['certificates_status']
        try:
            cur.execute('''
                UPDATE Contests
                SET finish_time = NOW() - INTERVAL 1 DAY,
                    certificates_status = 'uninitiated'
                WHERE contest_id = %s;
            ''', (contest_id,))
            dbconn.conn.commit()
            cur.execute('TRUNCATE TABLE `Certificates`;')
            dbconn.conn.commit()
            client = omegaup.api.Client(
                api_token=test_constants.API_TOKEN,
                url=test_constants.OMEGAUP_API_ENDPOINT,
            )
            producer_contest.send_contest_message_to_client(
                cur=cur,
                channel=channel,
                date_lower_limit=test_constants.DATE_LOWER_LIMIT,
                date_upper_limit=test_constants.DATE_UPPER_LIMIT,
                client=client,
                queue='contest-test',
                routing_key='ContestTestQueue')
            callback = ContestsCallbackForTesting(
                dbconn=dbconn.conn)
            cur.execute(
                'SELECT COUNT(*) AS count FROM `Certificates`;')
            count = cur.fetchone()
            assert count['count'] == 0
            rabbitmq_client.receive_messages(
                queue='contest-test',
                exchange='certificates',
                routing_key='ContestTestQueue',
                channel=channel,
                callback=callback,
                stop_after_message=True)
            cur.execute(
                'SELECT COUNT(*) AS count FROM `Certificates`;')
            count = cur.fetchone()
            assert count['count'] > 0
        finally:
            cur.execute('''
                UPDATE Contests
                SET finish_time = %s,
                    certificates_status = %s
                WHERE contest_id = %s;
            ''', (
                original_finish_time,
                original_certificates_status,
                contest_id,
            ))
            dbconn.conn.commit()


# @pytest.mark.skip(reason="Disabled temporarily because it's flaky")
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
            host=test_credentials.RABBITMQ_HOST,) as channel:
        rabbitmq_connection.initialize_rabbitmq(queue='contest-test',
                                                exchange='certificates',
                                                routing_key='ContestTestQueue',
                                                channel=channel)
        channel.queue_purge(queue='contest-test')
        client = omegaup.api.Client(
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT,
        )
        mocker.patch(
            'producer_contest.get_contests_from_db',
            return_value=[
                database.contest.ContestCertificate(
                    certificate_cutoff=1,
                    alias='contest1',
                    scoreboard_url='abcdef',
                    contest_id=1,
                    ranking=[
                        database.contest.Ranking(
                            username='user_1',
                            place='1')._asdict(),
                        database.contest.Ranking(
                            username='user_2',
                            place='2')._asdict(),
                        database.contest.Ranking(
                            username='user_3',
                            place='3')._asdict(),
                        database.contest.Ranking(
                            username='user_4',
                            place='4')._asdict(),
                    ],
                ),
            ],
        )
        producer_contest.send_contest_message_to_client(
            cur=cur,
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
            client=client,
            queue='contest-test',
            routing_key='ContestTestQueue')
        callback = ContestsCallbackForTesting(dbconn=dbconn.conn)
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0
        spy = mocker.spy(contest_callback, 'generate_contest_code')
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest-test',
            routing_key='ContestTestQueue',
            callback=callback,
            stop_after_message=True)
        assert spy.call_count == 4


# @pytest.mark.skip(reason="Disabled temporarily because it's flaky")
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
            host=test_credentials.RABBITMQ_HOST,) as channel:
        rabbitmq_connection.initialize_rabbitmq(queue='contest-test',
                                                exchange='certificates',
                                                routing_key='ContestTestQueue',
                                                channel=channel)
        channel.queue_purge(queue='contest-test')
        client = omegaup.api.Client(
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT,
        )
        producer_contest.send_contest_message_to_client(
            cur=cur,
            channel=channel,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
            client=client,
            queue='contest-test',
            routing_key='ContestTestQueue'
        )
        callback = ContestsCallbackForTesting(dbconn=dbconn.conn)
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0
        spy = mocker.spy(contest_callback, 'generate_contest_code')
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest-test',
            routing_key='ContestTestQueue',
            callback=callback,
            stop_after_message=True)

        assert spy.call_count > 4
