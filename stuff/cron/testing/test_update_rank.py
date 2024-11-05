#!/usr/bin/env python3

'''Unittest for the update rank script.'''

import datetime
from unittest.mock import MagicMock
import pytest
import cron.update_ranks


@pytest.fixture
def mock_main_cursor() -> MagicMock:
    '''Fixture to create a mock cursor.'''
    return MagicMock()


@pytest.fixture
def mock_readonly_cursor() -> MagicMock:
    '''Fixture to create a mock readonly cursor.'''
    return MagicMock()


@pytest.fixture
def first_day_of_current_month() -> datetime.date:
    '''Fixture to provide the first day of the current month.'''
    return datetime.date(2024, 10, 1)


def test_update_coder_of_the_month_candidates(
    # pylint: disable=redefined-outer-name
    mock_main_cursor: MagicMock,
    mock_readonly_cursor: MagicMock,
    first_day_of_current_month: datetime.date,
) -> None:
    '''Test the update_coder_of_the_month_candidates function.'''
    category = 'female'

    # Mock the execute and fetchall methods
    mock_main_cursor.execute.return_value = None
    mock_main_cursor.fetchall.return_value = [{'count': 0}]
    mock_readonly_cursor.execute.return_value = None
    mock_readonly_cursor.fetchall.return_value = [
        {'user_id': 1, 'username': 'user1', 'country_id': 'xx', 'school_id': 1,
         'ProblemsSolved': 10, 'score': 100, 'classname': 'user-rank-expert'},
        {'user_id': 2, 'username': 'user2', 'country_id': 'xx', 'school_id': 2,
         'ProblemsSolved': 8, 'score': 80, 'classname': 'user-rank-specialist'}
    ]

    # Call the function
    cron.update_ranks.update_coder_of_the_month_candidates(
        cur=mock_main_cursor,
        cur_readonly=mock_readonly_cursor,
        first_day_of_current_month=first_day_of_current_month,
        category=category
    )

    # Assertions to verify the behavior
    mock_main_cursor.execute.assert_called()
    mock_readonly_cursor.execute.assert_called()
    assert mock_main_cursor.execute.call_count > 0
    assert mock_readonly_cursor.execute.call_count > 0
