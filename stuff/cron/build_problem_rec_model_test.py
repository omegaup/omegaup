#!/usr/bin/env python3
'''Unittest for the recommendation model builder script.

These are function-level unittests for the recommendation model builder.
Integration tests should be done via a PHP entry point.
'''

import math
import os.path
import unittest
from typing import Any, Callable, Dict, List, Optional

import pandas as pd  # type: ignore

import build_problem_rec_model

_TESTDATA = os.path.join(os.path.dirname(os.path.realpath(__file__)),
                         'testdata.db')
_MetricFn = Callable[[List[int], List[int], int], Optional[float]]
_METRICS: Dict[str, _MetricFn] = {
    'precision': build_problem_rec_model.precision_at_k,
    'recall': build_problem_rec_model.recall_at_k,
    'map': build_problem_rec_model.average_precision_at_k,
    'ndcg': build_problem_rec_model.ndcg_at_k,
}


def _measure(predicted: List[int], expected: List[int],
             k: int) -> Dict[str, float]:
    '''Runs every metric on one prediction and asserts each is defined.'''
    values = {}
    for name, metric in _METRICS.items():
        value = metric(predicted, expected, k)
        assert value is not None, name
        values[name] = value
    return values


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
            build_problem_rec_model.load_sqlite(_TESTDATA))
        self.assertGreater(model.evaluate(), 0.3)

    def test_evaluate_is_unchanged_for_the_fixture(self) -> None:
        '''The published score bar is pinned to a known fixture value.'''
        model = build_problem_rec_model.Model(
            build_problem_rec_model.TrainingConfig(),
            build_problem_rec_model.load_sqlite(_TESTDATA))
        self.assertAlmostEqual(model.evaluate(), 0.3419, places=4)

    def test_evaluate_metrics_on_the_fixture(self) -> None:
        '''evaluate_metrics returns all four metrics inside [0, 1].'''
        model = build_problem_rec_model.Model(
            build_problem_rec_model.TrainingConfig(),
            build_problem_rec_model.load_sqlite(_TESTDATA))
        metrics = model.evaluate_metrics()
        self.assertEqual(set(metrics), {'precision', 'recall', 'map', 'ndcg'})
        for name, value in metrics.items():
            self.assertGreaterEqual(value, 0., name)
            self.assertLessEqual(value, 1., name)
        self.assertGreater(metrics['map'], 0.)

    def test_evaluate_metrics_are_consistent(self) -> None:
        '''Recall cannot be below precision when k exceeds the relevant set.

        Every prediction has at most `k` expected problems, so each relevant
        problem found contributes at least as much to recall as to precision.
        '''
        model = build_problem_rec_model.Model(
            build_problem_rec_model.TrainingConfig(),
            build_problem_rec_model.load_sqlite(_TESTDATA))
        metrics = model.evaluate_metrics()
        self.assertGreaterEqual(metrics['recall'], metrics['precision'])


class TestMetrics(unittest.TestCase):
    '''Hand computed checks for the ranking metric helpers.'''

    def test_partial_hit(self) -> None:
        '''One relevant problem at rank 2 out of three recommendations.'''
        got = _measure([10, 20, 30], [20, 40], 3)

        # 1 of the 3 recommendations is relevant.
        self.assertAlmostEqual(got['precision'], 1 / 3)
        # 1 of the 2 relevant problems was recovered.
        self.assertAlmostEqual(got['recall'], 0.5)
        # The only hit is at rank 2, so P@2 = 1/2, over min(2, 3) = 2.
        self.assertAlmostEqual(got['map'], 0.25)
        # DCG = 1/log2(3); IDCG puts both relevant problems first.
        self.assertAlmostEqual(got['ndcg'],
                               (1 / math.log2(3)) / (1 + 1 / math.log2(3)))

    def test_hit_at_first_rank_scores_higher(self) -> None:
        '''The same hit is worth more the higher it is ranked.'''
        top = _measure([20, 10, 30], [20, 40], 3)
        bottom = _measure([10, 30, 20], [20, 40], 3)

        # Precision and recall are rank blind.
        self.assertEqual(top['precision'], bottom['precision'])
        self.assertEqual(top['recall'], bottom['recall'])
        # MAP and NDCG are not: the hit moves from rank 1 to rank 3.
        self.assertAlmostEqual(top['map'], 1 / 2)
        self.assertAlmostEqual(bottom['map'], 1 / 3 / 2)
        self.assertAlmostEqual(top['ndcg'], 1 / (1 + 1 / math.log2(3)))
        self.assertAlmostEqual(bottom['ndcg'],
                               (1 / 2) / (1 + 1 / math.log2(3)))

    def test_perfect_ranking(self) -> None:
        '''Every recommendation is relevant and in order.'''
        for name, value in _measure([1, 2, 3], [1, 2, 3], 3).items():
            self.assertAlmostEqual(value, 1.0, msg=name)

    def test_no_hit(self) -> None:
        '''No recommendation is relevant.'''
        for name, value in _measure([1, 2], [3, 4], 2).items():
            self.assertEqual(value, 0.0, msg=name)

    def test_ndcg_ignores_the_order_of_the_expected_problems(self) -> None:
        '''IDCG is the ideal ordering, not the order the user solved in.'''
        self.assertAlmostEqual(
            _measure([10, 20, 30], [20, 40], 3)['ndcg'],
            _measure([10, 20, 30], [40, 20], 3)['ndcg'])

    def test_fewer_relevant_than_k(self) -> None:
        '''A single relevant problem found first is a perfect MAP and NDCG.'''
        got = _measure([7, 8, 9], [7], 3)
        self.assertAlmostEqual(got['precision'], 1 / 3)
        self.assertAlmostEqual(got['recall'], 1.0)
        # min(relevant, k) = 1, and IDCG only counts the one relevant problem.
        self.assertAlmostEqual(got['map'], 1.0)
        self.assertAlmostEqual(got['ndcg'], 1.0)

    def test_shorter_prediction_list(self) -> None:
        '''Precision still divides by k when fewer than k are returned.'''
        got = _measure([5], [5, 6], 3)
        self.assertAlmostEqual(got['precision'], 1 / 3)
        self.assertAlmostEqual(got['recall'], 0.5)
        self.assertAlmostEqual(got['map'], 0.5)
        self.assertAlmostEqual(got['ndcg'], 1 / (1 + 1 / math.log2(3)))

    def test_empty_expected_returns_none(self) -> None:
        '''Metrics are undefined when there is nothing to predict.'''
        for name, metric in _METRICS.items():
            self.assertIsNone(metric([1, 2], [], 3), name)

    def test_non_positive_k_returns_none(self) -> None:
        '''Metrics are undefined for an empty cutoff.'''
        for name, metric in _METRICS.items():
            self.assertIsNone(metric([1, 2], [1], 0), name)


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
