
#!/usr/bin/env python3

'''test producer of courses.'''

import json
import dataclasses
import os
import sys

from typing import Optional
import pytest
import pika
import pytest_mock
import course_callback

import test_credentials
import rabbitmq_connection
import producer_course
import rabbitmq_client
import rabbitmq_connection

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position

@dataclasses.dataclass
class MessageSavingCallback:
    '''class to save message'''
    message: Optional[course_callback.CourseCertificate] = None

    def __call__(self,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: pika.spec.Basic.Deliver,
                 _properties: pika.spec.BasicProperties,
                 body: bytes) -> None:
        '''Callback function to test'''
        self.message = json.loads(body.decode())
        channel.close()


# mypy has conflict with pytest decorations
@pytest.mark.parametrize(
    'params, expected',
    [
        (
            {
                'minimum_progress_for_certificate': 1,
                'course_id': 1,
                'alias': 'course1',
            },
            'minimum_progress_for_certificate',
        ),
        (
            {
                'minimum_progress_for_certificate': 1,
                'course_id': 2,
                'alias': 'course2',
            },
            'minimum_progress_for_certificate',
        ),
    ],
)  # type: ignore
def test_course_producer(mocker: pytest_mock.MockerFixture,
                         params,
                         expected) -> None:
    '''Test the message send to the course queue'''
    mocker.patch('producer_course.get_courses_from_db', return_value=params)

    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=test_credentials.MYSQL_USER,
            password=test_credentials.MYSQL_PASSWORD,
            host=test_credentials.MYSQL_HOST,
            database=test_credentials.MYSQL_DATABASE,
            port=test_credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )

    with dbconn.cursor(buffered=True, dictionary=True) as cur, \
        rabbitmq_connection.connect(username=test_credentials.OMEGAUP_USERNAME,
                                     password=test_credentials.OMEGAUP_PASSWORD,
                                     host=test_credentials.RABBITMQ_HOST) as channel:
        rabbitmq_connection.initialize_rabbitmq(
            queue='course',
            exchange='certificates',
            routing_key='CourseQueue',
            channel=channel)
        producer_course.send_course_message_to_client(cur=cur, channel=channel)
        callback = MessageSavingCallback()
        rabbitmq_client.receive_messages(queue='course',
                                         exchange='certificates',
                                         routing_key='CourseQueue',
                                         channel=channel,
                                         callback=callback)
        assert expected == callback.message
