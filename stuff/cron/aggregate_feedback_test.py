'''Unittests for the aggregate_feedback cron script.

These tests focus on ensuring that a failure while aggregating
feedback for a single problem does not prevent other problems from
being processed.
'''

import unittest
from typing import Any, List, Tuple, cast
from unittest.mock import MagicMock
import sys

# Mock dependencies since they might not be installed in the test environment
mock_mysql = MagicMock()
sys.modules['mysql'] = mock_mysql
sys.modules['mysql.connector'] = mock_mysql.connector

# Mock lib.db and lib.logs to avoid dependency on real DB and specific loggers
sys.modules['lib'] = MagicMock()
sys.modules['lib.db'] = MagicMock()
sys.modules['lib.logs'] = MagicMock()
sys.modules['pythonjsonlogger'] = MagicMock()

import aggregate_feedback


class _FakeCursor:
    '''Minimal cursor stub for aggregate_feedback.aggregate_feedback.'''

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids

    def execute(self, query: str, params: Any = None) -> None:
        '''Executes a fake SQL query without touching a real database.'''
        del query, params

    def fetchall(self) -> List[Tuple[int]]:
        '''Returns all fake problem_id rows for this cursor.'''
        return [(problem_id,) for problem_id in self._problem_ids]

    def __enter__(self) -> "_FakeCursor":
        return self

    def __exit__(
            self,
            exc_type: Any,
            exc_val: Any,
            exc_tb: Any) -> None:
        del exc_type, exc_val, exc_tb


class _FakeDBConnection:
    '''Minimal db connection stub used by
    aggregate_feedback.aggregate_feedback.'''

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids
        self.rollback_calls = 0
        self.conn = self

    def cursor(self) -> _FakeCursor:
        '''Creates a new fake cursor over the configured problem_ids.'''
        return _FakeCursor(self._problem_ids)

    def rollback(self) -> None:
        '''Records that a rollback was requested on the fake connection.'''
        self.rollback_calls += 1

    def commit(self) -> None:
        '''Fake commit method.'''
        pass


class AggregateFeedbackTest(unittest.TestCase):
    '''Tests for aggregate_feedback.aggregate_feedback.'''

    def test_single_problem_failure_does_not_stop_others(self) -> None:
        '''One failing problem should not prevent others from updating.'''
        problem_ids = [1, 2, 3]
        failing_problem_id = 2
        dbconn = _FakeDBConnection(problem_ids)

        called_ids: List[int] = []

        def fake_fill_rank_cutoffs(dbconn_arg: Any) -> Any:
            del dbconn_arg
            return []

        def fake_get_global_averages(
                dbconn_arg: Any, rank_cutoffs_arg: Any) -> Any:
            del dbconn_arg, rank_cutoffs_arg
            return (None, None)

        def fake_aggregate_problem_feedback(
                dbconn_arg: Any,
                problem_id: int,
                rank_cutoffs_arg: Any,
                global_quality_average_arg: Any,
                global_difficulty_average_arg: Any) -> None:
            del (dbconn_arg, rank_cutoffs_arg, global_quality_average_arg,
                 global_difficulty_average_arg)
            called_ids.append(problem_id)
            if problem_id == failing_problem_id:
                raise RuntimeError('simulated failure for testing')

        original_fill_rank_cutoffs = aggregate_feedback.fill_rank_cutoffs
        original_get_global = (
            aggregate_feedback.get_global_quality_and_difficulty_average)
        original_aggregate_problem_feedback = (
            aggregate_feedback.aggregate_problem_feedback)

        aggregate_feedback.fill_rank_cutoffs = cast(
            Any, fake_fill_rank_cutoffs)
        aggregate_feedback.get_global_quality_and_difficulty_average = cast(
            Any, fake_get_global_averages)
        aggregate_feedback.aggregate_problem_feedback = cast(
            Any, fake_aggregate_problem_feedback)

        try:
            aggregate_feedback.aggregate_feedback(cast(Any, dbconn))
        finally:
            aggregate_feedback.fill_rank_cutoffs = original_fill_rank_cutoffs
            aggregate_feedback.get_global_quality_and_difficulty_average = (
                original_get_global)
            aggregate_feedback.aggregate_problem_feedback = (
                original_aggregate_problem_feedback)

        self.assertEqual(called_ids, problem_ids)
        self.assertEqual(dbconn.rollback_calls, 1)


class AggregateReviewersFeedbackTest(unittest.TestCase):
    '''Tests for aggregate_feedback.aggregate_reviewers_feedback.'''

    def test_single_problem_failure_does_not_stop_others(self) -> None:
        '''One failing problem should not prevent others from updating.'''
        problem_ids = [1, 2, 3]
        failing_problem_id = 2
        dbconn = _FakeDBConnection(problem_ids)

        called_ids: List[int] = []

        def fake_aggregate_reviewers_feedback_for_problem(
                dbconn_arg: Any,
                problem_id: int) -> None:
            del dbconn_arg
            called_ids.append(problem_id)
            if problem_id == failing_problem_id:
                raise RuntimeError('simulated failure for testing')

        original_aggregate_problem_feedback = (
            aggregate_feedback.aggregate_reviewers_feedback_for_problem)
        aggregate_feedback.aggregate_reviewers_feedback_for_problem = cast(
            Any, fake_aggregate_reviewers_feedback_for_problem)

        try:
            aggregate_feedback.aggregate_reviewers_feedback(cast(Any, dbconn))
        finally:
            aggregate_feedback.aggregate_reviewers_feedback_for_problem = (
                original_aggregate_problem_feedback)

        self.assertEqual(called_ids, problem_ids)
        self.assertEqual(dbconn.rollback_calls, 1)


class MainResilienceTest(unittest.TestCase):
    '''Tests for aggregate_feedback.main phase resilience.'''

    def test_main_continues_through_failures(self) -> None:
        '''main() should attempt all phases even if one fails.'''
        phases_called = []

        def fake_aggregate_reviewers_feedback(dbconn_arg: Any) -> None:
            del dbconn_arg
            phases_called.append('reviewers')
            raise RuntimeError('phase 1 failure')

        def fake_aggregate_feedback(dbconn_arg: Any) -> None:
            del dbconn_arg
            phases_called.append('general')

        def fake_update_problem_of_the_week(dbconn_arg: Any,
                                           difficulty: str) -> None:
            del dbconn_arg, difficulty
            phases_called.append('potw')

        # Mocking external dependencies of main()
        # Using patch would be cleaner but follows the existing pattern
        original_reviewers = aggregate_feedback.aggregate_reviewers_feedback
        original_feedback = aggregate_feedback.aggregate_feedback
        original_potw = aggregate_feedback.update_problem_of_the_week
        original_connect = aggregate_feedback.lib.db.connect
        original_exit = aggregate_feedback.sys.exit

        aggregate_feedback.aggregate_reviewers_feedback = fake_aggregate_reviewers_feedback
        aggregate_feedback.aggregate_feedback = fake_aggregate_feedback
        aggregate_feedback.update_problem_of_the_week = fake_update_problem_of_the_week
        
        # Mocking db connect and sys.exit to avoid real world side effects
        class MockDB:
            def __init__(self): self.conn = self
            def close(self): pass
        aggregate_feedback.lib.db.connect = lambda _: MockDB()
        
        exit_codes = []
        aggregate_feedback.sys.exit = lambda code: exit_codes.append(code)

        try:
            # Mocking argparse
            import argparse
            original_parse = argparse.ArgumentParser.parse_args
            argparse.ArgumentParser.parse_args = lambda _: argparse.Namespace(
                db_host='localhost', db_user='user', db_password='password',
                db_name='name', mysql_config_file=None, logging_level='INFO')
            
            aggregate_feedback.main()
            
            argparse.ArgumentParser.parse_args = original_parse
        finally:
            aggregate_feedback.aggregate_reviewers_feedback = original_reviewers
            aggregate_feedback.aggregate_feedback = original_feedback
            aggregate_feedback.update_problem_of_the_week = original_potw
            aggregate_feedback.lib.db.connect = original_connect
            aggregate_feedback.sys.exit = original_exit

        self.assertEqual(phases_called, ['reviewers', 'general', 'potw'])
        self.assertEqual(exit_codes, [1])


if __name__ == '__main__':
    unittest.main()
