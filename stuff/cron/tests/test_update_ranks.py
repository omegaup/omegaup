'''Unit tests for the ranking logic in `update_ranks.py` and its helpers.'''
import datetime
import unittest
from typing import Dict, List, Optional, Sequence, Tuple, cast
from unittest import mock

import mysql.connector.cursor

from cron import update_ranks
from cron.database.coder_of_the_month import Problem, get_user_problems
from cron.database.school_of_the_month import School
from cron.tests.fixtures import sqlite_cursor
from cron.tests.fixtures.mock_cursor import MockCursor
from cron.utils import UserProblems, UserRank

_CURRENT_MONTH = datetime.date(2026, 6, 1)
_NEXT_MONTH = datetime.date(2026, 7, 1)

# Only the columns the `get_user_problems` queries touch.
_USER_PROBLEMS_SCHEMA = (
    'CREATE TABLE Identities (identity_id INTEGER, user_id INTEGER);',
    'CREATE TABLE Submissions (identity_id INTEGER, problem_id INTEGER, '
    'verdict TEXT, type TEXT, time TEXT);',
    'CREATE TABLE Problems_Forfeited (user_id INTEGER, problem_id INTEGER);',
    'CREATE TABLE Problems (problem_id INTEGER, acl_id INTEGER);',
    'CREATE TABLE ACLs (acl_id INTEGER, owner_id INTEGER);',
    'CREATE TABLE User_Roles (user_id INTEGER, acl_id INTEGER, '
    'role_id INTEGER);',
    'CREATE TABLE Group_Roles (group_id INTEGER, acl_id INTEGER, '
    'role_id INTEGER);',
    'CREATE TABLE Groups_Identities (group_id INTEGER, identity_id INTEGER);',
)


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


def _run_get_user_problems(
    submissions: Sequence[Tuple[int, int, str, str, str]],
    forfeited: Sequence[Tuple[int, int]] = (),
    problem_owners: Sequence[Tuple[int, int]] = (),
) -> Dict[int, List[int]]:
    '''Run `get_user_problems` over SQLite and return the solved ids.

    Submissions are `(identity_id, problem_id, verdict, type, time)` and
    `problem_owners` are `(problem_id, acl owner user_id)`. Each identity uses
    the user id of the same number.
    '''
    identity_ids = sorted({row[0] for row in submissions})
    problem_ids = sorted({row[1] for row in submissions})
    conn = sqlite_cursor.connect(*_USER_PROBLEMS_SCHEMA)
    conn.executemany(
        'INSERT INTO Identities VALUES (?, ?);',
        [(identity_id, identity_id) for identity_id in identity_ids])
    conn.executemany('INSERT INTO Submissions VALUES (?, ?, ?, ?, ?);',
                     submissions)
    conn.executemany('INSERT INTO Problems_Forfeited VALUES (?, ?);',
                     forfeited)
    conn.executemany(
        'INSERT INTO Problems VALUES (?, ?);',
        [(problem_id, problem_id) for problem_id in problem_ids])
    conn.executemany('INSERT INTO ACLs VALUES (?, ?);', problem_owners)

    user_problems = get_user_problems(
        cast(mysql.connector.cursor.MySQLCursorDict,
             sqlite_cursor.SqliteCursor(conn)),
        identity_ids,
        problem_ids,
        [_user(identity_id) for identity_id in identity_ids],
        _CURRENT_MONTH,
    )
    return {
        identity_id: problems['solved']
        for identity_id, problems in user_problems.items()
    }


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


class GetUserProblemsTest(unittest.TestCase):
    '''Tests for the filtering rules in `get_user_problems`.'''

    def test_counts_problems_first_solved_in_the_month(self) -> None:
        '''An accepted run inside the month counts for its identity.'''
        solved = _run_get_user_problems([
            (1, 10, 'AC', 'normal', '2026-06-01 00:00:00'),
            (2, 20, 'AC', 'normal', '2026-06-30 23:59:59'),
        ])

        self.assertEqual(solved, {1: [10], 2: [20]})

    def test_skips_problems_first_solved_before_the_month(self) -> None:
        '''Re-solving a problem inside the month does not make it count.'''
        solved = _run_get_user_problems([
            (1, 10, 'AC', 'normal', '2026-05-31 23:59:59'),
            (1, 10, 'AC', 'normal', '2026-06-15 10:00:00'),
        ])

        self.assertEqual(solved, {1: []})

    def test_skips_problems_first_solved_after_the_month(self) -> None:
        '''A problem first solved in a later month does not count.'''
        solved = _run_get_user_problems([
            (1, 10, 'AC', 'normal', '2026-07-01 00:00:00'),
        ])

        self.assertEqual(solved, {1: []})

    def test_skips_forfeited_problems(self) -> None:
        '''A forfeited problem is dropped only for the user that forfeited.'''
        solved = _run_get_user_problems(
            [
                (1, 10, 'AC', 'normal', '2026-06-05 10:00:00'),
                (2, 10, 'AC', 'normal', '2026-06-05 10:00:00'),
            ],
            forfeited=[(1, 10)],
        )

        self.assertEqual(solved, {1: [], 2: [10]})

    def test_skips_problems_administered_by_the_user(self) -> None:
        '''A problem is dropped only for the identity that administers it.'''
        solved = _run_get_user_problems(
            [
                (1, 10, 'AC', 'normal', '2026-06-05 10:00:00'),
                (2, 10, 'AC', 'normal', '2026-06-05 10:00:00'),
            ],
            problem_owners=[(10, 1)],
        )

        self.assertEqual(solved, {1: [], 2: [10]})

    def test_deduplicates_by_user_and_problem(self) -> None:
        '''Repeated accepted runs on one problem are counted once.'''
        solved = _run_get_user_problems([
            (1, 10, 'AC', 'normal', '2026-06-05 10:00:00'),
            (1, 10, 'AC', 'normal', '2026-06-06 10:00:00'),
            (1, 10, 'AC', 'normal', '2026-06-07 10:00:00'),
        ])

        self.assertEqual(solved, {1: [10]})

    def test_skips_rejected_and_non_normal_runs(self) -> None:
        '''Only accepted runs of type `normal` count.'''
        solved = _run_get_user_problems([
            (1, 10, 'WA', 'normal', '2026-06-05 10:00:00'),
            (1, 20, 'AC', 'test', '2026-06-05 10:00:00'),
        ])

        self.assertEqual(solved, {1: []})


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

    def test_returns_empty_when_no_eligible_problems(self) -> None:
        '''No eligible problems yields no ranking, not zero-scored schools.'''
        self._patch(
            get_last_12_schools_of_the_month=[],
            get_candidate_schools_list=[School(5, 'School 5', 0.0)],
            get_cotm_eligible_users=[_user(1, school_id=5)],
            get_eligible_problems={},
            get_user_problems={1: _solved()},
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

    _CUTOFFS = (
        (1900.0, 0.01, 'user-rank-international-master'),
        (1500.0, 0.09, 'user-rank-master'),
        (1100.0, 0.15, 'user-rank-expert'),
        (800.0, 0.35, 'user-rank-specialist'),
        (500.0, 0.40, 'user-rank-beginner'),
    )

    @staticmethod
    def _classnames(
        scores: Sequence[float],
        cutoffs: Sequence[Tuple[float, float, str]],
    ) -> List[Optional[str]]:
        '''Run the real UPDATE over SQLite and return the stored classnames.'''
        conn = sqlite_cursor.connect(
            'CREATE TABLE User_Rank (user_id INTEGER, score REAL, '
            'classname TEXT);',
            'CREATE TABLE User_Rank_Cutoffs (score REAL, percentile REAL, '
            'classname TEXT);')
        conn.executemany('INSERT INTO User_Rank VALUES (?, ?, NULL);',
                         list(enumerate(scores, start=1)))
        conn.executemany('INSERT INTO User_Rank_Cutoffs VALUES (?, ?, ?);',
                         cutoffs)

        update_ranks.update_user_rank_classname(
            cast(mysql.connector.cursor.MySQLCursorDict,
                 sqlite_cursor.SqliteCursor(conn)))

        return [
            row[0] for row in conn.execute(
                'SELECT classname FROM User_Rank ORDER BY user_id;')
        ]

    def test_assigns_the_most_selective_cutoff_reached(self) -> None:
        '''A score takes the most selective cutoff it reaches.'''
        self.assertEqual(
            self._classnames(
                [2000.0, 1900.0, 1600.0, 1100.0, 900.0, 500.0], self._CUTOFFS),
            [
                'user-rank-international-master',
                'user-rank-international-master',
                'user-rank-master',
                'user-rank-expert',
                'user-rank-specialist',
                'user-rank-beginner',
            ])

    def test_falls_back_to_unranked_below_every_cutoff(self) -> None:
        '''A score under the lowest cutoff is unranked.'''
        self.assertEqual(self._classnames([499.0, 0.0], self._CUTOFFS),
                         ['user-rank-unranked', 'user-rank-unranked'])

    def test_falls_back_to_unranked_without_cutoffs(self) -> None:
        '''With no cutoffs stored every user is unranked.'''
        self.assertEqual(self._classnames([2000.0], []),
                         ['user-rank-unranked'])


if __name__ == '__main__':
    unittest.main()
