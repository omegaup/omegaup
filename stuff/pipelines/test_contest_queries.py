#!/usr/bin/env python3

'''test database.contest module.'''

import datetime
import os
import random
import string
import sys
import time

import omegaup.api

import test_credentials
import database.contest
import test_constants


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position


def test_get_contests_information() -> None:
    '''Test get contest contestants'''

    client = omegaup.api.Client(api_token=test_constants.API_TOKEN,
                                url=test_constants.OMEGAUP_API_ENDPOINT)
    current_time = datetime.datetime.now()
    past_time = current_time - datetime.timedelta(hours=5)
    alias = ''.join(random.choices(string.digits, k=8))
    client.contest.create(
        title=alias,
        alias=alias,
        description='Test contest',
        start_time=time.mktime(past_time.timetuple()),
        finish_time=time.mktime(current_time.timetuple()),
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
        contests = database.contest.get_contests(
            cur=cur,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
            certificates=[],
            client=client,
        )

        assert alias in [contest.alias for contest in contests]
