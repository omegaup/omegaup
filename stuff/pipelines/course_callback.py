#!/usr/bin/python3

'''Processing courses messages.'''

import dataclasses
import json
import logging

from typing import Dict, List, Optional
from mysql.connector import errors
from mysql.connector import errorcode
import mysql.connector
import mysql.connector.cursor
import pika

import database.course
import verification_code


@dataclasses.dataclass
class Certificate:
    '''A dataclass for certificate.'''
    certificate_type: str
    course_id: int
    verification_code: str
    username: str


@dataclasses.dataclass
class CourseCertificate:
    '''A dataclass for course certificate.'''
    alias: str
    course_id: int
    minimum_progress_for_certificate: int
    progress: List[Dict[str, str]]


class CourseCallback:
    '''Courses callback'''
    def __init__(self,
                 dbconn: mysql.connector.MySQLConnection):
        '''Constructor for course callback'''
        self.dbconn = dbconn

    def __call__(self,
                 _channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: Optional[pika.spec.Basic.Deliver],
                 _properties: Optional[pika.spec.BasicProperties],
                 body: bytes) -> None:
        '''Function to store the certificates by a given course'''
        response = json.loads(body)

        students_progress = []
        for student_progress in response['progress']:
            students_progress.append(
                database.course.Progress(**student_progress))
        data = CourseCertificate(**json.loads(body))
        certificates: List[Certificate] = []

        for user in response['progress']:
            minimum_progress = data.minimum_progress_for_certificate
            if user.courseProgress < minimum_progress:
                continue
            certificates.append(Certificate(
                certificate_type='course',
                course_id=int(data.course_id),
                verification_code=generate_course_code(),
                username=str(user.username),
            ))
        with self.dbconn.cursor(buffered=True, dictionary=True) as cur:
            while True:
                try:
                    cur.execute('''
                        INSERT INTO
                            `Certificates` (
                                `identity_id`,
                                `certificate_type`,
                                `course_id`,
                                `verification_code`)
                        SELECT
                            `identity_id`,
                            %s,
                            %s,
                            %s
                        FROM
                            `Identities`
                        WHERE
                            `username` = %s;
                        ''',
                                [
                                    dataclasses.astuple(
                                        certificate
                                    ) for certificate in certificates])
                    self.dbconn.commit()
                    break
                except errors.IntegrityError as err:
                    self.dbconn.rollback()
                    if err.errno != errorcode.ER_DUP_ENTRY:
                        raise
                    if err.msg.find('Certificates.course_identity_key') > 0:
                        logging.exception(
                            'At least one certificate for this course is '
                            'duplicated'
                        )
                        break
                    for certificate in certificates:
                        certificate.verification_code = generate_course_code()
                    logging.exception(
                        'At least one of the verification codes had a conflict'
                    )


def generate_course_code() -> str:
    '''Generates a random verification code.'''
    return verification_code.generate_code()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
