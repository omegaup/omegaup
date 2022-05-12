#!/usr/bin/env python3

'''Mysql queries to generate messages for courses'''

import datetime
from typing import List, NamedTuple

import mysql.connector
import mysql.connector.cursor


class CourseCertificate(NamedTuple):
    '''Relevant information for courses.'''
    alias: str
    minimum_progress_for_certificate: str
    course_id: str


def get_courses(
        *,
        cur: mysql.connector.cursor.MySQLCursorDict,
        date_lower_limit: datetime.datetime,
        date_upper_limit: datetime.datetime,
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
    for row in cur:
        course = CourseCertificate(
            alias=row['alias'],
            minimum_progress_for_certificate=row[
                'minimum_progress_for_certificate'
            ],
            course_id=row['course_id'],
        )
        data.append(course)
    return data
