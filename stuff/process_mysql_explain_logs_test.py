#!/usr/bin/env python3
'''Unit tests for the MySQL EXPLAIN log processor.'''

from typing import Dict

import pytest

from process_mysql_explain_logs import (
    build_inefficiency_key,
    build_query_family,
    deduplicate_results,
    normalize_query,
    sort_results_for_csv,
)


def _result(
    query_id: str,
    normalized_query: str,
    table: str,
) -> Dict[str, str]:
    return {
        'Query ID': query_id,
        'Normalized Query': normalized_query,
        'Query Family': build_query_family(normalized_query),
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


@pytest.mark.parametrize(
    'normalized_query',
    [
        'SELECT * FROM Users WHERE user_id IN (?)',
        'SELECT * FROM Users WHERE user_id IN (?, ?)',
        'SELECT * FROM Users WHERE user_id IN (?,?,?)',
    ],
)
def test_build_query_family_groups_variable_in_lists(
    normalized_query: str,
) -> None:
    '''Placeholder lists of different sizes belong to the same family.'''
    expected = 'SELECT * FROM Users WHERE user_id IN (?)'
    assert build_query_family(normalized_query) == expected


def test_build_query_family_preserves_query_structure() -> None:
    '''Structurally different predicates remain in different families.'''
    equals = 'SELECT * FROM Users WHERE user_id = ?'
    in_list = 'SELECT * FROM Users WHERE user_id IN (?, ?)'

    assert build_query_family(equals) != build_query_family(in_list)


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


def test_deduplicate_results_does_not_use_query_family() -> None:
    '''Queries in the same family are not removed as duplicates.'''
    two_values = _result(
        '1',
        'SELECT * FROM Users WHERE user_id IN (?, ?)',
        'Users',
    )
    three_values = _result(
        '2',
        'SELECT * FROM Users WHERE user_id IN (?, ?, ?)',
        'Users',
    )

    assert deduplicate_results([
        two_values,
        three_values,
    ]) == [two_values, three_values]


def test_sort_results_for_csv_uses_review_order() -> None:
    '''CSV rows are ordered by family, normalized query, table and ID.'''
    second_id = _result('2', 'SELECT * FROM B', 'Users')
    first_id = _result('1', 'SELECT * FROM B', 'Users')
    other_table = _result('3', 'SELECT * FROM B', 'Courses')
    first_family = _result('4', 'SELECT * FROM A', 'Users')

    assert sort_results_for_csv([
        second_id,
        other_table,
        first_family,
        first_id,
    ]) == [first_family, other_table, first_id, second_id]
