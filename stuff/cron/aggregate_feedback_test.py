"""Unittests for the aggregate_feedback cron script.

These tests focus on ensuring that a failure while aggregating
feedback for a single problem does not prevent other problems from
being processed.
"""

import unittest
from typing import Any, List, Tuple, cast
from unittest.mock import patch
import logging

import aggregate_feedback


class _FakeCursor:
    """Minimal cursor stub for aggregate_feedback.aggregate_feedback."""

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids

    def execute(self, query: str, params: Any = None) -> None:
        """Executes a fake SQL query without touching a real database."""
        del query, params

    def fetchall(self) -> List[Tuple[int]]:
        """Returns all fake problem_id rows for this cursor."""
        return [(problem_id,) for problem_id in self._problem_ids]

    def __enter__(self) -> "_FakeCursor":
        return self

    def __exit__(self, exc_type: Any, exc_val: Any, exc_tb: Any) -> None:
        del exc_type, exc_val, exc_tb


class _FakeDBConnection:
    """Minimal db connection stub used by
    aggregate_feedback.aggregate_feedback."""

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids
        self.rollback_calls = 0
        self.conn = self

    def cursor(self) -> _FakeCursor:
        """Creates a new fake cursor over the configured problem_ids."""
        return _FakeCursor(self._problem_ids)

    def rollback(self) -> None:
        """Records that a rollback was requested on the fake connection."""
        self.rollback_calls += 1


class _FakeCursorForReviewers:
    """Minimal cursor stub for aggregate_feedback.aggregate_reviewers_feedback."""

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids

    def execute(self, query: str, params: Any = None) -> None:
        """Executes a fake SQL query without touching a real database."""
        del query, params

    def fetchall(self) -> List[Tuple[int]]:
        """Returns all fake problem_id rows for this cursor."""
        return [(problem_id,) for problem_id in self._problem_ids]

    def __enter__(self) -> "_FakeCursorForReviewers":
        return self

    def __exit__(self, exc_type: Any, exc_val: Any, exc_tb: Any) -> None:
        del exc_type, exc_val, exc_tb


class _FakeDBConnectionForReviewers:
    """Minimal db connection stub for aggregate_reviewers_feedback."""

    def __init__(self, problem_ids: List[int]) -> None:
        self._problem_ids = problem_ids
        self.rollback_calls = 0
        self.commit_calls = 0
        self.conn = self

    def cursor(self) -> _FakeCursorForReviewers:
        """Creates a new fake cursor over the configured problem_ids."""
        return _FakeCursorForReviewers(self._problem_ids)

    def rollback(self) -> None:
        """Records that a rollback was requested on the fake connection."""
        self.rollback_calls += 1

    def commit(self) -> None:
        """Records that a commit was requested on the fake connection."""
        self.commit_calls += 1


class AggregateFeedbackTest(unittest.TestCase):
    """Tests for aggregate_feedback.aggregate_feedback."""

    def test_single_problem_failure_does_not_stop_others(self) -> None:
        """One failing problem should not prevent others from updating."""
        problem_ids = [1, 2, 3]
        failing_problem_id = 2
        dbconn = _FakeDBConnection(problem_ids)

        called_ids: List[int] = []

        def fake_fill_rank_cutoffs(dbconn_arg: Any) -> Any:
            del dbconn_arg
            return []

        def fake_get_global_averages(dbconn_arg: Any, rank_cutoffs_arg: Any) -> Any:
            del dbconn_arg, rank_cutoffs_arg
            return (None, None)

        def fake_aggregate_problem_feedback(
            dbconn_arg: Any,
            problem_id: int,
            rank_cutoffs_arg: Any,
            global_quality_average_arg: Any,
            global_difficulty_average_arg: Any,
        ) -> None:
            del (
                dbconn_arg,
                rank_cutoffs_arg,
                global_quality_average_arg,
                global_difficulty_average_arg,
            )
            called_ids.append(problem_id)
            if problem_id == failing_problem_id:
                raise RuntimeError("simulated failure for testing")

        original_fill_rank_cutoffs = aggregate_feedback.fill_rank_cutoffs
        original_get_global = (
            aggregate_feedback.get_global_quality_and_difficulty_average
        )
        original_aggregate_problem_feedback = (
            aggregate_feedback.aggregate_problem_feedback
        )

        aggregate_feedback.fill_rank_cutoffs = cast(Any, fake_fill_rank_cutoffs)
        aggregate_feedback.get_global_quality_and_difficulty_average = cast(
            Any, fake_get_global_averages
        )
        aggregate_feedback.aggregate_problem_feedback = cast(
            Any, fake_aggregate_problem_feedback
        )

        try:
            aggregate_feedback.aggregate_feedback(cast(Any, dbconn))
        finally:
            aggregate_feedback.fill_rank_cutoffs = original_fill_rank_cutoffs
            aggregate_feedback.get_global_quality_and_difficulty_average = (
                original_get_global
            )
            aggregate_feedback.aggregate_problem_feedback = (
                original_aggregate_problem_feedback
            )

        self.assertEqual(called_ids, problem_ids)
        self.assertEqual(dbconn.rollback_calls, 1)


class AggregateReviewersFeedbackTest(unittest.TestCase):
    """Tests for aggregate_feedback.aggregate_reviewers_feedback."""

    def test_single_problem_failure_does_not_stop_others(self) -> None:
        """One failing problem should not prevent others from being processed."""
        problem_ids = [1, 2, 3]
        failing_problem_id = 2
        dbconn = _FakeDBConnectionForReviewers(problem_ids)

        called_ids: List[int] = []

        def fake_aggregate_reviewers_feedback_for_problem(
            dbconn_arg: Any, problem_id: int
        ) -> None:
            del dbconn_arg
            called_ids.append(problem_id)
            if problem_id == failing_problem_id:
                raise RuntimeError("simulated failure for testing")

        original_func = aggregate_feedback.aggregate_reviewers_feedback_for_problem
        aggregate_feedback.aggregate_reviewers_feedback_for_problem = cast(
            Any, fake_aggregate_reviewers_feedback_for_problem
        )

        try:
            with patch.object(aggregate_feedback, "logging") as mock_logging:
                aggregate_feedback.aggregate_reviewers_feedback(cast(Any, dbconn))
        finally:
            aggregate_feedback.aggregate_reviewers_feedback_for_problem = original_func

        self.assertEqual(called_ids, problem_ids)
        self.assertEqual(dbconn.rollback_calls, 1)
        self.assertEqual(dbconn.commit_calls, 2)

    def test_summary_log_includes_counts(self) -> None:
        """Summary log should include attempted, successful, and failed counts."""
        problem_ids = [1, 2, 3]
        failing_problem_id = 2
        dbconn = _FakeDBConnectionForReviewers(problem_ids)

        def fake_aggregate_reviewers_feedback_for_problem(
            dbconn_arg: Any, problem_id: int
        ) -> None:
            del dbconn_arg
            if problem_id == failing_problem_id:
                raise RuntimeError("simulated failure for testing")

        original_func = aggregate_feedback.aggregate_reviewers_feedback_for_problem
        aggregate_feedback.aggregate_reviewers_feedback_for_problem = cast(
            Any, fake_aggregate_reviewers_feedback_for_problem
        )

        try:
            with patch.object(aggregate_feedback, "logging") as mock_logging:
                aggregate_feedback.aggregate_reviewers_feedback(cast(Any, dbconn))
                summary_call = [
                    call
                    for call in mock_logging.info.call_args_list
                    if "attempted=" in str(call)
                ]
                self.assertEqual(len(summary_call), 1)
                self.assertIn("attempted=3", str(summary_call[0]))
                self.assertIn("successful=2", str(summary_call[0]))
                self.assertIn("failed=1", str(summary_call[0]))
        finally:
            aggregate_feedback.aggregate_reviewers_feedback_for_problem = original_func


if __name__ == "__main__":
    unittest.main()
