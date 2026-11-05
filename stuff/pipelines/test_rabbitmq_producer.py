#!/usr/bin/env python3

'''test rabbitmq_producer module: publisher confirms.'''

from typing import Any, Tuple

import pika.exceptions
import pytest
import pytest_mock

import rabbitmq_producer


def _producer(
        mocker: pytest_mock.MockerFixture
) -> Tuple[rabbitmq_producer.RabbitmqProducer, Any]:
    channel = mocker.MagicMock()
    producer = rabbitmq_producer.RabbitmqProducer(
        queue='contest',
        exchange='certificates',
        routing_key='ContestQueue',
        channel=channel)
    return producer, channel


def test_enables_publisher_confirms(
        mocker: pytest_mock.MockerFixture) -> None:
    '''The channel enables publisher confirms on construction.'''
    _, channel = _producer(mocker)
    channel.confirm_delivery.assert_called_once()


def test_send_message_confirmed(mocker: pytest_mock.MockerFixture) -> None:
    '''A confirmed publish uses mandatory and persistent delivery.'''
    producer, channel = _producer(mocker)
    producer.send_message('{"contest_id": 1}')
    published = channel.basic_publish.call_args.kwargs
    assert published['mandatory'] is True
    assert published['properties'].delivery_mode == 2


def test_send_message_unroutable_raises(
        mocker: pytest_mock.MockerFixture) -> None:
    '''An unroutable message raises MessageNotConfirmedError.'''
    producer, channel = _producer(mocker)
    channel.basic_publish.side_effect = pika.exceptions.UnroutableError([])
    with pytest.raises(rabbitmq_producer.MessageNotConfirmedError):
        producer.send_message('{"contest_id": 1}')


def test_send_message_nack_raises(
        mocker: pytest_mock.MockerFixture) -> None:
    '''A broker nack raises MessageNotConfirmedError.'''
    producer, channel = _producer(mocker)
    channel.basic_publish.side_effect = pika.exceptions.NackError([])
    with pytest.raises(rabbitmq_producer.MessageNotConfirmedError):
        producer.send_message('{"contest_id": 1}')


def test_send_message_amqp_error_raises(
        mocker: pytest_mock.MockerFixture) -> None:
    '''A generic AMQP error raises MessageNotConfirmedError.'''
    producer, channel = _producer(mocker)
    channel.basic_publish.side_effect = pika.exceptions.AMQPChannelError()
    with pytest.raises(rabbitmq_producer.MessageNotConfirmedError):
        producer.send_message('{"contest_id": 1}')
