#!/usr/bin/env python3

'''Implementation of rabbitmq producer.'''

import pika


class RabbitmqProducer:
    '''Implementation of rabbitmq producer.'''
    def __init__(
            self,
            queue: str, exchange: str, routing_key: str,
            channel: pika.adapters.blocking_connection.BlockingChannel
    ) -> None:
        '''initializes the queue, exchange and channel'''
        self.exchange = exchange
        self.routing_key = routing_key
        self.channel = channel
        self.channel.queue_declare(
            queue=queue, passive=False,
            durable=True, exclusive=False,
            auto_delete=False)
        self.channel.exchange_declare(
            exchange=self.exchange,
            auto_delete=False,
            durable=True,
            exchange_type='direct')

    def send_message(self, message: str) -> None:
        '''Send message to any queue'''
        body = message.encode()
        self.channel.basic_publish(exchange=self.exchange,
                                   routing_key=self.routing_key,
                                   body=body)
