#!/usr/bin/python3

'''test verification_code module.'''

import json
import pytest
import rabbitmq_connection
from pytest_mock import MockerFixture
from producer_coder_of_month import send_message_client
import rabbitmq_client
import pika


MESSAGE = {}


def initialize_rabbitmq(
        queue: str,
        exchange: str,
        routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel
) -> None:
    '''initializes the queue and exchange'''
    channel.queue_declare(
        queue=queue, passive=False,
        durable=False, exclusive=False,
        auto_delete=False)
    channel.exchange_declare(
        exchange=exchange,
        auto_delete=False,
        durable=True,
        exchange_type='direct')
    channel.queue_bind(exchange=exchange,
                       queue=queue,
                       routing_key=routing_key)


def callback(channel: pika.adapters.blocking_connection.BlockingChannel,
             method: pika.spec.Basic.Deliver,
             properties: pika.spec.BasicProperties,
             # pylint: disable=unused-argument,
             body: bytes) -> None:
    '''Callback function to test'''
    global MESSAGE
    MESSAGE = json.loads(body.decode())
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
def test_coder_of_the_month_queue(mocker: MockerFixture,
                                  params, expected) -> None:
    '''Test the message send to the coder of the month queue'''
    mocker.patch('producer_coder_of_month.get_coder_of_the_month',
                 return_value=params)
    with rabbitmq_connection.connect(username='omegaup',
                                     password='omegaup',
                                     host='rabbitmq') as channel:
        initialize_rabbitmq('coder_month',
                            'certificates',
                            'CoderOfTheMonthQueue',
                            channel)
        send_message_client(channel)
        rabbitmq_client.receive_messages('coder_month',
                                         'certificates',
                                         'CoderOfTheMonthQueue',
                                         channel,
                                         callback)
        assert expected == MESSAGE
