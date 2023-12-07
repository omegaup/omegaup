#!/usr/bin/env python3

'''Mysql queries to generate messages for courses'''

import datetime
from typing import List, NamedTuple

import mysql.connector
import mysql.connector.cursor
import omegaup.api


class Progress(NamedTuple):
    '''Relevant information for progress users in a course.'''
    username: str
    progress: str


class CourseCertificate(NamedTuple):
    '''Relevant information for courses.'''
    alias: str
    minimum_progress_for_certificate: str
    course_id: str


class UserCourseCertificate(NamedTuple):
    '''Relevant information for user course certificates.'''
    username: str
    course_id: str


def get_all_certificates_for_courses(
    *,
    cur: mysql.connector.cursor.MySQLCursorDict
) -> List[UserCourseCertificate]:
    '''
    Get all certificates previously registered in order to avoid duplicated
    entries for a user course
    '''

    cur.execute(
        '''
        SELECT
            i.username,
            c.course_id
        FROM
            Certificates ce
        INNER JOIN
            Courses c
        ON
            c.course_id = ce.course_id
        INNER JOIN
            Identities i
        ON
            i.identity_id = ce.identity_id;
        ''')

    data: List[UserCourseCertificate] = []
    for row in cur:
        user_course = UserCourseCertificate(
            username=row['username'],
            course_id=row['course_id'],
        )
        data.append(user_course)
    return data


def get_courses(
        *,
        cur: mysql.connector.cursor.MySQLCursorDict,
        date_lower_limit: datetime.datetime,
        date_upper_limit: datetime.datetime,
        certificates: List[UserCourseCertificate],
        client: omegaup.api.Client,
) -> List[CourseCertificate]:
    '''Get courses information'''

    cur.execute(
        '''
        SELECT
            c.course_id,
            c.alias,
            c.minimum_progress_for_certificate
        FROM
            Courses c
        WHERE
            finish_time >= %s AND finish_time <= %s;
        ''', (date_lower_limit,
              date_upper_limit.replace(hour=23, minute=59, second=59))
    )
    data: List[CourseCertificate] = []
    students_progress = []
    for row in cur:
        course_certificate = CourseCertificate(**row)
        result = client.course.studentsProgress(
            course=course_certificate.alias,
            length=1000,
            page=1
        )
        users_course: List[UserCourseCertificate] = [
            certificate for certificate in certificates
            if certificate.course_id == row['course_id']
        ]

        for student in result.progress:
            existing_certificates = [
                record for record in users_course
                if record.username == student.username
            ]
            if len(existing_certificates) > 0:
                continue
            students_progress.append(Progress(
                username=student.username,
                progress=f'{student.courseProgress}')._asdict())
        course = CourseCertificate(
            alias=row['alias'],
            minimum_progress_for_certificate=row[
                'minimum_progress_for_certificate'
            ],
            course_id=row['course_id'],
        )
        data.append(course)
    return data
