#!/usr/bin/python3

'''
Processing contest messages for certificates. Once  the connection with
rabbitmq is established, an exchange is created and then, the queue is created
and declarated, so the queue is binded to the specified exchange. Finally, the
queue is consumed for the consumer_tag to the consumer callback.
'''

import argparse
import logging
import os
import sys

import contest_callback
import rabbitmq_connection
import rabbitmq_client

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def main() -> None:
    '''
    Main entrypoint for the client contest.
    '''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

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
            callback = contest_callback.ContestsCallback(dbconn=dbconn.conn)
            rabbitmq_client.receive_messages(queue='contest',
                                             exchange='certificates',
                                             routing_key='ContestQueue',
                                             channel=channel,
                                             callback=callback)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
