#!/usr/bin/env python3

'''Implementation of rabbitmq producer.'''

import pika
import pika.exceptions


class MessageNotConfirmedError(Exception):
    '''Raised when the broker does not confirm a published message.'''


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
        self.channel.confirm_delivery()

    def send_message(self, message: str) -> None:
        '''Publish a persistent message and wait for broker confirmation.'''
        body = message.encode()
        try:
            self.channel.basic_publish(
                exchange=self.exchange,
                routing_key=self.routing_key,
                body=body,
                properties=pika.BasicProperties(delivery_mode=2),
                mandatory=True)
        except pika.exceptions.UnroutableError as err:
            raise MessageNotConfirmedError(
                'The broker could not route the message to any queue'
            ) from err
        except pika.exceptions.NackError as err:
            raise MessageNotConfirmedError(
                'The broker rejected the message (nack)'
            ) from err
        except pika.exceptions.AMQPError as err:
            raise MessageNotConfirmedError(
                'The message could not be confirmed by the broker'
            ) from err
