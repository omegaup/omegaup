#!/usr/bin/python3

'''Processing course messages.'''

import argparse
import logging
import os
import sys

import omegaup.api
import pika

import course_callback
import rabbitmq_connection
import rabbitmq_client

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    parser.add_argument('--user-username', type=str, help='omegaup username')
    parser.add_argument('--user-password', type=str, help='omegaup password')
    parser.add_argument('--api-token', type=str, help='omegaup api token')
    parser.add_argument('--url',
                        type=str,
                        help='omegaup api URL',
                        default='https://omegaup.com')

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    try:
        with rabbitmq_connection.connect(
            username=args.rabbitmq_username,
            password=args.rabbitmq_password,
            host=args.rabbitmq_host
        ) as channel:
            client = omegaup.api.Client(api_token=args.api_token, url=args.url)
            callback = course_callback.CourseCallback(
                dbconn=dbconn.conn,
                client=client,
            )
            rabbitmq_client.receive_messages(queue='course',
                                             exchange='certificates',
                                             routing_key='CourseQueue',
                                             channel=channel,
                                             callback=callback)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
