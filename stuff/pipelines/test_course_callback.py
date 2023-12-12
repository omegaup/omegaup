#!/usr/bin/python3

'''test course_callback module.'''

import dataclasses
import datetime
import json
import os
import random
import string
import sys
import time

from typing import Set

import course_callback
import database.course
import omegaup.api
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

    client_admin = omegaup.api.Client(api_token=test_constants.API_TOKEN,
                                      url=test_constants.OMEGAUP_API_ENDPOINT)
    current_time = datetime.datetime.now()
    future_time = current_time + datetime.timedelta(hours=5)
    course_alias = ''.join(random.choices(string.digits, k=8))
    assignment_alias = ''.join(random.choices(string.digits, k=8))

    # Creating a course with an assignment and then adding problem and users
    client_admin.course.create(
        name=course_alias,
        alias=course_alias,
        description='Test course',
        start_time=int(time.mktime(current_time.timetuple())),
        finish_time=int(time.mktime(future_time.timetuple())),
        objective='Testing',
        level='intermediate',
        show_scoreboard=True,
        requests_user_information='no',
    )

    client_admin.course.createAssignment(
        name=assignment_alias,
        alias=assignment_alias,
        course_alias=course_alias,
        assignment_type='homework',
        description='Test course assignment',
        start_time=time.mktime(current_time.timetuple()),
        finish_time=time.mktime(future_time.timetuple()),
    )

    assignment_details = client_admin.course.assignmentDetails(
        course=course_alias,
        assignment=assignment_alias)

    client_admin.course.addProblem(
        course_alias=course_alias,
        assignment_alias=assignment_alias,
        problem_alias='sumas',
        points=1.0,
    )

    usernames: Set[str] = set()
    for number in range(3):
        username = f'course_test_user_{number}'
        client_admin.course.addStudent(course_alias=course_alias,
                                       usernameOrEmail=username,
                                       share_user_information=True,
                                       accept_teacher_git_object_id='0',
                                       privacy_git_object_id='0',
                                       statement_type='accept_teacher',
                                       accept_teacher=False)
        client_user = omegaup.api.Client(
            username=username,
            password=username,
            #  api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT,
        )
        client_user.run.create(
            problemset_id=assignment_details.problemset_id,
            contest_alias='',  # This param should be optional
            problem_alias='sumas',
            language='py3',
            source='print(1)',
        )
        usernames.add(username)

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
    progress = []

    students_progress = client_admin.course.studentsProgress(
        course=course_alias,
        length=1000,
        page=1
    )
    for student_progress in students_progress.progress:
        progress.append(database.course.Progress(
            username=student_progress.username,
            progress=f'{student_progress.courseProgress}')._asdict())
    with rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
    ) as channel:
        callback = course_callback.CourseCallback(dbconn=dbconn.conn)
        body = course_callback.CourseCertificate(
            course_id=course_id,
            minimum_progress_for_certificate=50,  # setting an arbitrary value
            alias=course_alias,
            progress=progress,
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
                i.username
            FROM
                Certificates c
            INNER JOIN
                Identities i
            ON
                i.identity_id = c.identity_id
            INNER JOIN
                Courses cs
            ON
                cs.course_id = c.course_id
            WHERE
                cs.alias = %s;
            ''', (course_alias,))
        certificates = cur.fetchall()
        assert certificates

    stored_usernames: Set[str] = set()
    for certificate in certificates:
        stored_usernames.add(certificate['username'])

    assert stored_usernames == usernames
