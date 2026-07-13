'''Unittests for the update_ranks ranking audit.

These use an in-memory fake cursor so no real database is required. They
exercise the pre-publish guardrail that aborts a ranking run which would
replace a healthy ranking with an empty, negative-scored or drastically
smaller one.
'''

import unittest

from typing import Any, Dict, Optional, cast

import mysql.connector.cursor

import update_ranks


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


if __name__ == '__main__':
    unittest.main()
