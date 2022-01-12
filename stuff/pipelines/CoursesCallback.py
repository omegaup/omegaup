#!/usr/bin/python3

'''Processing courses messages.'''

import json
import dataclasses
import logging
from typing import List, Optional
import omegaup.api
import mysql.connector
import mysql.connector.cursor
from mysql.connector import errorcode
import pika
from verification_code import generate_code


@dataclasses.dataclass
class Certificate:
    '''A dataclass for certificate.'''
    certificate_type: str
    course_id: int
    verification_code: str
    username: str


class CoursesCallback:
    '''Courses callback'''
    def __init__(self,
                 dbconn: mysql.connector.MySQLConnection,
                 api_token: str,
                 url: str,
                 user_password: str,
                 user_username: str):
        '''Contructor for course callback'''
        self.dbconn = dbconn
        self.api_token = api_token
        self.url = url
        self.user_password = user_password
        self.user_username = user_username

    def __call__(self,
                 _channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: pika.spec.Basic.Deliver,
                 _properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Function to stores the certificates by a given course'''
        data = json.loads(body.decode())
        client = omegaup.api.Client(api_token=self.api_token,
                                    url=self.url)
        login = client.user.login(
            password=self.user_password,
            usernameOrEmail=self.user_username,
        )
        result = client.course.studentsProgress(
            auth_token=login['auth_token'],
            course=data['alias'],
        )
        progress = result['progress']

        certificates: List[Certificate] = []

        for user in progress:
            minimum_progress = data['minimum_progress_for_certificate']
            if user['courseProgress'] < minimum_progress:
                continue
            verification_code = generate_code()
            certificates.append(Certificate(
                certificate_type='course',
                course_id=int(data['course_id']),
                verification_code=verification_code,
                username=str(user['username']),
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
                except mysql.connector.Error as err:
                    self.dbconn.rollback()
                    if err.errno != errorcode.ER_DUP_ENTRY:
                        raise
                    for certificate in certificates:
                        certificate.verification_code = generate_code()
                    logging.exception(
                        'At least one of the verification codes had a conflict'
                    )

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
