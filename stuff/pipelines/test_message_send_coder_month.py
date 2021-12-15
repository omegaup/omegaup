#!/usr/bin/python3

'''test verification_code module.'''

import pytest
import os
import BasicClient
import rabbitmq_connection
import argparse
import logging
import os
import sys
import json
import MySQLdb
import MySQLdb.cursors
import pika

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

def test_coder_of_the_month_queue() -> None:
    '''Test the message send to the coder of the month queue'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    rabbitmq_connection.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    os.system('python3 send_messages_coder_of_month_queue.py')
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur, \
            rabbitmq_connection.connect(args) as channel:
            client = BasicClient.BasicClient('coder_month',
                                 'certificates',
                                 'CoderOfTheMonthQueue')
            client.receive_messages(cur, dbconn, channel)
            assert client.message == 'Example'
    finally:
        dbconn.close()
        logging.info('Done')