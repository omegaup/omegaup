#!/usr/bin/python3

'''Send messages to queues in rabbitmq'''

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
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def send_messages(cur: MySQLdb.cursors.BaseCursor,
                  rabbit_user: str,
                  rabbit_password: str) -> None:

    '''Send messages to queues'''
    credentials = pika.PlainCredentials(rabbit_user, rabbit_password)
    parameters = pika.ConnectionParameters('rabbitmq', 5672, '/', credentials)
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    channel.exchange_declare(exchange='logs_exchange', exchange_type='direct')
    logging.info('Send messages to Course_Queue')
    cur.execute(
        '''
        SELECT
            Courses.course_id, Groups_Identities.identity_id
        FROM
            Courses
        INNER JOIN
            Groups_Identities ON Courses.group_id=Groups_Identities.group_id;
        '''
    )
    for row in cur:
        data = {"course_id": str(row['course_id']),
                "identity_id": str(row['identity_id'])}
        message = json.dumps(data)
        body = message.encode()
        channel.basic_publish(
            exchange='logs_exchange',
            routing_key='CourseQueue',
            body=body)
    logging.info('Send messages to Contest_Queue')
    cur.execute(
        '''
        SELECT
            contest_id
        FROM
            Contests;
        '''
    )
    for row in cur:
        data = {"contest_id": str(row['contest_id'])}
        message = json.dumps(data)
        body = message.encode()
        channel.basic_publish(
            exchange='logs_exchange',
            routing_key='ContestQueue',
            body=body)
    logging.info('Send messages to Coder_Month_Queue')
    cur.execute(
        '''
        SELECT
            user_id, time, category
        FROM
            Coder_Of_The_Month;
        '''
    )
    for row in cur:
        data = {"user_id": str(row['user_id'])}
        data = {"time": row['time']}
        data = {"category": row['category']}
        message = json.dumps(data)
        body = message.encode()
        channel.basic_publish(
            exchange='logs_exchange',
            routing_key='CoderMonthQueue',
            body=body)
    connection.close()


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    parser.add_argument('--user_rabbit')
    parser.add_argument('--password_rabbit')

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    print(args.user_rabbit)

    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            send_messages(cur, args.user_rabbit, args.password_rabbit)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()
