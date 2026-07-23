#!/usr/bin/env python3
'''Unittest for the recommendation model builder script.

These are function-level unittests for the recommendation model builder.
Integration tests should be done via a PHP entry point.
'''

import os.path
import unittest
from typing import Any, List, Tuple, cast

import pandas as pd  # type: ignore

import build_problem_rec_model
import lib.db  # pylint: disable=wrong-import-position,wrong-import-order


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


class _FakeCursor:
    '''Records executed statements so the insert can be asserted on.'''

    def __init__(self, connection: '_FakeConnection') -> None:
        self._connection = connection
        self.lastrowid = 1

    def execute(self, query: str, params: Any = None) -> None:
        '''Records the executed statement and its params.'''
        self._connection.calls.append((query, params))

    def fetchone(self) -> Any:
        '''Returns the scripted row for the last query.'''
        return self._connection.next_fetchone

    def __enter__(self) -> '_FakeCursor':
        return self

    def __exit__(self, exc_type: Any, exc: Any, traceback: Any) -> None:
        del exc_type, exc, traceback


class _FakeConnection:
    '''Minimal in-memory stand-in for lib.db.Connection.'''

    def __init__(self) -> None:
        self.calls: List[Tuple[str, Any]] = []
        self.commits = 0
        self.next_fetchone: Any = None
        self.conn = self

    def cursor(self, **_kwargs: Any) -> _FakeCursor:
        '''Returns a fake cursor bound to this connection.'''
        return _FakeCursor(self)

    def commit(self) -> None:
        '''Counts commits so the test can assert one happened.'''
        self.commits += 1


class TestRecordModelRun(unittest.TestCase):
    '''Test recording a training run into Recommendation_Model_Runs.'''

    def test_record_model_run_inserts_published_row(self) -> None:
        '''A published run is stored with published set to 1.'''
        conn = _FakeConnection()
        config = build_problem_rec_model.TrainingConfig(
            train_fraction=0.8, num_followups=3, followup_decay=0.4)

        build_problem_rec_model.record_model_run(
            cast(lib.db.Connection, conn),
            cron_run_id=42,
            config=config,
            map_score=0.51,
            dataset_size=1234,
            output_path='/tmp/model.db',
            published=True,
            skip_reason=None)

        self.assertEqual(len(conn.calls), 1)
        query, params = conn.calls[0]
        self.assertIn('Recommendation_Model_Runs', query)
        self.assertEqual(
            params,
            (42, 0.51, 1234, 3, 0.4, 0.8, '/tmp/model.db', 1, None))
        self.assertEqual(conn.commits, 1)

    def test_record_unpublished_stores_skip_reason(self) -> None:
        '''An unpublished run stores published=0 and the skip reason.'''
        conn = _FakeConnection()
        config = build_problem_rec_model.TrainingConfig()

        build_problem_rec_model.record_model_run(
            cast(lib.db.Connection, conn),
            cron_run_id=None,
            config=config,
            map_score=0.12,
            dataset_size=10,
            output_path='/tmp/model.db',
            published=False,
            skip_reason='MAP score 0.1200 below minimum 0.3000')

        _query, params = conn.calls[0]
        self.assertIsNone(params[0])
        self.assertEqual(params[7], 0)
        self.assertEqual(params[8], 'MAP score 0.1200 below minimum 0.3000')


class TestShouldPublish(unittest.TestCase):
    '''Test the write-audit-publish guardrail decision.'''

    def test_publishes_when_above_floor_and_no_baseline(self) -> None:
        '''A good first model with no previous baseline is published.'''
        published, reason = build_problem_rec_model.should_publish(
            score=0.40,
            min_map_score=0.30,
            last_published_map=None,
            max_map_regression=0.05)
        self.assertTrue(published)
        self.assertIsNone(reason)

    def test_rejects_below_absolute_floor(self) -> None:
        '''A model below the minimum MAP is not published.'''
        published, reason = build_problem_rec_model.should_publish(
            score=0.20,
            min_map_score=0.30,
            last_published_map=None,
            max_map_regression=0.05)
        self.assertFalse(published)
        assert reason is not None
        self.assertIn('below minimum', reason)

    def test_rejects_regression_beyond_tolerance(self) -> None:
        '''Too large a regression from the baseline is not published.'''
        published, reason = build_problem_rec_model.should_publish(
            score=0.40,
            min_map_score=0.30,
            last_published_map=0.50,
            max_map_regression=0.05)
        self.assertFalse(published)
        assert reason is not None
        self.assertIn('regressed', reason)

    def test_allows_small_regression_within_tolerance(self) -> None:
        '''A model within the regression tolerance is still published.'''
        published, reason = build_problem_rec_model.should_publish(
            score=0.47,
            min_map_score=0.30,
            last_published_map=0.50,
            max_map_regression=0.05)
        self.assertTrue(published)
        self.assertIsNone(reason)


class TestGetLastPublishedMap(unittest.TestCase):
    '''Test reading the last published MAP for the guardrail baseline.'''

    def test_returns_score_when_present(self) -> None:
        '''Returns the queried MAP score as a float.'''
        conn = _FakeConnection()
        conn.next_fetchone = (0.42,)
        result = build_problem_rec_model.get_last_published_map(
            cast(lib.db.Connection, conn))
        self.assertEqual(result, 0.42)

    def test_returns_none_when_no_published_model(self) -> None:
        '''Returns None when there is no published model yet.'''
        conn = _FakeConnection()
        conn.next_fetchone = None
        result = build_problem_rec_model.get_last_published_map(
            cast(lib.db.Connection, conn))
        self.assertIsNone(result)


if __name__ == '__main__':
    unittest.main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
