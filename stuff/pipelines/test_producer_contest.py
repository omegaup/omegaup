#!/usr/bin/env python3

'''test producer of contests.'''

import json
import dataclasses
import os
import sys

from typing import Dict, Any
import pytest
import pika
import pytest_mock 

import credentials
import rabbitmq_connection
import producer_contest
import rabbitmq_client
import rabbitmq_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position

@dataclasses.dataclass
class Message:
    '''class to save message'''
    message: Dict[str, Any] = dataclasses.field(default_factory=dict)

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
        ({'user_id': 1, 'time': '2022-01-26',
          'category': 'all'},
         {'user_id': 1, 'time': '2022-01-26',
          'category': 'all'}),
        ({'user_id': 1, 'time': '2022-01-26',
          'category': 'female'},
         {'user_id': 1, 'time': '2022-01-26',
          'category': 'female'}),
    ],
)  # type: ignore
def test_contest_producer(mocker: pytest_mock.MockerFixture,
                          params,
                          expected) -> None:
    '''Test the message send to the contest queue'''
    mocker.patch('producer_contest.get_contests', return_value=params)

    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=credentials.MYSQL_USER,
            password=credentials.MYSQL_PASSWORD,
            host=credentials.MYSQL_HOST,
            database=credentials.MYSQL_DATABASE,
            port=credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(username=credentials.OMEGAUP_USERNAME,
                                     password=credentials.OMEGAUP_PASSWORD,
                                     host=credentials.RABBITMQ_HOST) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='contest',
            exchange='certificates',
            routing_key='ContestQueue',
            channel=channel)
        producer_contest.send_contest_message_to_client(cur=cur,
                                                        channel=channel)
        message = Message
        rabbitmq_client.receive_messages(queue='contest',
                                         exchange='certificates',
                                         routing_key='ContestQueue',
                                         channel=channel,
                                         callback=message())
        assert expected == message()
