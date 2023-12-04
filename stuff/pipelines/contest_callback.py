#!/usr/bin/python3

'''Processing contest messages.'''

import dataclasses
import json
import logging

from typing import Dict, List, Optional
import mysql.connector
import mysql.connector.cursor
from mysql.connector import errors
from mysql.connector import errorcode
import pika

import database.contest
import verification_code


@dataclasses.dataclass
class Certificate:
    '''A dataclass for certificate.'''
    certificate_type: str
    contest_id: int
    verification_code: str
    contest_place: Optional[int]
    username: str


@dataclasses.dataclass
class ContestCertificate:
    '''A dataclass for contest certificate.'''
    certificate_cutoff: int
    alias: str
    scoreboard_url: str
    contest_id: int
    ranking: List[Dict[str, str]]


class ContestsCallback:
    '''Contests callback'''
    def __init__(
        self,
        dbconn: mysql.connector.MySQLConnection,
        for_testing: bool = False
    ):
        '''Constructor for contest callback'''
        self.dbconn = dbconn
        self.for_testing = for_testing

    def __call__(self,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: Optional[pika.spec.Basic.Deliver],
                 _properties: Optional[pika.spec.BasicProperties],
                 body: bytes) -> None:
        '''Function to store the certificates by a given contest'''
        response = json.loads(body)

        ranking = []
        for user_ranking in response['ranking']:
            ranking.append(database.contest.Ranking(**user_ranking))
        response['ranking'] = ranking
        data = ContestCertificate(**response)
        certificates: List[Certificate] = []

        for user_ranking in data.ranking:
            contest_place: Optional[int] = None
            place = int(user_ranking.place)
            cutoff = data.certificate_cutoff
            if (cutoff and place and place <= cutoff):
                contest_place = user_ranking.place
            certificates.append(Certificate(
                certificate_type='contest',
                contest_id=data.contest_id,
                verification_code=generate_contest_code(),
                contest_place=contest_place,
                username=user_ranking.username
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
                    if err.msg.find('Certificates.contest_identity_key') > 0:
                        logging.exception(
                            'At least one certificate for this contest is '
                            'duplicated'
                        )
                        break
                    for certificate in certificates:
                        certificate.verification_code = generate_contest_code()
                    logging.exception(
                        'At least one of the verification codes had a conflict'
                    )
            try:
                cur.execute('''
                    UPDATE
                        `Contests`
                    SET
                        `certificates_status` = 'generated'
                    WHERE
                        `contest_id` = %s;
                    ''', (data.contest_id,))
                self.dbconn.commit()
            except:  # noqa: bare-except
                logging.exception('Failed to update the certificate status')

        if self.for_testing:
            logging.info(
                'Closing the connection for testing purposes'
            )
            channel.connection.close()


def generate_contest_code() -> str:
    '''Generates a random verification code.'''
    return verification_code.generate_code()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
