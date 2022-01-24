#!/usr/bin/python3

'''test verification_code module.'''

import os
import argparse
import logging
import sys
from pytest_mock import MockerFixture
import basic_client
import rabbitmq_connection
import send_messages_coder_of_month_queue


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def test_coder_of_the_month_queue(mocker: MockerFixture) -> None:
    '''Test the message send to the coder of the month queue'''
    mocker.patch('send_messages_coder_of_month_queue.get_coder_of_the_month',
                 return_value='Example')
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    rabbitmq_connection.configure_parser(parser)
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    # os.system('python3 send_messages_coder_of_month_queue.py')
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur, \
            rabbitmq_connection.connect(args) as channel:
            send_messages_coder_of_month_queue.send_coder_month(
                cur, channel, 'all')
            # send_messages_coder_of_month_queue.send_coder_month(
            #    cur, channel, 'female')
            callback = basic_client.ClientCallback()
            basic_client.receive_messages('coder_month',
                                          'certificates',
                                          'CoderOfTheMonthQueue',
                                          channel,
                                          callback)
            assert callback.message == 'Example'
    finally:
        dbconn.conn.close()
        logging.info('Done')
