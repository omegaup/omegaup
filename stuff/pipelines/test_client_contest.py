#!/usr/bin/python3

'''test verification_code module.'''

import os

import argparse
import logging
import sys
import MySQLdb
import MySQLdb.cursors
import rabbitmq_connection
import client_contest

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

    parser.add_argument('--api-token',
                        type=str,
                        help='omegaup api token',
                        default='xxxx')
    parser.add_argument('--url',
                        type=str,
                        help='omegaup api URL',
                        default='https://omegaup.com')

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    os.system('python3 send_messages_contest_queue.py')
    client = client_contest.ClientContest('contest',
                                          'certificates',
                                          'ContestQueue')
    with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur, \
        rabbitmq_connection.connect(args) as channel:
        client.certificate_contests_receive_messages(cur,
                                                     dbconn,
                                                     channel,
                                                     args)
        assert client.message == [{
            'alias': 'prueba',
            'scoreboard_ur': 'abcde',
            'contest_id': 1,
            'certificate_cutoff': 3
        }]
