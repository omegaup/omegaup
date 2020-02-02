#!/usr/bin/python3

'''Unittest for the recommendation model builder script.

These are function-level unittests for the recommendation model builder.
Integration tests should be done via a PHP entry point.
'''

import os.path
import sys
import unittest

from typing import Tuple

import MySQLdb.connections

import pandas as pd  # type: ignore

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import build_problem_rec_model


class TestFirstAcPerProblem(unittest.TestCase):
    '''Test extracting the first AC submission per user.'''

    def test_single_user_multiple_submissions(self) -> None:
        '''Multiple AC submissions for the same problem should be ignored.'''
        runs = pd.DataFrame([(1, 1, 0),
                             (1, 1, 1),     # Repeat solution of 1
                             (1, 2, 2),
                             (1, 3, 3),
                             (1, 1, 4)],    # Repeat solution of 1
                            columns=['identity_id', 'problem_id', 'time'])
        expected = pd.DataFrame([(1, 1, 0),
                                 (1, 2, 2),
                                 (1, 3, 3)],
                                columns=['identity_id', 'problem_id', 'time'])
        first_ac = build_problem_rec_model.get_first_run_per_problem(runs)
        self.assertCountEqual(first_ac, expected)

    def test_multiple_users(self) -> None:
        '''First AC submissions are picked for multiple users.'''
        runs = pd.DataFrame([(1, 1, 0),
                             (1, 2, 2),
                             (1, 3, 3),
                             (2, 1, 0),
                             (2, 2, 1),
                             (3, 1, 0),
                             (3, 4, 5)],
                            columns=['identity_id', 'problem_id', 'time'])
        expected = pd.DataFrame([(1, 1, 0),
                                 (1, 2, 2),
                                 (1, 3, 3),
                                 (2, 1, 0),
                                 (2, 2, 1),
                                 (3, 1, 0),
                                 (3, 4, 5)],
                                columns=['identity_id', 'problem_id', 'time'])
        first_ac = build_problem_rec_model.get_first_run_per_problem(runs)
        self.assertCountEqual(first_ac, expected)

    def test_records_out_of_order(self) -> None:
        '''Tests that the first AC submission is kept even if records are out
        of order
        '''
        runs = pd.DataFrame([(2, 1, 3),     # Out of order solution of 1
                             (2, 2, 1),
                             (2, 3, 2),
                             (2, 1, 0)],
                            columns=['identity_id', 'problem_id', 'time'])
        expected = pd.DataFrame([(2, 1, 0),
                                 (2, 2, 1),
                                 (2, 3, 2)],
                                columns=['identity_id', 'problem_id', 'time'])
        first_ac = build_problem_rec_model.get_first_run_per_problem(runs)
        self.assertCountEqual(first_ac, expected)


class ModelWithMockData(build_problem_rec_model.Model):
    '''Model subclass to allow using Mock data.'''

    def __init__(self, runs: pd.DataFrame, train_users: pd.Series,
                 test_users: pd.Series):
        parser = build_problem_rec_model.build_parser()
        args = parser.parse_args()
        config = build_problem_rec_model.TrainingConfig(args)
        super().__init__(config)

        self.runs = runs
        self.train_users = train_users
        self.test_users = test_users

    def load(
            self,
            dbconn: MySQLdb.connections.Connection
    ) -> Tuple[pd.DataFrame, pd.Series, pd.Series]:
        '''Simply return the stored mock data.'''
        # pylint: disable=unused-argument
        return self.runs, self.train_users, self.test_users


class TestModelGeneration(unittest.TestCase):
    '''Test model generation'''

    def test_single_recommendation(self) -> None:
        '''Tests single recommendation.'''
        runs = pd.DataFrame([(1, 1, 0),
                             (2, 1, 0),
                             (2, 2, 1),
                             (2, 3, 2)],
                            columns=['identity_id', 'problem_id', 'time'])
        train_users = pd.Series([2])
        test_users = pd.Series([1])
        model = ModelWithMockData(runs, train_users, test_users)
        model.build(None)
        recs = model.recommend(1, [], 1)
        self.assertCountEqual(recs, [2])

    def test_banned_recommendation(self) -> None:
        '''Tests single recommendation after solving the top recommendation.'''
        runs = pd.DataFrame([(1, 1, 0),
                             (2, 1, 0),
                             (2, 2, 1),
                             (2, 3, 2)],
                            columns=['identity_id', 'problem_id', 'time'])
        train_users = pd.Series([2])
        test_users = pd.Series([1])
        model = ModelWithMockData(runs, train_users, test_users)
        model.build(None)
        recs = model.recommend(1, [2], 1)
        self.assertCountEqual(recs, [3],
                              "Recommend 3 because 2 has already been solved.")


class TestModelEvaluation(unittest.TestCase):
    '''Test model evaluation.'''


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
