'''Unittests for the aggregate_feedback cron script.

These tests focus on ensuring that a failure while aggregating
feedback for a single problem does not prevent other problems from
being processed.
'''

import unittest
from typing import Any, List, Tuple

import aggregate_feedback


class _FakeCursor:
    '''Minimal cursor stub for aggregate_feedback.aggregate_feedback.'''

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids

    def execute(self, query: str, params: Any = None) -> None:
        del query, params

    def fetchall(self) -> List[Tuple[int]]:
        return [(problem_id,) for problem_id in self._problem_ids]

    def __enter__(self) -> "_FakeCursor":
        return self

    def __exit__(self, exc_type, exc, tb) -> None: 
        del exc_type, exc, tb
        return False


class _FakeDBConnection:
    '''Minimal db connection stub used by aggregate_feedback.aggregate_feedback.'''

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids
        self.rollback_calls = 0
        self.conn = self

    def cursor(self) -> _FakeCursor:  
        return _FakeCursor(self._problem_ids)

    def rollback(self) -> None: 
        self.rollback_calls += 1


class AggregateFeedbackTest(unittest.TestCase):
    '''Tests for aggregate_feedback.aggregate_feedback.'''

    def test_single_problem_failure_does_not_stop_others(self) -> None:
        '''One failing problem should not prevent others from updating.'''
        problem_ids = [1, 2, 3]
        failing_problem_id = 2
        dbconn = _FakeDBConnection(problem_ids)

        called_ids: List[int] = []

        def fake_fill_rank_cutoffs(db: Any) -> Any:
            del db
            return []

        def fake_get_global_averages(db: Any, rank_cutoffs: Any) -> Any:
            del db, rank_cutoffs
            return (None, None)

        def fake_aggregate_problem_feedback(
                db: Any,
                problem_id: int,
                rank_cutoffs: Any,
                global_quality_average: Any,
                global_difficulty_average: Any) -> None:
            del db, rank_cutoffs, global_quality_average, global_difficulty_average
            called_ids.append(problem_id)
            if problem_id == failing_problem_id:
                raise RuntimeError('simulated failure for testing')

        original_fill_rank_cutoffs = aggregate_feedback.fill_rank_cutoffs
        original_get_global = (
            aggregate_feedback.get_global_quality_and_difficulty_average)
        original_aggregate_problem_feedback = (
            aggregate_feedback.aggregate_problem_feedback)

        aggregate_feedback.fill_rank_cutoffs = fake_fill_rank_cutoffs
        aggregate_feedback.get_global_quality_and_difficulty_average = (
            fake_get_global_averages)
        aggregate_feedback.aggregate_problem_feedback = (
            fake_aggregate_problem_feedback)

        try:
            aggregate_feedback.aggregate_feedback(dbconn)
        finally:
            aggregate_feedback.fill_rank_cutoffs = original_fill_rank_cutoffs
            aggregate_feedback.get_global_quality_and_difficulty_average = (
                original_get_global)
            aggregate_feedback.aggregate_problem_feedback = (
                original_aggregate_problem_feedback)

        self.assertEqual(called_ids, problem_ids)
        self.assertEqual(dbconn.rollback_calls, 1)


if __name__ == '__main__':
    unittest.main()

