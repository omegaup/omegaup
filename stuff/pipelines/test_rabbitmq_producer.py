#!/usr/bin/env python3

'''test rabbitmq_producer module: publisher confirms.'''

from typing import Any, Tuple, cast

import pika.exceptions
import pytest
import pytest_mock

import database.contest
import producer_contest
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


def _contest(contest_id: int) -> database.contest.ContestCertificate:
    '''Builds a contest message for the batch tests.'''
    return database.contest.ContestCertificate(
        certificate_cutoff=1,
        alias='contest-%d' % contest_id,
        scoreboard_url='url',
        contest_id=contest_id,
        ranking=[])


def test_batch_continues_and_reports_failed_contests(
        mocker: pytest_mock.MockerFixture,
        caplog: pytest.LogCaptureFixture) -> None:
    '''A failed publication does not stop the batch and is reported.'''
    mocker.patch(
        'producer_contest.get_contests_from_db',
        return_value=[_contest(1), _contest(2), _contest(3)])
    producer = mocker.MagicMock()
    producer.send_message.side_effect = [
        None,
        rabbitmq_producer.MessageNotConfirmedError('nack'),
        None,
    ]
    mocker.patch('rabbitmq_producer.RabbitmqProducer', return_value=producer)

    with pytest.raises(
            rabbitmq_producer.UnconfirmedPublicationsError) as raised:
        producer_contest.send_contest_message_to_client(
            cur=mocker.MagicMock(),
            channel=mocker.MagicMock(),
            client=mocker.MagicMock())

    failure = cast(
        rabbitmq_producer.UnconfirmedPublicationsError, raised.value)
    assert producer.send_message.call_count == 3
    assert failure.failed_ids == [2]
    assert 'Contest 2 was not confirmed' in caplog.text


def test_batch_without_failures_does_not_raise(
        mocker: pytest_mock.MockerFixture) -> None:
    '''A batch where every message is confirmed finishes without error.'''
    mocker.patch(
        'producer_contest.get_contests_from_db',
        return_value=[_contest(1), _contest(2)])
    producer = mocker.MagicMock()
    producer.send_message.return_value = None
    mocker.patch('rabbitmq_producer.RabbitmqProducer', return_value=producer)

    producer_contest.send_contest_message_to_client(
        cur=mocker.MagicMock(),
        channel=mocker.MagicMock(),
        client=mocker.MagicMock())

    assert producer.send_message.call_count == 2
