#!/usr/bin/env python3

'''Implementation of rabbitmq client.'''

import logging
from typing import Callable, cast
import pika


ClientCallback = Callable[
    [
        pika.adapters.blocking_connection.BlockingChannel,
        pika.spec.Basic.Deliver,
        pika.spec.BasicProperties,
        bytes,
    ], None]


class UnrecoverableMessageError(Exception):
    '''Raised when a message can never be processed; must not be requeued.'''


class TransientMessageError(Exception):
    '''Raised when a message fails temporarily and may be retried.'''


def _process_and_ack(
        *,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        method: pika.spec.Basic.Deliver,
        properties: pika.spec.BasicProperties,
        body: bytes,
        callback: ClientCallback) -> None:
    try:
        callback(channel, method, properties, body)
    except UnrecoverableMessageError:
        logging.exception(
            'Message is unrecoverable, nacking without requeue'
        )
        if channel.is_open:
            channel.basic_nack(
                delivery_tag=cast(int, method.delivery_tag),
                requeue=False)
        return
    except:  # noqa: bare-except pylint: disable=bare-except
        logging.exception(
            'Failed to process message, nacking and requeueing it'
        )
        if channel.is_open:
            channel.basic_nack(
                delivery_tag=cast(int, method.delivery_tag),
                requeue=True)
        return
    if channel.is_open:
        channel.basic_ack(delivery_tag=cast(int, method.delivery_tag))


def receive_messages(
        *, queue: str, exchange: str, routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        callback: ClientCallback,
        stop_after_message: bool = False) -> None:
    '''Receive messages from a queue, acking after a successful callback.'''

    channel.exchange_declare(exchange=exchange,
                             durable=True,
                             exchange_type='direct')
    channel.queue_declare(queue=queue,
                          durable=True,
                          exclusive=False)
    channel.queue_bind(exchange=exchange,
                       queue=queue,
                       routing_key=routing_key)
    if stop_after_message:
        method, properties, body = channel.basic_get(
            queue=queue,
            auto_ack=False)

        if method is not None and properties is not None and body is not None:
            _process_and_ack(
                channel=channel,
                method=cast(pika.spec.Basic.Deliver, method),
                properties=properties,
                body=cast(bytes, body),
                callback=callback)
        return

    def _on_message_callback(
            channel: pika.adapters.blocking_connection.BlockingChannel,
            method: pika.spec.Basic.Deliver,
            properties: pika.spec.BasicProperties,
            body: bytes) -> None:
        _process_and_ack(
            channel=channel,
            method=method,
            properties=properties,
            body=body,
            callback=callback)

    channel.basic_consume(queue=queue,
                          on_message_callback=_on_message_callback,
                          auto_ack=False)
    try:
        channel.start_consuming()
    except KeyboardInterrupt:
        channel.stop_consuming()
