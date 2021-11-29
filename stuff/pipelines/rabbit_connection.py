#!/usr/bin/python3

'''Send messages to Contest queue in rabbitmq'''

import argparse
import datetime
import pika


class Rabbit:
    '''Class to connect rabbitmq'''
    def __init__(self, args: argparse.Namespace) -> None:
        '''Connects to rabbitmq with the arguments provided.'''
        username = args.rabbitmq_username
        password = args.rabbitmq_password
        credentials = pika.PlainCredentials(username, password)
        parameters = pika.ConnectionParameters('rabbitmq',
                                               5672, '/', credentials)
        self.connection = pika.BlockingConnection(parameters)
        self.channel = self.connection.channel()
        self.channel.exchange_declare(exchange='certificates',
                                      exchange_type='direct')

    def close(self) -> None:
        '''Close connection'''
        self.connection.close()


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
