#!/usr/bin/python3

'''Processing course messages.'''

import argparse
import dataclasses
import logging
import os
import sys
import json
from dataclasses import dataclass
from typing import List
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


@dataclass
class Certificate:
    '''A dataclass for certificate.'''
    certificate_type: str
    course_id: int
    verification_code: str
    username: str


def certificate_course_receive_messages(
        cur: MySQLdb.cursors.BaseCursor,
        dbconn: MySQLdb.connections.Connection,
        channel: pika.adapters.blocking_connection.BlockingChannel,
        args: argparse.Namespace) -> None:
    '''Receive courses messages'''

    result = channel.queue_declare(queue='', exclusive=True)
    queue_name = result.method.queue
    assert queue_name is not None
    channel.queue_bind(
        exchange='certificates',
        queue=queue_name,
        routing_key='CourseQueue')
    logging.info('[*] waiting for the messages')

    def certificate_course_callback(
            _channel: pika.adapters.blocking_connection.BlockingChannel,
            _method: pika.spec.Basic.Deliver,
            _properties: pika.spec.BasicProperties,
            body: bytes) -> None:
        '''Function to receive messages'''
        data = json.loads(body.decode())
        client = omegaup.api.Client(api_token=args.api_token, url=args.url)
        login = client.user.login(
            password=args.password,
            usernameOrEmail=args.username,
        )
        result = client.course.studentsProgress(
            auth_token=login['auth_token'],
            course=data['alias'],
        )
        progress = result['progress']

        certificates: List[Certificate] = []

        for user in progress:
            minimum_progress = data['minimum_progress_for_certificate']
            if user['courseProgress'] < minimum_progress:
                continue
            verification_code = generate_code()
            certificates.append(Certificate(
                certificate_type='course',
                course_id=int(data['course_id']),
                verification_code=verification_code,
                username=str(user['username']),
            ))
        while True:
            try:
                cur.execute('''
                    INSERT INTO
                        `Certificates` (
                            `identity_id`,
                            `certificate_type`,
                            `course_id`,
                            `verification_code`)
                    SELECT
                        `identity_id`,
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
                    'At least one of the verification codes had a conflict')
                dbconn.rollback()
    channel.basic_consume(
        queue=queue_name,
        on_message_callback=certificate_course_callback,
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
            certificate_course_receive_messages(cur, dbconn, channel, args)
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
