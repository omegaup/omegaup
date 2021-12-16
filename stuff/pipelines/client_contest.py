#!/usr/bin/python3

'''Processing contest messages.'''

import argparse
import dataclasses
import logging
import os
import sys
import json
from typing import List, Optional, Dict
import omegaup.api
import MySQLdb
import MySQLdb.cursors
import pika
from verification_code import generate_code
import rabbitmq_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.'))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


@dataclasses.dataclass
class Certificate:
    '''A dataclass for certificate.'''
    certificate_type: str
    contest_id: int
    verification_code: str
    contest_place: Optional[int]
    username: str


class ClientContest:
    '''Client Contest class'''
    def __init__(self, queue: str, exchange: str, routing_key: str) -> None:
        self.message: List[Dict[str, object]] = []
        self.queue = queue
        self.exchange = exchange
        self.routing_key = routing_key

    def certificate_contests_receive_messages(
            self,
            cur: MySQLdb.cursors.BaseCursor,
            dbconn: MySQLdb.connections.Connection,
            channel: pika.adapters.blocking_connection.BlockingChannel,
            args: argparse.Namespace) -> None:
        '''Receive contest messages from a queue'''

        channel.exchange_declare(exchange=self.exchange,
                                 durable=True,
                                 exchange_type='direct')
        channel.queue_declare(queue=self.queue, durable=True, exclusive=True)
        channel.queue_bind(
            exchange=self.exchange,
            queue=self.queue,
            routing_key=self.routing_key)
        logging.info('[*] waiting for the messages')

        def certificate_contests_callback(
                _channel: pika.adapters.blocking_connection.BlockingChannel,
                _method: pika.spec.Basic.Deliver,
                _properties: pika.spec.BasicProperties,
                body: bytes) -> None:
            data = json.loads(body.decode())
            client = omegaup.api.Client(api_token=args.api_token, url=args.url)
            scoreboard = client.contest.scoreboard(
                contest_alias=data['alias'],
                token=data['scoreboard_url'])
            ranking = scoreboard['ranking']
            certificates: List[Certificate] = []

            for user in ranking:
                contest_place: Optional[int] = None
                if (data['certificate_cutoff']
                        and user['place'] <= data['certificate_cutoff']):
                    contest_place = user['place']
                verification_code = generate_code()
                certificates.append(Certificate(
                    certificate_type='contest',
                    contest_id=int(data['contest_id']),
                    verification_code=verification_code,
                    contest_place=contest_place,
                    username=str(user['username'])
                ))
            while True:
                try:
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
                        ''',
                                    [
                                        dataclasses.astuple(
                                            certificate
                                        ) for certificate in certificates])
                    dbconn.commit()
                    break
                except:  # noqa: bare-except
                    for certificate in certificates:
                        certificate.verification_code = generate_code()
                    logging.exception(
                        'At least one of the verification codes had a conflict'
                    )
                    dbconn.rollback()
        channel.basic_consume(
            queue=self.queue,
            on_message_callback=certificate_contests_callback,
            auto_ack=True)
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

    parser.add_argument('--api-token', type=str, help='omegaup api token')
    parser.add_argument('--url',
                        type=str,
                        help='omegaup api URL',
                        default='https://omegaup.com')

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)
    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur, \
            rabbitmq_connection.connect(args) as channel:
            client = ClientContest('contest', 'certificates', 'ContestQueue')
            client.certificate_contests_receive_messages(cur,
                                                         dbconn,
                                                         channel,
                                                         args)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
