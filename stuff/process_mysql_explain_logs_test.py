#!/usr/bin/env python3
'''Unit tests for the MySQL EXPLAIN log processor.'''

import pytest

from process_mysql_explain_logs import normalize_query


@pytest.mark.parametrize(
    ('query', 'expected'),
    [
        (
            'SELECT * FROM Users WHERE user_id = 123',
            'SELECT * FROM Users WHERE user_id = ?',
        ),
        (
            "SELECT * FROM Users WHERE username = 'alice'",
            'SELECT * FROM Users WHERE username = ?',
        ),
        (
            'SELECT * FROM Users WHERE username = "alice"',
            'SELECT * FROM Users WHERE username = ?',
        ),
    ],
)
def test_normalize_query_replaces_literals(
    query: str,
    expected: str,
) -> None:
    '''Literal values are replaced with placeholders.'''
    assert normalize_query(query) == expected


def test_normalize_query_collapses_whitespace() -> None:
    '''Equivalent formatting produces the same normalized query.'''
    single_line = 'SELECT * FROM Users WHERE user_id = 10'
    multiline = '''
        SELECT  *
        FROM Users
        WHERE user_id = 20
    '''

    expected = 'SELECT * FROM Users WHERE user_id = ?'
    assert normalize_query(single_line) == expected
    assert normalize_query(multiline) == expected


def test_normalize_query_preserves_query_structure() -> None:
    '''Structurally different queries remain different.'''
    select_all = 'SELECT * FROM Users WHERE user_id = 10'
    select_username = 'SELECT username FROM Users WHERE user_id = 10'

    assert normalize_query(select_all) != normalize_query(select_username)
