#!/usr/bin/env python3

'''Send messages to queues in rabbitmq'''

import contextlib

from typing import Iterator

import argparse
import datetime
import pika


@contextlib.contextmanager
def connect(
        *, username: str, password: str, host: str
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
        connection.close()


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
    parser.add_argument('--date-lower-limit',
                        type=lambda s:
                        datetime.datetime.strptime(s, '%Y-%m-%d'),
                        help='date lower limit',
                        default=datetime.date(2005, 1, 1))
    parser.add_argument('--date-upper-limit',
                        type=lambda s:
                        datetime.datetime.strptime(s, '%Y-%m-%d'),
                        help='date upper limit',
                        default=datetime.date.today())
