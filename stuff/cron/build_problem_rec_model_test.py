#!/usr/bin/python3
'''Unittest for the recommendation model builder script.

These are function-level unittests for the recommendation model builder.
Integration tests should be done via a PHP entry point.
'''

import os.path
import unittest
from typing import Any

import pandas as pd  # type: ignore

import build_problem_rec_model


class TestModelGeneration(unittest.TestCase):
    '''Test model generation'''

    def __init__(self, *args: Any, **kwargs: Any) -> None:
        super().__init__(*args, **kwargs)
        runs = pd.DataFrame([(1, 1, 0),
                             (2, 1, 0),
                             (2, 2, 1),
                             (2, 3, 2)],
                            columns=['identity_id', 'problem_id', 'time'])
        self.model = build_problem_rec_model.Model(
            build_problem_rec_model.TrainingConfig(), runs)

    def test_single_recommendation(self) -> None:
        '''Tests single recommendation.'''
        recs = self.model.recommend(1, set(), 1)
        assert recs is not None
        self.assertCountEqual(recs, [2])

    def test_banned_recommendation(self) -> None:
        '''Tests single recommendation after solving the top recommendation.'''
        recs = self.model.recommend(1, set([2]), 1)
        assert recs is not None
        self.assertCountEqual(
            recs, [3], "Recommend 3 because 2 has already been solved.")


class TestModelEvaluation(unittest.TestCase):
    '''Test model evaluation.'''
    def test_build_model_from_sqlite(self) -> None:
        '''Tests that a Model.evaluate() returns a decent value.'''
        model = build_problem_rec_model.Model(
            build_problem_rec_model.TrainingConfig(),
            build_problem_rec_model.load_sqlite(
                os.path.join(os.path.dirname(os.path.realpath(__file__)),
                             'testdata.db')))
        self.assertGreater(model.evaluate(), 0.3)


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
