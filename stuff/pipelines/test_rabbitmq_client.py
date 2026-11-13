#!/usr/bin/env python3

'''test rabbitmq_client module: ack and nack decisions.'''

import json
from typing import Any

import pytest
import pytest_mock
from mysql.connector import errors

import contest_callback
import rabbitmq_client


def _valid_body() -> bytes:
    '''Returns a structurally valid contest message body.'''
    return json.dumps({
        'certificate_cutoff': 3,
        'alias': 'x',
        'scoreboard_url': 'abc',
        'contest_id': 1,
        'ranking': [{'username': 'user_1', 'place': '1'}],
    }).encode('utf-8')


def _deliver(
        mocker: pytest_mock.MockerFixture,
        callback: Any,
        body: bytes = b'{}',
        is_open: bool = True,
) -> Any:
    '''Runs one delivery through _process_and_ack and returns the channel.'''
    channel = mocker.MagicMock()
    channel.is_open = is_open
    method = mocker.MagicMock()
    method.delivery_tag = 7
    rabbitmq_client._process_and_ack(  # pylint: disable=protected-access
        channel=channel,
        method=method,
        properties=mocker.MagicMock(),
        body=body,
        callback=callback)
    return channel


def test_success_acks(mocker: pytest_mock.MockerFixture) -> None:
    '''A successful callback acknowledges the message.'''
    channel = _deliver(mocker, mocker.MagicMock())
    channel.basic_ack.assert_called_once_with(delivery_tag=7)
    channel.basic_nack.assert_not_called()


def test_unrecoverable_nacks_without_requeue(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''A permanent error is rejected without requeueing.'''
    callback = mocker.MagicMock(
        side_effect=rabbitmq_client.UnrecoverableMessageError('bad'))
    channel = _deliver(mocker, callback)
    channel.basic_nack.assert_called_once_with(delivery_tag=7, requeue=False)
    channel.basic_ack.assert_not_called()


def test_transient_nacks_with_requeue(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''A transient error is rejected and requeued.'''
    callback = mocker.MagicMock(
        side_effect=rabbitmq_client.TransientMessageError('temporary'))
    channel = _deliver(mocker, callback)
    channel.basic_nack.assert_called_once_with(delivery_tag=7, requeue=True)
    channel.basic_ack.assert_not_called()


def test_closed_channel_is_left_alone(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''Nothing is acknowledged or rejected on a closed channel.'''
    callback = mocker.MagicMock(
        side_effect=rabbitmq_client.TransientMessageError('temporary'))
    channel = _deliver(mocker, callback, is_open=False)
    channel.basic_ack.assert_not_called()
    channel.basic_nack.assert_not_called()


@pytest.mark.parametrize(
    'error',
    [
        errors.InterfaceError(  # type: ignore[attr-defined]
            'MySQL server is unavailable'),
        errors.OperationalError(  # type: ignore[attr-defined]
            'Lost connection to MySQL server'),
        errors.InternalError(  # type: ignore[attr-defined]
            'Deadlock found', errno=1213),
        ConnectionError('connection reset'),
        TimeoutError('timed out'),
    ],
)  # type: ignore
def test_infrastructure_failure_is_requeued(
        mocker: pytest_mock.MockerFixture,
        error: Exception
) -> None:
    '''An infrastructure failure in the real callback requeues the message.'''
    dbconn = mocker.MagicMock()
    dbconn.ping.side_effect = error
    callback = contest_callback.ContestsCallback(dbconn=dbconn)
    channel = _deliver(mocker, callback, body=_valid_body())
    channel.basic_nack.assert_called_once_with(delivery_tag=7, requeue=True)
    channel.basic_ack.assert_not_called()


@pytest.mark.parametrize(
    'body',
    [
        b'not json at all',
        b'{}',
        json.dumps({
            'certificate_cutoff': 3,
            'alias': 'x',
            'scoreboard_url': 'abc',
            'contest_id': 1,
            'ranking': [{'username': 'user_1', 'place': 'abc'}],
        }).encode('utf-8'),
    ],
)  # type: ignore
def test_permanent_failure_is_not_requeued(
        mocker: pytest_mock.MockerFixture,
        body: bytes
) -> None:
    '''A data error in the real callback is rejected without requeueing.'''
    callback = contest_callback.ContestsCallback(dbconn=mocker.MagicMock())
    channel = _deliver(mocker, callback, body=body)
    channel.basic_nack.assert_called_once_with(delivery_tag=7, requeue=False)
    channel.basic_ack.assert_not_called()


def test_unexpected_failure_is_not_requeued(
        mocker: pytest_mock.MockerFixture
) -> None:
    '''An unexpected exception in the real callback is not retried.'''
    dbconn = mocker.MagicMock()
    dbconn.ping.side_effect = RuntimeError('unexpected')
    callback = contest_callback.ContestsCallback(dbconn=dbconn)
    channel = _deliver(mocker, callback, body=_valid_body())
    channel.basic_nack.assert_called_once_with(delivery_tag=7, requeue=False)
    channel.basic_ack.assert_not_called()
