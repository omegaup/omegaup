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


def receive_contest_messages(
        cur: MySQLdb.cursors.BaseCursor,
        dbconn: MySQLdb.connections.Connection,
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

    def callback(_channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: pika.spec.Basic.Deliver,
                 _properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        data = json.loads(body.decode())
        cur.execute('''
                SELECT
                    @n := @n + 1 place,
                    r.contest_score,
                    r.username,
                    r.identity_id,
                    c.certificate_cutoff,
                    ce.certificate_id
                FROM
                    (
                        SELECT
                            SUM(r.contest_score) AS contest_score,
                            i.username,
                            i.identity_id,
                            pp.problemset_id
                        FROM
                            Problemset_Problems pp
                        INNER JOIN
                            Submissions s
                        ON
                            s.problemset_id = pp.problemset_id
                        INNER JOIN
                            Runs r
                        ON
                            s.current_run_id = r.run_id
                        INNER JOIN
                            Identities i
                        ON
                            i.identity_id = s.identity_id
                        INNER JOIN
                            Contests co
                        ON
                            pp.problemset_id = co.problemset_id
                        WHERE
                            co.contest_id = %s
                            AND r.status = 'ready'
                            AND s.type = 'normal'
                            AND r.verdict NOT IN ('CE', 'JE', 'VE')
                        GROUP BY
                            s.identity_id
                        ORDER BY
                            contest_score DESC
                    ) AS r
                INNER JOIN
                    Contests c
                ON
                    c.problemset_id = r.problemset_id
                LEFT JOIN
                    Certificates ce
                ON
                    r.identity_id = ce.identity_id
                    AND c.contest_id = ce.contest_id
                CROSS JOIN
                    (SELECT @n := 0) AS temp
                WHERE
                    certificate_id IS NULL;
                ''', (data['contest_id'],))
        certificates = []
        for row in cur:
            contest_place = None
            if row['certificate_cutoff'] is None:
                logging.info('The contest has no places')
            elif row['place'] > row['certificate_cutoff']:
                logging.info('The user did not reach the place to be reported')
            else:
                contest_place = row['place']
            code_verification = generate_code()
            certificates.append((
                row['identity_id'], 'contest', data['contest_id'],
                code_verification, contest_place
            ))
        cur.executemany('''
            INSERT INTO
                `Certificates` (
                    `identity_id`,
                    `certificate_type`,
                    `contest_id`,
                    `verification_code`,
                    `contest_place`)
            VALUES(%s, %s, %s, %s, %s);
            ''', certificates)
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
    parser.add_argument('--rabbitmq-user')
    parser.add_argument('--rabbitmq-password')
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            receive_contest_messages(
                cur, dbconn, args.rabbitmq_user,
                args.rabbitmq_password)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
