#!/usr/bin/python3

'''test rabbitmq connection module.'''

import pytest
import pika


# mypy has conflict with pytest decorations
@pytest.mark.parametrize(
    "exchange, expected",
    [
        ('certificates', True),
        ('wrong_exchange_name', False),
    ],
)  # type: ignore
def test_rabbitmq_connection(exchange: str, expected: bool):
    '''Test rabbitmq'''
    conn = pika.BlockingConnection(pika.ConnectionParameters(
        host='rabbitmq',
        port=5672,
        virtual_host='/',
        credentials=pika.PlainCredentials('omegaup', 'omegaup')))
    channel = conn.channel()

    def on_message(channel, _method_frame, _header_frame, body):
        message = body.decode()
        assert message == 'value'
        channel.basic_cancel('test-consumer')

    def publish_message(body):
        channel.basic_publish(
            exchange=exchange,
            routing_key='ContestQueue',
            body=body,
        )

    result = channel.queue_declare(queue='', exclusive=True)
    queue_name = result.method.queue
    assert queue_name is not None

    publish_message('some message')
    try:
        channel.basic_consume(
            queue=queue_name,
            on_message_callback=on_message,
        )
        assert expected is True
    except pika.exceptions.ChannelClosedByBroker:
        assert expected is False
