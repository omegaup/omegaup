#!/usr/bin/python3

'''test verification_code module.'''

import os

import argparse
import logging
import sys
import rabbitmq_connection
import client_contest
from pytest_mock import MockerFixture
import test_credentials

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def test_client_contest() -> None:
    '''Basic test for client contest queue.'''
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
        cur.execute('TRUNCATE TABLE `Certificates`;')
        dbconn.conn.commit()

        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] == 0

        client_contest.certificate_contests_receive_messages(
            cur,
            dbconn.conn,
            channel,
            test_credentials.API_TOKEN,
            test_credentials.OMEGAUP_API_ENDPOINT)
        cur.execute('SELECT COUNT(*) AS count FROM `Certificates`;')
        count = cur.fetchone()
        assert count['count'] > 0


def test_client_contest_with_duplicated_codes(mocker: MockerFixture) -> None:
    '''Test client contest queue when a code already exists'''
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
        mocker.patch('client_contest.generate_code', return_value='XMCF384X8X')
        client_contest.certificate_contests_receive_messages(
            cur,
            dbconn.conn,
            channel,
            test_credentials.API_TOKEN,
            test_credentials.OMEGAUP_API_ENDPOINT)
