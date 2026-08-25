#!/usr/bin/env python3
'''Unit tests for the MySQL EXPLAIN log processor.'''

from typing import Dict

import pytest

from process_mysql_explain_logs import (
    build_inefficiency_key,
    deduplicate_results,
    normalize_query,
)


def _result(
    query_id: str,
    normalized_query: str,
    table: str,
) -> Dict[str, str]:
    return {
        'Query ID': query_id,
        'Normalized Query': normalized_query,
        'Table': table,
    }


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


def test_inefficiency_key_does_not_depend_on_query_id() -> None:
    '''Log ordering does not affect an inefficiency identity.'''
    first = _result('1', 'SELECT * FROM Users', 'Users')
    second = _result('20', 'SELECT * FROM Users', 'Users')

    assert build_inefficiency_key(first) == build_inefficiency_key(second)


def test_inefficiency_key_includes_table() -> None:
    '''Different problematic tables have different identities.'''
    users = _result('1', 'SELECT * FROM Users JOIN Courses', 'Users')
    courses = _result('1', 'SELECT * FROM Users JOIN Courses', 'Courses')

    assert build_inefficiency_key(users) != build_inefficiency_key(courses)


def test_deduplicate_results_uses_query_and_table() -> None:
    '''Only duplicate records for the same query and table are removed.'''
    first = _result('1', 'SELECT * FROM Users JOIN Courses', 'Users')
    duplicate = _result('2', 'SELECT * FROM Users JOIN Courses', 'Users')
    other_table = _result(
        '2',
        'SELECT * FROM Users JOIN Courses',
        'Courses',
    )

    assert deduplicate_results([
        first,
        duplicate,
        other_table,
    ]) == [first, other_table]
