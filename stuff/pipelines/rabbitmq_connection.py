#!/usr/bin/env python3

'''Send messages to queues in rabbitmq'''

import contextlib

from typing import Iterator

import argparse
import logging
import pika


@contextlib.contextmanager
def connect(
        *, username: str, password: str, host: str, for_testing: bool = False
) -> Iterator[pika.adapters.blocking_connection.BlockingChannel]:
    '''Connects to rabbitmq with the arguments provided.'''
    connection = pika.BlockingConnection(pika.ConnectionParameters(
        host=host,
        port=5672,
        virtual_host='/',
        credentials=pika.PlainCredentials(username, password),
        heartbeat=600,
        # mypy does not support structural typing yet
        # https://github.com/python/mypy/issues/3186
        blocked_connection_timeout=300.0,  # type: ignore
    ))
    channel = connection.channel()

    channel.exchange_declare(exchange='certificates',
                             exchange_type='direct',
                             durable=True)
    try:
        yield channel
    finally:
        if not for_testing:
            connection.close()
        else:
            logging.info('Avoiding close the connection for testing purposes')


def configure_parser(parser: argparse.ArgumentParser) -> None:
    '''Add rabbitmq-related arguments to `parser`'''
    parser.add_argument('--rabbitmq-username', type=str,
                        help='rabbitmq username',
                        default='omegaup')
    parser.add_argument('--rabbitmq-password', type=str,
                        help='rabbitmq password',
                        default='omegaup')
    parser.add_argument('--rabbitmq-host', type=str,
                        help='rabbitmq host',
                        default='rabbitmq')


def initialize_rabbitmq(
        *,
        queue: str,
        exchange: str,
        routing_key: str,
        channel: pika.adapters.blocking_connection.BlockingChannel
) -> None:
    '''initializes the queue and exchange'''
    channel.queue_declare(
        queue=queue, passive=False,
        durable=True, exclusive=False,
        auto_delete=False)
    channel.exchange_declare(
        exchange=exchange,
        auto_delete=False,
        durable=True,
        exchange_type='direct')
    channel.queue_bind(exchange=exchange,
                       queue=queue,
                       routing_key=routing_key)
