#!/usr/bin/python3

'''Implementation of rabbitmq producer.'''

import pika


def send_message(
        queue: str, exchange: str, routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        message: str) -> None:
    '''Send message to any queue'''
    channel.queue_declare(queue, passive=False,
                          durable=False, exclusive=False,
                          auto_delete=False)
    channel.exchange_declare(exchange=exchange,
                             auto_delete=False,
                             durable=True,
                             exchange_type='direct')
    body = message.encode()
    channel.basic_publish(exchange=exchange,
                          routing_key=routing_key,
                          body=body)
