#!/usr/bin/env python3

'''test producer of contests.'''

import json
import dataclasses
import os
import sys

from typing import Optional
import pytest
import pika
import pytest_mock
import contest_callback

import database.contest
import producer_contest
import rabbitmq_connection
import rabbitmq_client
import test_credentials

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position


@dataclasses.dataclass
class MessageSavingCallback:
    '''class to save message'''
    message: Optional[contest_callback.ContestCertificate] = None

    def __call__(self,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: pika.spec.Basic.Deliver,
                 _properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Callback function to test'''
        self.message = json.loads(body.decode())
        channel.close()


# mypy has conflict with pytest decorations
@pytest.mark.parametrize(
    'params, expected',
    [
        (
            [
                database.contest.ContestCertificate(
                    certificate_cutoff=1,
                    alias='contest1',
                    scoreboard_url='abcdef',
                    contest_id=1,
                ),
            ],
            database.contest.ContestCertificate(
                certificate_cutoff=1,
                alias='contest1',
                scoreboard_url='abcdef',
                contest_id=1,
            )._asdict(),
        ),
        (
            [
                database.contest.ContestCertificate(
                    certificate_cutoff=1,
                    alias='contest2',
                    scoreboard_url='123456',
                    contest_id=2,
                ),
            ],
            database.contest.ContestCertificate(
                certificate_cutoff=1,
                alias='contest2',
                scoreboard_url='123456',
                contest_id=2,
            )._asdict(),
        ),
    ],
)  # type: ignore
def test_contest_producer(mocker: pytest_mock.MockerFixture,
                          params,
                          expected) -> None:
    '''Test the message send to the contest queue'''
    mocker.patch('producer_contest.get_contests_from_db', return_value=params)

    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=test_credentials.MYSQL_USER,
            password=test_credentials.MYSQL_PASSWORD,
            host=test_credentials.MYSQL_HOST,
            database=test_credentials.MYSQL_DATABASE,
            port=test_credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
    ) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='contest',
            exchange='certificates',
            routing_key='ContestQueue',
            channel=channel)
        producer_contest.send_contest_message_to_client(cur=cur,
                                                        channel=channel)
        callback = MessageSavingCallback()
        rabbitmq_client.receive_messages(queue='contest',
                                         exchange='certificates',
                                         routing_key='ContestQueue',
                                         channel=channel,
                                         callback=callback)
        assert expected == callback.message
