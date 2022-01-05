#!/usr/bin/python3

'''test verification_code module.'''

import os

import argparse
import logging
import sys
import mysql.connector
import mysql.connector.cursor
import rabbitmq_connection
import client_contest
import test_credentials

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def test_client_contest() -> None:
    '''Test checksum digit'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    os.system('python3 send_messages_contest_queue.py')
    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(args) as channel:
        client_contest.certificate_contests_receive_messages(
            cur,
            dbconn.conn,
            channel,
            test_credentials.API_TOKEN,
            test_credentials.OMEGAUP_API_ENDPOINT)
        client_contest.close_channel(channel)
