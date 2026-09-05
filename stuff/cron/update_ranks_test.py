'''Unittests for the update_ranks ranking audit.

These use an in-memory fake cursor so no real database is required. They
exercise the pre-publish guardrail that aborts a ranking run which would
replace a healthy ranking with an empty, negative-scored or drastically
smaller one.
'''

import sqlite3
import sys
import unittest

from typing import Any, Dict, Optional, cast

import mysql.connector.cursor

import update_ranks


def _drop_cron_database_package() -> None:
    '''Unbinds the `database` package that importing update_ranks pulled in.

    stuff/cron and stuff/pipelines both ship a package called `database` and
    the first one imported wins for the rest of the pytest session, so the
    pipelines tests would stop finding theirs.
    '''
    for name in [key for key in sys.modules
                 if key.split('.')[0] == 'database']:
        del sys.modules[name]


_drop_cron_database_package()


class _FakeCursor:
    '''Answers the two audit COUNT queries from scripted numbers.'''

    def __init__(self, new_count: int, negatives: int = 0) -> None:
        self._new_count = new_count
        self._negatives = negatives
        self._last: Optional[Dict[str, int]] = None

    def execute(self, query: str, params: Any = None) -> None:
        '''Remembers which scripted count the next fetch should return.'''
        del params
        normalized = ' '.join(query.lower().split())
        if 'score' in normalized and '< 0' in normalized:
            self._last = {'n': self._negatives}
        elif 'count(*)' in normalized:
            self._last = {'n': self._new_count}
        else:
            self._last = None

    def fetchone(self) -> Optional[Dict[str, int]]:
        '''Returns the scripted row for the last query.'''
        return self._last


def _audit(cursor: _FakeCursor, previous_count: int, max_churn: float) -> None:
    '''Runs the ranking audit against a fake cursor.'''
    update_ranks.audit_user_rank(
        cast(mysql.connector.cursor.MySQLCursorDict, cursor),
        previous_count=previous_count,
        max_churn=max_churn)


class _SqliteCursor:
    '''Runs the real count query against an in memory User_Rank.'''

    def __init__(self, ranked: int, author_only: int) -> None:
        self._conn = sqlite3.connect(':memory:')
        self._conn.execute(
            'CREATE TABLE User_Rank (user_id INTEGER, ranking INTEGER)')
        rows = ([(i, i + 1) for i in range(ranked)] +
                [(1000 + i, None) for i in range(author_only)])
        self._conn.executemany('INSERT INTO User_Rank VALUES (?, ?)', rows)
        self._cur = self._conn.cursor()

    def execute(self, query: str, params: Any = None) -> None:
        '''Executes the query with the backticks sqlite does not accept.'''
        del params
        self._cur.execute(query.replace('`', '"').rstrip(';'))

    def fetchone(self) -> Optional[Dict[str, int]]:
        '''Returns the row as the dictionary cursor would.'''
        row = self._cur.fetchone()
        return {'n': row[0]} if row else None


class CountUserRankTest(unittest.TestCase):
    '''Tests for update_ranks._count_user_rank.'''

    def _count(self, ranked: int, author_only: int) -> int:
        # pylint: disable=protected-access
        return update_ranks._count_user_rank(
            cast(mysql.connector.cursor.MySQLCursorDict,
                 _SqliteCursor(ranked, author_only)))

    def test_counts_the_users_in_the_ranking(self) -> None:
        '''A table of ranked users counts all of them.'''
        self.assertEqual(self._count(ranked=7, author_only=0), 7)

    def test_ignores_author_only_rows(self) -> None:
        '''Author rows have a null ranking and are not part of the count.'''
        self.assertEqual(self._count(ranked=7, author_only=9), 7)

    def test_counts_nothing_when_only_authors_are_stored(self) -> None:
        '''An author only table is an empty ranking, not a full one.'''
        self.assertEqual(self._count(ranked=0, author_only=9), 0)


class UpdateRanksAuditTest(unittest.TestCase):
    '''Tests for update_ranks.audit_user_rank.'''

    def test_passes_on_healthy_ranking(self) -> None:
        '''A healthy ranking of a comparable size is published.'''
        _audit(_FakeCursor(new_count=100), previous_count=100, max_churn=0.5)

    def test_raises_on_empty_ranking(self) -> None:
        '''An empty new ranking aborts the publish.'''
        with self.assertRaises(update_ranks.RankingAuditError):
            _audit(_FakeCursor(new_count=0), previous_count=100, max_churn=0.5)

    def test_raises_on_negative_scores(self) -> None:
        '''Any negative-scored row aborts the publish.'''
        with self.assertRaises(update_ranks.RankingAuditError):
            _audit(
                _FakeCursor(new_count=100, negatives=3),
                previous_count=100,
                max_churn=0.5)

    def test_raises_on_excessive_churn(self) -> None:
        '''Dropping more than the allowed fraction of rows aborts.'''
        with self.assertRaises(update_ranks.RankingAuditError):
            _audit(
                _FakeCursor(new_count=40), previous_count=100, max_churn=0.5)

    def test_allows_ranking_growth(self) -> None:
        '''A larger ranking than before is allowed.'''
        _audit(_FakeCursor(new_count=150), previous_count=100, max_churn=0.5)

    def test_allows_first_run_from_empty(self) -> None:
        '''The churn check is skipped when there was no previous ranking.'''
        _audit(_FakeCursor(new_count=50), previous_count=0, max_churn=0.5)

    def test_allows_empty_when_there_was_nothing_before(self) -> None:
        '''An empty ranking is fine when there was nothing to lose.'''
        _audit(_FakeCursor(new_count=0), previous_count=0, max_churn=0.5)


if __name__ == '__main__':
    unittest.main()
