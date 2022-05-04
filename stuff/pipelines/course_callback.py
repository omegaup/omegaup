#!/usr/bin/python3

'''Processing courses messages.'''

import dataclasses
import json
import logging

import omegaup.api

from typing import List
from mysql.connector import errors
from mysql.connector import errorcode
import mysql.connector
import mysql.connector.cursor
import pika

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


class CourseCallback:
    '''Courses callback'''
    def __init__(self,
                 dbconn: mysql.connector.MySQLConnection,
                 client: omegaup.api.Client):
        '''Contructor for course callback'''
        self.dbconn = dbconn
        self.client = client

    def __call__(self,
                 _channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: pika.spec.Basic.Deliver,
                 _properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Function to stores the certificates by a given course'''
        data = CourseCertificate(**json.loads(body))

        result = self.client.course.studentsProgress(
            course=data.alias,
            length=100,
            page=1
        )
        progress = result.progress

        certificates: List[Certificate] = []

        for user in progress:
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
                    for certificate in certificates:
                        certificate.verification_code = generate_course_code()
                    logging.exception(
                        'At least one of the verification codes had a conflict'
                    )


def generate_course_code() -> str:
    return verification_code.generate_code()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
