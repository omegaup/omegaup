#!/usr/bin/python3

'''Processing contest messages.'''

import argparse
import logging
import os
import sys
import json
from typing import Any, List, Optional, Tuple
import MySQLdb
import MySQLdb.cursors
import pika
from verification_code import generate_code
import rabbitmq_connection

import omegaup.api   # pylint: disable=import-error

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


def certificate_contests_receive_messages(
        cur: MySQLdb.cursors.BaseCursor,
        dbconn: MySQLdb.connections.Connection,
        channel: pika.adapters.blocking_connection.BlockingChannel) -> None:
    '''Receive contest messages'''
    result = channel.queue_declare(queue='', exclusive=True)
    queue_name = result.method.queue
    assert queue_name is not None
    channel.queue_bind(
        exchange='certificates',
        queue=queue_name,
        routing_key="ContestQueue")
    logging.info('[*] waiting for the messages')

    def certificate_contests_callback(
            _channel: pika.adapters.blocking_connection.BlockingChannel,
            _method: pika.spec.Basic.Deliver,
            _properties: pika.spec.BasicProperties,
            body: bytes) -> None:
        data = json.loads(body.decode())
        client = omegaup.api.Client(
            api_token='01fef0b0d78f56be29d42a25dacc69b743158039')
        scoreboard = client.contest.scoreboard(contest_alias=data['alias'],
                                               token=data['scoreboard_url'])
        ranking = scoreboard['ranking']
        certificates: List[Tuple[str, Any, str, Optional[Any], Any]] = []
        for user in ranking:
            contest_place: Optional[int] = None
            if (data['certificate_cutoff']
                    and user['place'] <= data['certificate_cutoff']):
                contest_place = user['place']
            verification_code = generate_code()
            certificates.append((
                'contest', data['contest_id'], verification_code,
                contest_place, user['username']
            ))
        cur.executemany('''
            INSERT INTO
                `Certificates` (
                    `identity_id`,
                    `certificate_type`,
                    `contest_id`,
                    `verification_code`,
                    `contest_place`)
            SELECT
                `identity_id`,
                %s,
                %s,
                %s,
                %s
            FROM
                `Identities`
            WHERE
                `username` = %s;
            ''', certificates)
        dbconn.commit()
    channel.basic_consume(
        queue=queue_name,
        on_message_callback=certificate_contests_callback,
        auto_ack=False)
    try:
        channel.start_consuming()
    except KeyboardInterrupt:
        channel.stop_consuming()


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
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur, \
            rabbitmq_connection.connect(args) as channel:
            certificate_contests_receive_messages(cur, dbconn, channel)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
