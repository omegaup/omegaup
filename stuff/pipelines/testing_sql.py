#!/usr/bin/python3

'''Send message to coder_month_client'''


import argparse
import logging
import os
import sys
import json
from typing import Optional, Dict, Any
from rabbitmq_database import get_coder_of_the_month
from rabbitmq_producer import RabbitmqProducer
import mysql.connector
import mysql.connector.cursor
import pika
import rabbitmq_connection
from verification_code import generate_code
from mysql.connector import errors
import rabbitmq_database

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    rabbitmq_connection.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    data = {'user_id': 3, 'time': '2022-01-26', 'category': 'all'}
    try:
        with dbconn.cursor(buffered=True, dictionary=True) as cur:
             rabbitmq_database.insert_coder_of_the_month(data, cur, dbconn.conn)
    finally:
        dbconn.conn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
