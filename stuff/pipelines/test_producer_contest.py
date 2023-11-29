#!/usr/bin/env python3

'''test producer of contests.'''

import json
import dataclasses
import os
import sys

from typing import Dict, List, Optional
import pytest
import pika
import pytest_mock
import contest_callback

import database.contest
import producer_contest
import rabbitmq_connection
import rabbitmq_client
import test_credentials
import test_constants
import omegaup.api

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
        self.message = contest_callback.ContestCertificate(**json.loads(
            body.decode()))
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
                    ranking=[
                        database.contest.Ranking(
                            username='user_1', place='1')._asdict(),
                    ],
                ),
            ],
            database.contest.ContestCertificate(
                certificate_cutoff=1,
                alias='contest1',
                scoreboard_url='abcdef',
                contest_id=1,
                ranking=[
                    database.contest.Ranking(
                        username='user_1', place='1')._asdict(),
                ],
            )._asdict(),
        ),
        (
            [
                database.contest.ContestCertificate(
                    certificate_cutoff=1,
                    alias='contest2',
                    scoreboard_url='123456',
                    contest_id=2,
                    ranking=[
                        database.contest.Ranking(
                            username='user_1', place='1')._asdict(),
                    ],
                ),
            ],
            database.contest.ContestCertificate(
                certificate_cutoff=1,
                alias='contest2',
                scoreboard_url='123456',
                contest_id=2,
                ranking=[
                    database.contest.Ranking(
                        username='user_1', place='1')._asdict(),
                ],
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
            for_testing=False
    ) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='contest',
            exchange='certificates',
            routing_key='ContestQueue',
            channel=channel)
        client = omegaup.api.Client(
            api_token=test_constants.API_TOKEN,
            url=test_constants.OMEGAUP_API_ENDPOINT,
        )
        producer_contest.send_contest_message_to_client(cur=cur,
                                                        channel=channel,
                                                        client=client)
        callback = MessageSavingCallback()
        rabbitmq_client.receive_messages(queue='contest',
                                         exchange='certificates',
                                         routing_key='ContestQueue',
                                         channel=channel,
                                         callback=callback)

        if callback.message is not None:
            ranking: List[Dict[str, str]] = []
            for ranking_data in callback.message.ranking:
                ranking.append(ranking_data)

            callback.message.ranking = ranking

            result = callback.message
            assert expected['certificate_cutoff'] == result.certificate_cutoff
            assert expected['alias'] == result.alias
            assert expected['scoreboard_url'] == result.scoreboard_url
            assert expected['ranking'] == result.ranking
