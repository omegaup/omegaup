#!/usr/bin/python3

'''Processing contest messages.'''

import dataclasses
import json
import logging

from typing import List, Optional
import omegaup.api
import mysql.connector
import mysql.connector.cursor
from mysql.connector import errors
from mysql.connector import errorcode
import pika

from verification_code import generate_code


@dataclasses.dataclass
class Certificate:
    '''A dataclass for certificate.'''
    certificate_type: str
    contest_id: int
    verification_code: str
    contest_place: Optional[int]
    username: str


class ContestsCallback:
    '''Contests callback'''
    def __init__(self,
                 dbconn: mysql.connector.MySQLConnection,
                 api_token: str,
                 url: str):
        '''Contructor for contest callback'''
        self.dbconn = dbconn
        self.api_token = api_token
        self.url = url

    def __call__(self,
                 _channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: pika.spec.Basic.Deliver,
                 _properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Function to stores the certificates by a given contest'''
        data = json.loads(body.decode())
        client = omegaup.api.Client(api_token=self.api_token,
                                    url=self.url)

        scoreboard = client.contest.scoreboard(
            contest_alias=data[1],
            token=data[2])
        ranking = scoreboard['ranking']
        certificates: List[Certificate] = []

        for user in ranking:
            contest_place: Optional[int] = None
            if (data[0] and user['place'] <= data[0]):
                contest_place = user['place']
            verification_code = generate_code()
            certificates.append(Certificate(
                certificate_type='contest',
                contest_id=int(data[3]),
                verification_code=verification_code,
                contest_place=contest_place,
                username=str(user['username'])
            ))
        with self.dbconn.cursor(buffered=True, dictionary=True) as cur:
            while True:
                try:
                    cur.executemany('''
                        INSERT INTO
                            `Certificates` (
                                `identity_id`,
                                `certificate_type`,
                                `contest_id`,
                                `verification_code`,
                                `contest_place`)
                        SELECT
                            `identity_id`,
                            %s,
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
                        certificate.verification_code = generate_code()
                    logging.exception(
                        'At least one of the verification codes had a conflict'
                    )

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
