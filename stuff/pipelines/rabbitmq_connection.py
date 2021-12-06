#!/usr/bin/python3

'''Send messages to queues in rabbitmq'''

import contextlib

from typing import Iterator

import argparse
import datetime
import pika


@contextlib.contextmanager
def connect(
        args: argparse.Namespace
) -> Iterator[pika.adapters.blocking_connection.BlockingChannel]:
    '''Connects to rabbitmq with the arguments provided.'''
    username = args.rabbitmq_username
    password = args.rabbitmq_password
    credentials = pika.PlainCredentials(username, password)
    parameters = pika.ConnectionParameters('rabbitmq',
                                           5672,
                                           '/',
                                           credentials,
                                           heartbeat=600,
                                           blocked_connection_timeout=300.0)
    connection = pika.BlockingConnection(parameters)
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
