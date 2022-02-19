#!/usr/bin/python3

'''Implementation of rabbitmq client.'''

from typing import Callable
import pika


ClientCallback = Callable[
    [
        pika.adapters.blocking_connection.BlockingChannel,
        pika.spec.Basic.Deliver,
        pika.spec.BasicProperties,
        bytes,
    ], None]


def receive_messages(
        *, queue: str, exchange: str, routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        callback: ClientCallback) -> None:
    '''Receive messages from a queue'''

    channel.exchange_declare(exchange=exchange,
                             durable=True,
                             exchange_type='direct')
    channel.queue_declare(queue=queue,
                          durable=True,
                          exclusive=False)
    channel.queue_bind(exchange=exchange,
                       queue=queue,
                       routing_key=routing_key)

    channel.basic_consume(queue=queue,
                          on_message_callback=callback,
                          auto_ack=True)
    try:
        channel.start_consuming()
    except KeyboardInterrupt:
        channel.stop_consuming()
