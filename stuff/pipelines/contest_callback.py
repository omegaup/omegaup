#!/usr/bin/python3

'''Processing contest messages.'''

import dataclasses
import json
import logging

from typing import List, Optional

import verification_code

import database.contest
import omegaup.api
import pika
import mysql.connector
import mysql.connector.cursor
from mysql.connector import errors
from mysql.connector import errorcode


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


class ContestsCallback:
    '''Contests callback'''
    def __init__(self,
                 dbconn: mysql.connector.MySQLConnection,
                 client: omegaup.api.Client):
        '''Contructor for contest callback'''
        self.dbconn = dbconn
        self.client = client

    def __call__(self,
                 _channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: Optional[pika.spec.Basic.Deliver],
                 _properties: Optional[pika.spec.BasicProperties],
                 body: bytes) -> None:
        '''Function to store the certificates by a given contest'''
        response = json.loads(body)
        data = ContestCertificate(**response)

        scoreboard = self.client.contest.scoreboard(
            contest_alias=data.alias,
            token=data.scoreboard_url)
        ranking = scoreboard.ranking
        certificates: List[Certificate] = []
        usernames: List[str] = []

        for user in ranking:
            contest_place: Optional[int] = None
            if (data.certificate_cutoff and user.place
                    and user.place <= data.certificate_cutoff):
                contest_place = user.place
            certificates.append(Certificate(
                certificate_type='contest',
                contest_id=data.contest_id,
                verification_code=generate_contest_code(),
                contest_place=contest_place,
                username=str(user.username)
            ))
            usernames.append(user.username)

        notifications = []
        with self.dbconn.cursor(buffered=True, dictionary=True) as cur:
            users_ids = database.contest.get_users_ids(
                cur,
                usernames
            )
            contest_title = database.contest.get_contest_title(
                cur,
                data.contest_id
            )
            for index, user_id in enumerate(users_ids):
                notifications.append(
                    (user_id, json.dumps({
                        'type': 'certificate',
                        'body': {
                            'localizationString':
                                'notificationNewContestCertificate',
                            'localizationParams': {
                                'contest_title': contest_title,
                            },
                            'url': "/certificates/mine/#" +
                                certificates[index].verification_code,
                            'iconUrl': '/media/info.png',
                        },
                    })))

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
                    cur.executemany(
                        '''
                        INSERT INTO
                            Notifications (user_id, contents)
                        VALUES (%s, %s)''', notifications)
                    self.dbconn.commit()
                    break
                except errors.IntegrityError as err:
                    self.dbconn.rollback()
                    if err.errno != errorcode.ER_DUP_ENTRY:
                        raise
                    for index, certificate in enumerate(certificates):
                        certificate.verification_code = generate_contest_code()
                        user_id = notifications[index][0]
                        notifications[index] = (user_id, json.dumps({
                            'type': 'certificate',
                            'body': {
                                'localizationString':
                                    'notificationNewContestCertificate',
                                'localizationParams': {
                                    'contest_title': contest_title,
                                },
                                'url': "/certificates/mine/#" +
                                    certificate.verification_code,
                                'iconUrl': '/media/info.png',
                            },
                        }))
                    logging.exception(
                        'At least one of the verification codes had a conflict'
                    )


def generate_contest_code() -> str:
    '''Generates a random verification code.'''
    return verification_code.generate_code()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
