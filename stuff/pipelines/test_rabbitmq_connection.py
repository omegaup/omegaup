#!/usr/bin/env python3

'''test rabbitmq connection module.'''

import pytest
import pika
import rabbitmq_connection
import test_credentials


# mypy has conflict with pytest decorations
@pytest.mark.parametrize(
    "exchange, expected",
    [
        ('certificates', True),
        ('wrong_exchange_name', False),
    ],
)  # type: ignore
def test_rabbitmq_connection(exchange: str, expected: bool) -> None:
    '''Test rabbitmq'''
    with rabbitmq_connection.connect(
            username=test_credentials.OMEGAUP_USERNAME,
            password=test_credentials.OMEGAUP_PASSWORD,
            host=test_credentials.RABBITMQ_HOST,
            for_testing=False
    ) as channel:
        def on_message(
                channel: pika.adapters.blocking_connection.BlockingChannel,
                _method: pika.spec.Basic.Deliver,
                _properties: pika.spec.BasicProperties,
                body: bytes) -> None:
            '''Mocking on_message function'''
            message = body.decode()
            assert message == 'value'
            channel.basic_cancel('test-consumer')

        def publish_message(body: str) -> None:
            '''Mocking publish_message function'''
            channel.basic_publish(
                exchange=exchange,
                routing_key='ContestQueue',
                body=body.encode(),
            )

        result = channel.queue_declare(queue='test', durable=True,
                                       exclusive=True)
        queue_name = result.method.queue
        assert queue_name is not None

        publish_message('some message')
        try:
            channel.basic_consume(
                queue=queue_name,
                on_message_callback=on_message,
            )
            assert expected is True
        except:  # noqa: bare-except
            assert expected is False
