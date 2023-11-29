#!/usr/bin/python3

'''test contest_callback module.'''

import dataclasses
import datetime
import json
import os
import random
import string
import sys
import time

from typing import List
import omegaup.api

import database.contest
import contest_callback
import test_credentials
import rabbitmq_connection
import test_constants


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position


def test_insert_contest_certificate() -> None:
    '''Test get contest contestants'''

    client = omegaup.api.Client(api_token=test_constants.API_TOKEN,
                                url=test_constants.OMEGAUP_API_ENDPOINT)
    current_time = datetime.datetime.now()
    future_time = current_time + datetime.timedelta(hours=5)
    alias = ''.join(random.choices(string.digits, k=8))

    # Creating a contest and then adding some users
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

    usernames: List[str] = []
    for number in range(5):
        user = f'test_user_{number}'
        client.contest.addUser(contest_alias=alias, usernameOrEmail=user)
        usernames.append(user)

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

    with dbconn.cursor(buffered=True, dictionary=True) as cur:
        cur.execute(
            '''
            SELECT
                c.contest_id,
                p.scoreboard_url
            FROM
                Contests c
            INNER JOIN
                Problemsets p
            ON
                p.problemset_id = c.problemset_id
            WHERE
                alias = %s;
            ''', (alias,))
        result = cur.fetchone()
    contest_id = result['contest_id']
    scoreboard_url = result['scoreboard_url']
    ranking = []
    scoreboard = client.contest.scoreboard(
        contest_alias=alias,
        token=scoreboard_url)
    for position in scoreboard.ranking:
        ranking.append(database.contest.Ranking(
            username=position.username,
            place=f'{position.place}')._asdict())
    with rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
            for_testing=False
    ) as channel:
        callback = contest_callback.ContestsCallback(
            dbconn=dbconn.conn,
            for_testing=False
        )
        body = contest_callback.ContestCertificate(
            contest_id=contest_id,
            # setting a default value
            certificate_cutoff=3,
            alias=alias,
            scoreboard_url=scoreboard_url,
            ranking=ranking,
        )
        callback(
            _channel=channel,
            _method=None,
            _properties=None,
            body=json.dumps(dataclasses.asdict(body)).encode('utf-8')
        )

    with dbconn.cursor(buffered=True, dictionary=True) as cur:
        cur.execute(
            '''
            SELECT
                i.username,
                c.contest_place
            FROM
                Certificates c
            INNER JOIN
                Identities i
            ON
                i.identity_id = c.identity_id
            INNER JOIN
                Contests cs
            ON
                cs.contest_id = c.contest_id
            WHERE
                cs.alias = %s;
            ''', (alias,))
        certificates = cur.fetchall()
        assert certificates

    for certificate in certificates:
        assert certificate['username'] in usernames
        # At this moment, there are no submissions for the contest, so all the
        # participants got the first place
        assert certificate['contest_place'] == 1
