#!/usr/bin/python3

'''test contest_callback module.'''

import datetime
import os
import random
import string
import sys
import time

import omegaup.api
import pytest_mock

import contest_callback
import rabbitmq_connection
import credentials
import test_constants


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position


def test_insert_contest_certificate(mocker: pytest_mock.MockerFixture) -> None:
    '''Test get contest contestants'''

    client = omegaup.api.Client(api_token=test_constants.API_TOKEN,
                                url=test_constants.OMEGAUP_API_ENDPOINT)
    current_time = datetime.datetime.now()
    future_time = current_time + datetime.timedelta(hours=5)
    alias = ''.join(random.choices(string.digits, k=8))
    client.contest.create(
        title=alias,
        alias=alias,
        description='Test contest',
        start_time=time.mktime(current_time.timetuple()),
        finish_time=time.mktime(future_time.timetuple()),
        window_length=0,
        scoreboard=100,
        points_decay_factor=0,
        partial_score=True,
        submissions_gap=1200,
        penalty=0,
        feedback='detailed',
        penalty_type='contest_start',
        languages='py2,py3',
        penalty_calc_policy='sum',
        admission_mode='private',
        show_scoreboard_after=True,
    )
    contest_scoreboard = [{
        'classname': 'user-rank-unranked',
        'country': 'xx',
        'is_invited': True,
        'name': 'Test User',
        'place': 1,
        'problems': [{
            'alias': 'sumas',
            'penalty': 0,
            'points': 100,
            'runs': 1
        }],
        'total': {'points': 100, 'penalty': 0},
        'username': 'testUser'
    }]
    mocker.patch('contest_callback.get_contest_scoreboard',
                 return_value=contest_scoreboard)

    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=credentials.MYSQL_USER,
            password=credentials.MYSQL_PASSWORD,
            host=credentials.MYSQL_HOST,
            database=credentials.MYSQL_DATABASE,
            port=credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )
    with rabbitmq_connection.connect(
            username=credentials.OMEGAUP_USERNAME,
            password=credentials.OMEGAUP_PASSWORD,
            host=credentials.RABBITMQ_HOST
    ) as channel:
        callback = contest_callback.ContestsCallback(
            dbconn=dbconn.conn,
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT
        )
        body = '''
        {"contest_id": 1, "certificate_cutoff": 3, "alias": "test", \
        "scoreboard_url": "abcedef"}
        '''
        callback(
            _channel=channel,
            _method=None,
            _properties=None,
            body=body.encode()
        )

    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=credentials.MYSQL_USER,
            password=credentials.MYSQL_PASSWORD,
            host=credentials.MYSQL_HOST,
            database=credentials.MYSQL_DATABASE,
            port=credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )

    with dbconn.cursor(buffered=True, dictionary=True) as cur:
        cur.execute(
            '''
            SELECT COUNT(*) AS count FROM Certificates WHERE contest_id = 1;
            ''')
        count = cur.fetchone()
        assert count['count'] == 1
