#!/usr/bin/python3

'''test course_callback module.'''

from ast import alias
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

import course_callback
import rabbitmq_connection
import test_constants
import test_credentials


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position


def test_insert_course_certificate() -> None:
    '''Test get course participants'''

    client = omegaup.api.Client(api_token=test_constants.API_TOKEN,
                                url=test_constants.OMEGAUP_API_ENDPOINT)
    current_time = datetime.datetime.now()
    past_time = current_time - datetime.timedelta(hours=5)
    future_time = current_time + datetime.timedelta(hours=5)
    course_alias = ''.join(random.choices(string.digits, k=8))
    assignment_alias = ''.join(random.choices(string.digits, k=8))

    # Creating a course with an assignment and then adding problem and users
    client.course.create(
        name=course_alias,
        alias=course_alias,
        description='Test course',
        start_time=time.mktime(current_time.timetuple()),
        finish_time=time.mktime(future_time.timetuple()),
        objective='Testing',
        level='intermediate',
        show_scoreboard=1,
        requests_user_information='no',
    )

    client.course.createAssignment(
        name=assignment_alias,
        alias=assignment_alias,
        course_alias=course_alias,
        assignment_type='homework',
        description='Test course assignment',
        start_time=time.mktime(current_time.timetuple()),
        finish_time=time.mktime(future_time.timetuple()),
    )

    assignmentDetails = client.course.assignmentDetails(
        course=course_alias,
        assignment=assignment_alias)

    client.course.addProblem(
        course_alias=course_alias,
        assignment_alias=assignment_alias,
        problem_alias='sumas',
        points=1.0,
    )

    usernames: List[str] = []
    for number in range(3):
        user = f'course_test_user_{number}'
        client.course.addStudent(course_alias=course_alias,
                                 usernameOrEmail=user,
                                 share_user_information=True,
                                 accept_teacher_git_object_id='0',
                                 privacy_git_object_id='0',
                                 statement_type='accept_teacher',
                                 accept_teacher=False)
        client.run.create(
            problemset_id=assignmentDetails.problemset_id,
            contest_alias='', # Fix the API in order to make optional this param
            problem_alias='sumas',
            language='py3',
            source='print(1)',
        )
        usernames.append(user)

    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=test_credentials.MYSQL_USER,
            password=test_credentials.MYSQL_PASSWORD,
            host=test_credentials.MYSQL_HOST,
            database=test_credentials.MYSQL_DATABASE,
            port=test_credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or '',
        )
    )

    with dbconn.cursor(buffered=True, dictionary=True) as cur:
        cur.execute(
            '''
            SELECT
                c.course_id
            FROM
                Courses c
            WHERE
                alias = %s;
            ''', (course_alias,))
        result = cur.fetchone()

    course_id = result['course_id']
    with rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
    ) as channel:
        callback = course_callback.CourseCallback(
            dbconn=dbconn.conn,
            client=client,
        )
        body = course_callback.CourseCertificate(
            course_id=course_id,
            minimum_progress_for_certificate=50, # mocking a default value
            alias=course_alias,
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
