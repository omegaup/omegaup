#!/usr/bin/python3

'''Unittest for the recommendation model builder script.

These are function-level unittests for the recommendation model builder.
Integration tests should be done via a PHP entry point.
'''

import os.path
import sys
import unittest

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


class TestModelGeneration(unittest.TestCase):
    '''Test...'''
    pass


class TestModelEvaluation(unittest.TestCase):
    '''Test...'''
    pass


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
