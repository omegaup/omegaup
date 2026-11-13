#!/usr/bin/python3

'''test client contest module.'''

import dataclasses
import datetime
import json
import os
import random
import string
import sys
import time

from typing import Collection, List, Optional

# pylint indicates pytest_mock should be placed before "import mysql.connector"
import contest_callback
import pika
import pytest_mock
import mysql.connector
import mysql.connector.cursor
import omegaup.api

import database.contest
import rabbitmq_connection
import rabbitmq_client
import test_constants
import test_credentials
import verification_code

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


def _create_finished_contest_with_users(
        client: omegaup.api.Client,
        num_users: int = 3,
) -> str:
    '''Creates an isolated, already-finished contest with its own users.

    Returns the contest alias. Each test creates its own contest so it does
    not depend on bootstrap data or on the state left behind by other tests.
    '''
    alias = 'client_test_' + ''.join(random.choices(string.digits, k=8))
    current_time = datetime.datetime.now()
    past_time = current_time - datetime.timedelta(hours=5)
    client.contest.create(
        title=alias,
        alias=alias,
        description='Test contest for client_contest',
        start_time=time.mktime(past_time.timetuple()),
        finish_time=time.mktime(current_time.timetuple()),
        window_length=0,
        scoreboard=100,
        points_decay_factor=0,
        score_mode='partial',
        submissions_gap=1200,
        penalty=0,
        feedback='detailed',
        penalty_type='contest_start',
        languages='py2,py3',
        penalty_calc_policy='sum',
        admission_mode='private',
        show_scoreboard_after=True,
    )
    for number in range(num_users):
        client.contest.addUser(
            contest_alias=alias,
            usernameOrEmail=f'test_user_{number}')
    return alias


def _new_verification_codes(
        cur: mysql.connector.cursor.MySQLCursorDict,
        count: int,
) -> List[str]:
    '''Returns distinct codes that do not exist yet in Certificates.'''
    codes: List[str] = []
    while len(codes) < count:
        code = verification_code.generate_code()
        if code in codes:
            continue
        cur.execute(
            'SELECT 1 FROM `Certificates` WHERE verification_code = %s;',
            (code,))
        if cur.fetchone() is None:
            codes.append(code)
    return codes


def _build_contest_message(
        cur: mysql.connector.cursor.MySQLCursorDict,
        client: omegaup.api.Client,
        alias: str,
) -> str:
    '''Builds the JSON message for a specific contest from its scoreboard.'''
    cur.execute(
        '''
        SELECT
            c.contest_id,
            c.certificate_cutoff,
            p.scoreboard_url
        FROM
            Contests c
        INNER JOIN
            Problemsets p
        ON
            p.problemset_id = c.problemset_id
        WHERE
            c.alias = %s;
        ''', (alias,))
    row = cur.fetchone()
    scoreboard = client.contest.scoreboard(
        contest_alias=alias,
        token=row['scoreboard_url'])
    ranking = [
        database.contest.Ranking(
            username=position.username,
            place=f'{position.place}')._asdict()
        for position in scoreboard.ranking
    ]
    message = contest_callback.ContestCertificate(
        contest_id=row['contest_id'],
        certificate_cutoff=(
            row['certificate_cutoff']
            if row['certificate_cutoff'] is not None else 3),
        alias=alias,
        scoreboard_url=row['scoreboard_url'],
        ranking=ranking,
    )
    return json.dumps(dataclasses.asdict(message))


def _assert_certificates(
        cur: mysql.connector.cursor.MySQLCursorDict,
        message: str,
        allowed_codes: Optional[Collection[str]] = None,
        rejected_codes: Collection[str] = (),
) -> List[str]:
    '''Checks what was stored for the contest of the published message.'''
    data = json.loads(message)
    cutoff = data['certificate_cutoff']
    expected_places = {}
    for entry in data['ranking']:
        place = int(entry['place'])
        expected_places[entry['username']] = (
            place if cutoff and place and place <= cutoff else None)
    assert expected_places

    cur.execute(
        '''
        SELECT
            i.username,
            i.user_id,
            c.verification_code,
            c.contest_place
        FROM
            Certificates c
        INNER JOIN
            Identities i
        ON
            i.identity_id = c.identity_id
        WHERE
            c.contest_id = %s;
        ''', (data['contest_id'],))
    rows = list(cur.fetchall())
    codes = [row['verification_code'] for row in rows]

    assert len(rows) == len(expected_places)
    assert {
        row['username']: row['contest_place'] for row in rows
    } == expected_places
    assert len(set(codes)) == len(codes)
    if allowed_codes is not None:
        assert set(codes) <= set(allowed_codes)
    assert not set(codes) & set(rejected_codes)

    for row in rows:
        cur.execute(
            'SELECT COUNT(*) AS count FROM `Notifications` '
            'WHERE user_id = %s AND contents LIKE %s;',
            (row['user_id'],
             f"%/certificates/mine/#{row['verification_code']}%"))
        assert cur.fetchone()['count'] == 1
    for code in rejected_codes:
        cur.execute(
            'SELECT COUNT(*) AS count FROM `Notifications` '
            'WHERE contents LIKE %s;',
            (f'%/certificates/mine/#{code}%',))
        assert cur.fetchone()['count'] == 0

    cur.execute(
        'SELECT certificates_status FROM `Contests` WHERE contest_id = %s;',
        (data['contest_id'],))
    assert cur.fetchone()['certificates_status'] == 'generated'
    return codes


def test_client_contest() -> None:
    '''Basic test for client contest queue.

    Creates its own isolated contest, publishes a single message for it and
    verifies that consuming that message inserts certificates only for that
    contest. The test is self-contained and idempotent.
    '''
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

    client = omegaup.api.Client(
        api_token=test_constants.API_TOKEN,
        url=test_constants.OMEGAUP_API_ENDPOINT,
    )
    alias = _create_finished_contest_with_users(client)

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='contest-test',
            exchange='certificates',
            routing_key='ContestQueue-test',
            channel=channel)
        channel.queue_purge(queue='contest-test')

        message = _build_contest_message(cur, client, alias)
        channel.basic_publish(
            exchange='certificates',
            routing_key='ContestQueue-test',
            body=message.encode())

        contest_id = json.loads(message)['contest_id']

        cur.execute(
            'SELECT COUNT(*) AS count FROM `Certificates` '
            'WHERE contest_id = %s;', (contest_id,))
        assert cur.fetchone()['count'] == 0

        callback = ContestsCallbackForTesting(dbconn=dbconn.conn)
        rabbitmq_client.receive_messages(
            queue='contest-test',
            exchange='certificates',
            routing_key='ContestQueue-test',
            channel=channel,
            callback=callback,
            stop_after_message=True)

        _assert_certificates(cur, message)


def test_client_contest_with_mocked_codes(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''One verification code is generated per contestant (no collisions).'''
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
    client = omegaup.api.Client(
        api_token=test_constants.API_TOKEN,
        url=test_constants.OMEGAUP_API_ENDPOINT,
    )
    alias = _create_finished_contest_with_users(client, num_users=4)

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='contest-test',
            exchange='certificates',
            routing_key='ContestQueue-test',
            channel=channel)
        channel.queue_purge(queue='contest-test')
        codes = _new_verification_codes(cur, 4)
        mocker.patch('contest_callback.generate_contest_code',
                     side_effect=iter(codes))

        message = _build_contest_message(cur, client, alias)
        channel.basic_publish(
            exchange='certificates',
            routing_key='ContestQueue-test',
            body=message.encode())

        callback = ContestsCallbackForTesting(dbconn=dbconn.conn)
        spy = mocker.spy(contest_callback, 'generate_contest_code')
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest-test',
            routing_key='ContestQueue-test',
            callback=callback,
            stop_after_message=True)
        assert spy.call_count == 4
        _assert_certificates(cur, message, allowed_codes=codes)


def test_client_contest_with_duplicated_codes(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''A collision in the verification code triggers a regeneration retry.'''
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
    client = omegaup.api.Client(
        api_token=test_constants.API_TOKEN,
        url=test_constants.OMEGAUP_API_ENDPOINT,
    )
    alias = _create_finished_contest_with_users(client, num_users=4)

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='contest-test',
            exchange='certificates',
            routing_key='ContestQueue-test',
            channel=channel)
        channel.queue_purge(queue='contest-test')
        codes = _new_verification_codes(cur, 7)
        first_batch = codes[:3] + [codes[1]]
        second_batch = codes[3:]
        mocker.patch('contest_callback.generate_contest_code',
                     side_effect=iter(first_batch + second_batch))

        message = _build_contest_message(cur, client, alias)
        channel.basic_publish(
            exchange='certificates',
            routing_key='ContestQueue-test',
            body=message.encode())

        callback = ContestsCallbackForTesting(dbconn=dbconn.conn)
        spy = mocker.spy(contest_callback, 'generate_contest_code')
        rabbitmq_client.receive_messages(
            channel=channel,
            exchange='certificates',
            queue='contest-test',
            routing_key='ContestQueue-test',
            callback=callback,
            stop_after_message=True)

        assert spy.call_count == 8
        _assert_certificates(
            cur, message,
            allowed_codes=second_batch,
            rejected_codes=codes[:3])
