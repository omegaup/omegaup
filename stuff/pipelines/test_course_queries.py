#!/usr/bin/env python3

'''test database.course module.'''

import datetime
import os
import random
import string
import sys
import time

import omegaup.api

import test_credentials
import database.course
import test_constants


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position


def test_get_courses_information() -> None:
    '''Test get course participants'''

    client = omegaup.api.Client(api_token=test_constants.API_TOKEN,
                                url=test_constants.OMEGAUP_API_ENDPOINT)
    current_time = datetime.datetime.now()
    past_time = current_time - datetime.timedelta(hours=5)
    course_alias = ''.join(random.choices(string.digits, k=8))
    client.course.create(
        name=course_alias,
        alias=course_alias,
        description='Test course',
        start_time=int(time.mktime(past_time.timetuple())),
        finish_time=int(time.mktime(current_time.timetuple())),
        objective='Testing',
        level='intermediate',
        show_scoreboard=True,
        requests_user_information='no',
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
        courses = database.course.get_courses(
            cur=cur,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
            certificates=[],
            client=client,
        )

        assert course_alias in [course.alias for course in courses]
