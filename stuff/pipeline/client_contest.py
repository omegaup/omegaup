#!/usr/bin/python3

'''Processing contest messages.'''

import argparse
import logging
import os
import sys
import json
import random
import MySQLdb
import MySQLdb.cursors
import pika

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def generate_code() -> str:
    '''Generate an aleatory code'''
    diccionary_alfabeth = {"2": 0, "3": 1, "4": 2, "5": 3,
                           "6": 4, "7": 5, "8": 6, "9": 7,
                           "C": 8, "F": 9, "G": 10, "H": 11,
                           "J": 12, "M": 13, "P": 14, "Q": 15,
                           "R": 16, "V": 17, "W": 18, "X": 19}
    code_alfabeth = "23456789CFGHJMPQRVWX"
    code_generate = ''.join(random.choices(code_alfabeth, k = 9))
    sum_values = 0
    for i in range(1, 10):
        sum_values += i * diccionary_alfabeth[code_generate[i - 1]]
    sum_values = sum_values % 20
    code_generate += list(diccionary_alfabeth.keys())[sum_values]
    return code_generate


def receive_contest_messages(
        cur: MySQLdb.cursors.BaseCursor,
        dbconn: MySQLdb.connections.Connection,
        # pylint: disable=unused-argument,
        rabbit_user: str,
        rabbit_password: str) -> None:
    '''Receive contest messages'''

    credentials = pika.PlainCredentials(rabbit_user, rabbit_password)
    parameters = pika.ConnectionParameters('rabbitmq', 5672, '/', credentials)
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    channel.exchange_declare(exchange='logs_exchange', exchange_type='direct')
    result = channel.queue_declare(queue='', exclusive=True)
    queue_name = result.method.queue
    assert queue_name is not None
    channel.queue_bind(
        exchange='logs_exchange',
        queue=queue_name,
        routing_key="ContestQueue")
    logging.info('[*] waiting for the messages')

    def callback(channel: pika.adapters.blocking_connection.BlockingChannel,
                 method: pika.spec.Basic.Deliver,
                 properties: pika.spec.BasicProperties,
                 # pylint: disable=unused-argument,
                 body: bytes) -> None:
        data = json.loads(body.decode())
        cur.execute('''
                SELECT
                    COUNT(*) AS `count`
                FROM
                    `Contests`
                WHERE
                    `contest_id` = %s AND
                    `finish_time` >= NOW();
                ''', (data["contest_id"]))
        for row in cur:
            if row['count'] > 0:
                logging.info('Skipping because already contest is not finish')
                return
        cur.execute('''
                SELECT
                    certificate_cutoff
                FROM
                    `Contests`
                WHERE
                    `contest_id` = %s;
                ''', (data["contest_id"]))
        for row in cur:
            cur.execute('''
                SELECT
                    user_id
                FROM
                    `Contest_Log`
                WHERE
                    `contest_id` = %s
                ORDER BY time DESC
                LIMIT %s;
                ''', (data["contest_id"], row["certificate_cutoff"]))
            for users in cur:
                code_verification = generate_code()
                cur.execute('''
                    INSERT INTO
                        `Certificates` (`identity_id`,
                                     `certificate_type`,
                                     `contest_id`,
                                     `verification_code`)
                    VALUES(%s, %s, %s, %s);''',
                            (users["user_id"], 'contest', data["contest_id"],
                             code_verification))
                dbconn.commit()
    channel.basic_consume(
        queue=queue_name,
        on_message_callback=callback,
        auto_ack=True)
    channel.start_consuming()


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    parser.add_argument('--user_rabbit')
    parser.add_argument('--password_rabbit')
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            receive_contest_messages(
                cur, dbconn, args.user_rabbit,
                args.password_rabbit)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
