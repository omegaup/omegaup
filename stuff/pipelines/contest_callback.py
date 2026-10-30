#!/usr/bin/python3

'''Processing contest messages.'''

import dataclasses
import json
import logging

from typing import Any, Dict, List, Optional
import mysql.connector
import mysql.connector.cursor
from mysql.connector import errors
from mysql.connector import errorcode
import pika

import database.contest
import rabbitmq_client
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
        response = self._parse_body(body)
        self._validate_structure(response)

        try:
            self._process(channel, response)
        except rabbitmq_client.UnrecoverableMessageError:
            self._handle_processing_error(response)
            raise
        except Exception as err:  # pylint: disable=broad-except
            self._handle_processing_error(response)
            raise rabbitmq_client.TransientMessageError(
                'Transient error while processing the message'
            ) from err

    def _process(
            self,
            channel: pika.adapters.blocking_connection.BlockingChannel,
            response: Any) -> None:
        '''Builds and stores the certificates for a validated message.'''
        self.dbconn.ping(  # type: ignore[attr-defined]
            reconnect=True,
            attempts=3,
            delay=5,
        )
        ranking = []
        for user_ranking in response['ranking']:
            ranking.append(database.contest.Ranking(**user_ranking))
        response['ranking'] = ranking
        data = ContestCertificate(**response)

        certificates: List[Certificate] = []
        usernames: List[str] = []

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
            usernames.append(user_ranking.username)

        notifications: List[Any] = []
        with self.dbconn.cursor(buffered=True, dictionary=True) as cur:
            try:
                users_ids = database.contest.get_users_ids(
                    cur,
                    usernames
                )
            except KeyError as err:
                raise rabbitmq_client.UnrecoverableMessageError(
                    'A username in the ranking does not exist'
                ) from err
            contest_title = database.contest.get_contest_title(
                cur,
                data.contest_id
            )
            for index, user_id in enumerate(users_ids):
                notifications.append(
                    (user_id, json.dumps({
                        'type': 'certificate-awarded',
                        'body': {
                            'localizationString':
                                'notificationNewContestCertificate',
                            'localizationParams': {
                                'contest_title': contest_title,
                            },
                            'url': '/certificates/mine/#' +
                                certificates[index].verification_code,
                            'iconUrl': '/media/info.png',
                        },
                    })))

            self._insert_certificates(
                cur, certificates, notifications, contest_title)

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
                logging.exception(
                    'Failed to update the certificate status'
                )

        if self.for_testing:
            logging.info(
                'Closing the connection for testing purposes'
            )
            channel.connection.close()

    def _insert_certificates(
            self,
            cur: mysql.connector.cursor.MySQLCursorDict,
            certificates: List[Certificate],
            notifications: List[Any],
            contest_title: str) -> None:
        '''Inserts the certificates, regenerating codes on collision.'''
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
                return
            except errors.IntegrityError as err:
                self.dbconn.rollback()
                if err.errno != errorcode.ER_DUP_ENTRY:
                    raise
                if err.msg.find('Certificates.contest_identity_key') > 0:
                    logging.exception(
                        'At least one certificate for this contest is '
                        'duplicated'
                    )
                    return
                for index, cert in enumerate(certificates):
                    cert.verification_code = generate_contest_code()
                    user_id = notifications[index][0]
                    notifications[index] = (user_id, json.dumps({
                        'type': 'certificate-awarded',
                        'body': {
                            'localizationString':
                                'notificationNewContestCertificate',
                            'localizationParams': {
                                'contest_title': contest_title,
                            },
                            'url': '/certificates/mine/#' +
                                cert.verification_code,
                            'iconUrl': '/media/info.png',
                        },
                    }))
                logging.exception(
                    'At least one of the verification codes had a '
                    'conflict'
                )

    def _handle_processing_error(self, response: Any) -> None:
        '''Marks the contest as retryable when it can be identified.'''
        contest_id = (
            response.get('contest_id')
            if isinstance(response, dict) else None
        )
        if contest_id is not None:
            try:
                self._mark_retryable_error(contest_id)
            except Exception:  # noqa: bare-except pylint: disable=broad-except
                logging.exception(
                    'Failed to mark the contest as retryable_error'
                )

    _REQUIRED_FIELDS = (
        'certificate_cutoff',
        'alias',
        'scoreboard_url',
        'contest_id',
        'ranking',
    )

    @staticmethod
    def _parse_body(body: bytes) -> Any:
        '''Parses the message body, discarding it if it is not valid JSON.'''
        try:
            return json.loads(body)
        except json.JSONDecodeError as err:
            raise rabbitmq_client.UnrecoverableMessageError(
                'Message body is not valid JSON'
            ) from err

    @classmethod
    def _validate_structure(cls, response: Any) -> None:
        '''Validates the minimum structure of a contest message.'''
        if not isinstance(response, dict):
            raise rabbitmq_client.UnrecoverableMessageError(
                'Message body is not a JSON object'
            )
        missing = [
            field for field in cls._REQUIRED_FIELDS if field not in response
        ]
        if missing:
            raise rabbitmq_client.UnrecoverableMessageError(
                'Message is missing required fields: ' + ', '.join(missing)
            )
        if not isinstance(response['ranking'], list):
            raise rabbitmq_client.UnrecoverableMessageError(
                "Message field 'ranking' is not a list"
            )

    def _mark_retryable_error(self, contest_id: int) -> None:
        '''Marks the given contest's certificates as a retryable error.'''
        with self.dbconn.cursor(buffered=True, dictionary=True) as cur:
            cur.execute('''
                UPDATE
                    `Contests`
                SET
                    `certificates_status` = 'retryable_error'
                WHERE
                    `contest_id` = %s;
                ''', (contest_id,))
            self.dbconn.commit()


def generate_contest_code() -> str:
    '''Generates a random verification code.'''
    return verification_code.generate_code()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
