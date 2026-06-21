'''Unit tests for the ranking logic in `update_ranks.py`.'''
import datetime
import unittest
from typing import Dict, List, Optional, cast
from unittest import mock

import mysql.connector.cursor

from cron import update_ranks
from cron.database.coder_of_the_month import Problem
from cron.database.school_of_the_month import School
from cron.tests.fixtures.mock_cursor import MockCursor
from cron.utils import UserProblems, UserRank

_CURRENT_MONTH = datetime.date(2026, 6, 1)
_NEXT_MONTH = datetime.date(2026, 7, 1)


def _cursor() -> mysql.connector.cursor.MySQLCursorDict:
    '''A MockCursor typed as the dict cursor the cron functions expect.'''
    return cast(mysql.connector.cursor.MySQLCursorDict, MockCursor())


def _user(identity_id: int, school_id: Optional[int] = None) -> UserRank:
    '''Build a UserRank fixture with sensible defaults.'''
    return UserRank(
        user_id=identity_id,
        identity_id=identity_id,
        username=f'user{identity_id}',
        country_id='MX',
        school_id=school_id,
        problems_solved=0,
        score=0.0,
        classname='user-rank-unranked',
    )


def _problems(scores_by_id: Dict[int, float]) -> Dict[int, Problem]:
    '''Build the eligible-problems mapping keyed by problem_id.'''
    return {
        problem_id: Problem(
            problem_id=problem_id,
            alias=f'problem{problem_id}',
            score=score,
        )
        for problem_id, score in scores_by_id.items()
    }


def _solved(*problem_ids: int) -> UserProblems:
    '''Build the per-user problems entry consumed by the compute functions.'''
    return {'solved': list(problem_ids), 'score': 0.0}


class _PatchHelpersMixin(unittest.TestCase):
    '''Patches the database helpers update_ranks delegates to.'''

    def _patch(self, **returns: object) -> Dict[str, mock.Mock]:
        mocks: Dict[str, mock.Mock] = {}
        for name, value in returns.items():
            patcher = mock.patch.object(
                update_ranks, name, return_value=value)
            mocks[name] = patcher.start()
            self.addCleanup(patcher.stop)
        return mocks


class ComputePointsForUserTest(_PatchHelpersMixin):
    '''Tests for `update_ranks.compute_points_for_user`.'''

    def test_scores_users_and_sorts_descending(self) -> None:
        '''Each user's score is the sum of the problems they solved.'''
        user_a = _user(1)
        user_b = _user(2)
        self._patch(
            get_last_12_coders_of_the_month=[],
            get_cotm_eligible_users=[user_a, user_b],
            get_eligible_problems=_problems({1: 10.0, 2: 5.0}),
            get_user_problems={
                user_a.identity_id: _solved(1, 2),
                user_b.identity_id: _solved(1),
            },
        )

        result = update_ranks.compute_points_for_user(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH, 'all', 10)

        self.assertEqual([u.identity_id for u in result], [1, 2])
        self.assertAlmostEqual(result[0].score, 15.0)
        self.assertEqual(result[0].problems_solved, 2)
        self.assertAlmostEqual(result[1].score, 10.0)
        self.assertEqual(result[1].problems_solved, 1)

    def test_truncates_to_coder_list_count(self) -> None:
        '''Only the top `coder_list_count` users are returned.'''
        users = [_user(i) for i in range(1, 4)]
        self._patch(
            get_last_12_coders_of_the_month=[],
            get_cotm_eligible_users=users,
            get_eligible_problems=_problems({1: 10.0, 2: 5.0, 3: 1.0}),
            get_user_problems={
                1: _solved(1, 2, 3),
                2: _solved(1, 2),
                3: _solved(1),
            },
        )

        result = update_ranks.compute_points_for_user(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH, 'all', 2)

        self.assertEqual([u.identity_id for u in result], [1, 2])

    def test_returns_empty_when_no_eligible_users(self) -> None:
        '''No eligible users yields no ranking.'''
        self._patch(
            get_last_12_coders_of_the_month=[],
            get_cotm_eligible_users=[],
            get_eligible_problems=_problems({1: 10.0}),
            get_user_problems={},
        )

        result = update_ranks.compute_points_for_user(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH, 'all', 10)

        self.assertEqual(result, [])

    def test_returns_empty_when_no_eligible_problems(self) -> None:
        '''No eligible problems yields no ranking.'''
        self._patch(
            get_last_12_coders_of_the_month=[],
            get_cotm_eligible_users=[_user(1)],
            get_eligible_problems={},
            get_user_problems={1: _solved()},
        )

        result = update_ranks.compute_points_for_user(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH, 'all', 10)

        self.assertEqual(result, [])

    def test_last_12_coders_are_passed_to_eligibility(self) -> None:
        '''The last-12 winners are forwarded to the eligibility query.'''
        last_12 = ['winner-a', 'winner-b']
        mocks = self._patch(
            get_last_12_coders_of_the_month=last_12,
            get_cotm_eligible_users=[],
            get_eligible_problems={},
            get_user_problems={},
        )

        update_ranks.compute_points_for_user(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH, 'all', 10)

        forwarded = mocks['get_cotm_eligible_users'].call_args[0][4]
        self.assertEqual(forwarded, last_12)

    def test_includes_zero_score_users_ranked_last(self) -> None:
        '''A user who solved nothing is still ranked last, with score 0.'''
        solver = _user(1)
        idle = _user(2)
        self._patch(
            get_last_12_coders_of_the_month=[],
            get_cotm_eligible_users=[solver, idle],
            get_eligible_problems=_problems({1: 10.0}),
            get_user_problems={
                solver.identity_id: _solved(1),
                idle.identity_id: _solved(),
            },
        )

        result = update_ranks.compute_points_for_user(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH, 'all', 10)

        self.assertEqual([u.identity_id for u in result], [1, 2])
        self.assertAlmostEqual(result[1].score, 0.0)
        self.assertEqual(result[1].problems_solved, 0)

    def test_preserves_input_order_on_score_ties(self) -> None:
        '''Users with equal scores keep their original eligibility order.'''
        first = _user(7)
        second = _user(3)
        self._patch(
            get_last_12_coders_of_the_month=[],
            get_cotm_eligible_users=[first, second],
            get_eligible_problems=_problems({1: 10.0}),
            get_user_problems={
                first.identity_id: _solved(1),
                second.identity_id: _solved(1),
            },
        )

        result = update_ranks.compute_points_for_user(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH, 'all', 10)

        self.assertEqual([u.identity_id for u in result], [7, 3])
        self.assertAlmostEqual(result[0].score, result[1].score)


class ComputePointsForSchoolTest(_PatchHelpersMixin):
    '''Tests for `update_ranks.compute_points_for_school`.'''

    def test_school_score_is_sum_of_unique_problems(self) -> None:
        '''A school is scored on the distinct problems its users solved.'''
        user_a = _user(1, school_id=5)
        user_b = _user(2, school_id=5)
        self._patch(
            get_last_12_schools_of_the_month=[],
            get_candidate_schools_list=[School(5, 'School 5', 0.0)],
            get_cotm_eligible_users=[user_a, user_b],
            get_eligible_problems=_problems({1: 10.0, 2: 5.0, 3: 7.0}),
            get_user_problems={
                user_a.identity_id: _solved(1, 2),
                user_b.identity_id: _solved(2, 3),
            },
        )

        result = update_ranks.compute_points_for_school(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH)

        self.assertEqual(len(result), 1)
        # Problem 2 is shared, so it is only counted once: 10 + 5 + 7.
        self.assertAlmostEqual(result[0].score, 22.0)

    def test_excludes_last_12_schools(self) -> None:
        '''Schools that won in the last 12 months are filtered out.'''
        self._patch(
            get_last_12_schools_of_the_month=[School(1, 'School 1', 0.0)],
            get_candidate_schools_list=[
                School(1, 'School 1', 0.0),
                School(2, 'School 2', 0.0),
            ],
            get_cotm_eligible_users=[_user(1, school_id=2)],
            get_eligible_problems=_problems({1: 10.0}),
            get_user_problems={1: _solved(1)},
        )

        result = update_ranks.compute_points_for_school(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH)

        self.assertEqual({s.school_id for s in result}, {2})

    def test_sorts_schools_by_score_descending(self) -> None:
        '''Schools are returned ordered by score, highest first.'''
        self._patch(
            get_last_12_schools_of_the_month=[],
            get_candidate_schools_list=[
                School(10, 'School A', 0.0),
                School(20, 'School B', 0.0),
            ],
            get_cotm_eligible_users=[
                _user(1, school_id=10),
                _user(2, school_id=20),
            ],
            get_eligible_problems=_problems({1: 5.0, 2: 9.0}),
            get_user_problems={1: _solved(1), 2: _solved(2)},
        )

        result = update_ranks.compute_points_for_school(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH)

        self.assertEqual([s.school_id for s in result], [20, 10])

    def test_returns_empty_when_no_eligible_schools(self) -> None:
        '''No candidate schools yields no ranking.'''
        self._patch(
            get_last_12_schools_of_the_month=[],
            get_candidate_schools_list=[],
            get_cotm_eligible_users=[_user(1, school_id=5)],
            get_eligible_problems=_problems({1: 10.0}),
            get_user_problems={1: _solved(1)},
        )

        result = update_ranks.compute_points_for_school(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH)

        self.assertEqual(result, [])

    def test_returns_empty_when_no_eligible_users(self) -> None:
        '''Candidate schools with no eligible users yields no ranking.'''
        self._patch(
            get_last_12_schools_of_the_month=[],
            get_candidate_schools_list=[School(5, 'School 5', 0.0)],
            get_cotm_eligible_users=[],
            get_eligible_problems=_problems({1: 10.0}),
            get_user_problems={},
        )

        result = update_ranks.compute_points_for_school(
            _cursor(), _CURRENT_MONTH, _NEXT_MONTH)

        self.assertEqual(result, [])


class UpdateUserRankCutoffsTest(unittest.TestCase):
    '''Tests for `update_ranks.update_user_rank_cutoffs`.'''

    @staticmethod
    def _inserts(cur: MockCursor) -> List[object]:
        '''Return the params of every INSERT recorded by the cursor.'''
        return [
            params for sql, params in cur.calls if 'INSERT' in sql.upper()
        ]

    def test_empty_scores_only_clears_the_table(self) -> None:
        '''With no scores the table is cleared but nothing is inserted.'''
        cur = MockCursor()

        update_ranks.update_user_rank_cutoffs(
            cast(mysql.connector.cursor.MySQLCursorDict, cur), [])

        self.assertEqual(len(cur.calls), 1)
        self.assertIn('DELETE', cur.calls[0][0].upper())
        self.assertEqual(self._inserts(cur), [])

    def test_inserts_cutoffs_at_expected_indices(self) -> None:
        '''Each cutoff picks the score at `int(len(scores) * percentile)`.'''
        scores = [100.0 - i for i in range(100)]  # descending 100.0 .. 1.0
        cur = MockCursor()

        update_ranks.update_user_rank_cutoffs(
            cast(mysql.connector.cursor.MySQLCursorDict, cur), scores)

        self.assertEqual(
            self._inserts(cur),
            [
                (scores[1], 0.01, 'user-rank-international-master'),
                (scores[9], 0.09, 'user-rank-master'),
                (scores[15], 0.15, 'user-rank-expert'),
                (scores[35], 0.35, 'user-rank-specialist'),
                (scores[40], 0.40, 'user-rank-beginner'),
            ],
        )

    def test_handles_fewer_scores_than_buckets(self) -> None:
        '''Short score lists still index in range (guards against overflow).'''
        scores = [30.0, 20.0, 10.0]
        cur = MockCursor()

        update_ranks.update_user_rank_cutoffs(
            cast(mysql.connector.cursor.MySQLCursorDict, cur), scores)

        # int(3 * percentile) lands on 0 for the top three buckets and 1 for
        # the last two; nothing reaches index 3, so no IndexError.
        self.assertEqual(
            self._inserts(cur),
            [
                (scores[0], 0.01, 'user-rank-international-master'),
                (scores[0], 0.09, 'user-rank-master'),
                (scores[0], 0.15, 'user-rank-expert'),
                (scores[1], 0.35, 'user-rank-specialist'),
                (scores[1], 0.40, 'user-rank-beginner'),
            ],
        )


class UpdateUserRankClassnameTest(unittest.TestCase):
    '''Tests for `update_ranks.update_user_rank_classname`.'''

    def test_executes_single_classname_update(self) -> None:
        '''The function issues one UPDATE against User_Rank.'''
        cur = MockCursor()

        update_ranks.update_user_rank_classname(
            cast(mysql.connector.cursor.MySQLCursorDict, cur))

        self.assertEqual(len(cur.calls), 1)
        self.assertIn('UPDATE USER_RANK', cur.calls[0][0].upper())


if __name__ == '__main__':
    unittest.main()
