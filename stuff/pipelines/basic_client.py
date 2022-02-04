#!/usr/bin/python3

'''Implementation of a client class to be using with the tests.'''

import json
from typing import Callable, Dict
import pika


class ClientCallback():
    '''Client callback'''
    def __init__(self) -> None:
        self.message: Dict[str, str] = {}

    def __call__(
            self,
            channel: pika.adapters.blocking_connection.BlockingChannel,
            method: pika.spec.Basic.Deliver,
            properties: pika.spec.BasicProperties,
            # pylint: disable=unused-argument,
            body: bytes) -> None:
        data = json.loads(body.decode())
        self.message = data
        channel.close()


def receive_messages(
        queue: str, exchange: str, routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        callback: Callable[
            [
                pika.adapters.blocking_connection.BlockingChannel,
                pika.spec.Basic.Deliver,
                pika.spec.BasicProperties,
                bytes,
            ], None]) -> None:
    '''Receive messages from a queue'''

    channel.exchange_declare(exchange=exchange,
                             durable=True,
                             exchange_type='direct')
    channel.queue_bind(exchange=exchange,
                       queue=queue,
                       routing_key=routing_key)

    channel.basic_consume(queue=queue,
                          on_message_callback=callback,
                          auto_ack=True)
    channel.start_consuming()
