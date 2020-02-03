#!/usr/bin/python3

'''Unittest for the recommendation model builder script.

These are function-level unittests for the recommendation model builder.
Integration tests should be done via a PHP entry point.
'''

import unittest

import pandas as pd  # type: ignore

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
        parser = build_problem_rec_model.build_parser()
        args = parser.parse_args()
        config = build_problem_rec_model.TrainingConfig(args)
        model = build_problem_rec_model.Model(config)
        model.build(runs, train_users, test_users)
        recs = model.recommend(1, [], 1)
        assert recs is not None
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
        parser = build_problem_rec_model.build_parser()
        args = parser.parse_args()
        config = build_problem_rec_model.TrainingConfig(args)
        model = build_problem_rec_model.Model(config)
        model.build(runs, train_users, test_users)
        recs = model.recommend(1, [2], 1)
        assert recs is not None
        self.assertCountEqual(recs, [3],
                              "Recommend 3 because 2 has already been solved.")


class TestModelEvaluation(unittest.TestCase):
    '''Test model evaluation.'''


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
