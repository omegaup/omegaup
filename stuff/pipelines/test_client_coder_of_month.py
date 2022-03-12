#!/usr/bin/python3

'''test verification_code module.'''


import pytest
from pytest_mock import MockerFixture
import rabbitmq_connection
from producer_coder_of_month import send_message_client
import client_coder_of_month
from rabbitmq_database import new_message
from rabbitmq_connection import initialize_rabbitmq


# mypy has conflict with pytest decorations
@pytest.mark.parametrize(
    'params, expected',
    [
        ({'user_id': 1, 'time': '2022-01-26',
          'category': 'all'},
         {'user_id': 1, 'time': '2022-01-26',
          'category': 'all'}),
        ({'user_id': 1, 'time': '2022-01-26',
          'category': 'female'},
         {'user_id': 1, 'time': '2022-01-26',
          'category': 'female'}),
    ],
)  # type: ignore
def test_client_coder_of_the_month_queue(mocker: MockerFixture,
                                         params, expected) -> None:
    '''Test client receive message'''
    mocker.patch('producer_coder_of_month.get_coder_of_the_month',
                 return_value=params)
    with rabbitmq_connection.connect(username='omegaup',
                                     password='omegaup',
                                     host='rabbitmq') as channel:
        initialize_rabbitmq('coder_month',
                            'certificates',
                            'CoderOfTheMonthQueue',
                            channel)
        send_message_client(channel)
        client_coder_of_month.client(channel)
        assert expected == new_message.message
