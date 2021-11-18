#!/usr/bin/python3

'''Processing contest messages.'''

import argparse
import logging
import os
import sys
import json
import MySQLdb
import MySQLdb.cursors
import pika
from verification_code import generate_code

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def is_contest_finish(
        cur: MySQLdb.cursors.BaseCursor,
        contest_id: str) -> bool:
    '''verificate if contest is finish'''
    cur.execute('''
                SELECT
                    COUNT(*) AS `count`
                FROM
                    `Contests`
                WHERE
                    `contest_id` = %s AND
                    `finish_time` >= NOW();
                ''', (contest_id))
    for row in cur:
        if row['count'] > 0:
            logging.info('Skipping because already contest is not finish')
            return True
    return False


def selection_cuttoff_contest(
        cur: MySQLdb.cursors.BaseCursor,
        contest_id: str) -> str:
    '''selecting cuttoff from a contest'''
    cur.execute('''
                SELECT
                    `certificate_cutoff`
                FROM
                     `Contests`
                WHERE
                    `contest_id` = %s
                ''', (contest_id))
    for row in cur:
        cutoff = str(row['certificate_cutoff'])
    return cutoff


def verificate_certificate(
        cur: MySQLdb.cursors.BaseCursor,
        user_id: str,
        contest_id: str) -> bool:
    '''verificate if certificate exist'''
    cur.execute('''
                SELECT
                    COUNT(*) AS `count`
                FROM
                    `Certificates`
                WHERE
                    `identity_id` = %s AND
                    `contest_id` = %s;
                ''', (user_id, contest_id))
    for row in cur:
        if row['count'] > 0:
            logging.info('Exist certificates')
            return False
    return True


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
        if is_contest_finish(cur, data["contest_id"]):
            return
        cur.execute('''
                SELECT
                    COUNT(*) AS `count`
                FROM
                    `Contests`
                WHERE
                    `contest_id` = %s AND
                    certificate_cutoff IS NULL;
                ''', (data["contest_id"]))
        for row in cur:
            if row['count'] > 0:
                logging.info('Contest no has places')
                cur.execute('''
                    SELECT
                        user_id
                    FROM
                        `Contest_Log`
                    WHERE
                        `contest_id` = %s;
                    ''', (data["contest_id"]))
                for users in cur:
                    if verificate_certificate(cur,
                                              users["user_id"],
                                              data["contest_id"]):
                        code_verification = generate_code()
                        cur.execute('''
                            INSERT INTO
                                 `Certificates` (`identity_id`,
                                            `certificate_type`,
                                            `contest_id`,
                                            `verification_code`)
                            VALUES(%s, %s, %s, %s);''',
                                    (users["user_id"],
                                     'contest', data["contest_id"],
                                     code_verification))
                        dbconn.commit()
            else:
                logging.info('Contest has places')
                cur.execute('''
                    SELECT
                        `certificate_cutoff`
                    FROM
                         `Contests`
                    WHERE
                        `contest_id` = %s
                ''', (data["contest_id"]))

                cutoff = int(selection_cuttoff_contest(cur,
                                                       data["contest_id"]))
                cur.execute('''
                    SELECT
                        user_id
                    FROM
                        `Contest_Log`
                    WHERE
                        `contest_id` = %s
                    ORDER BY time DESC;
                    ''', (data["contest_id"]))
                place = 1
                for users in cur:
                    if verificate_certificate(cur,
                                              users["user_id"],
                                              data["contest_id"]):
                        code_verification = generate_code()
                        if cutoff >= place:
                            cur.execute('''
                                INSERT INTO
                                    `Certificates` (`identity_id`,
                                                `certificate_type`,
                                                `contest_id`,
                                                `verification_code`,
                                                `contest_place`)
                                VALUES(%s, %s, %s, %s,%s);''',
                                        (users["user_id"],
                                         'contest', data["contest_id"],
                                         code_verification, str(place)))
                            dbconn.commit()
                            place += 1
                        else:
                            cur.execute('''
                                INSERT INTO
                                    `Certificates` (`identity_id`,
                                                `certificate_type`,
                                                `contest_id`,
                                                `verification_code`)
                                VALUES(%s, %s, %s, %s);''',
                                        (users["user_id"],
                                         'contest', data["contest_id"],
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
