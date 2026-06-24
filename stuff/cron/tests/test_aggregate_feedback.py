'''Unit tests for the pure functions in `aggregate_feedback.py`.'''
import unittest

from cron import aggregate_feedback
from cron.tests.fixtures import sample_data


class BayesianAverageTest(unittest.TestCase):
    '''Tests for `aggregate_feedback.bayesian_average`.'''

    def test_returns_none_when_prior_is_none(self) -> None:
        '''With no global prior, no per-problem average can be produced.'''
        votes = sample_data.make_quality_votes([0, 0, 0, 0, 20])
        self.assertIsNone(aggregate_feedback.bayesian_average(None, votes))

    def test_returns_none_when_weighted_n_below_confidence(self) -> None:
        '''Below CONFIDENCE (10) the sample is not yet trusted.'''
        votes = sample_data.make_quality_votes([0, 0, 5, 0, 0])
        self.assertIsNone(
            aggregate_feedback.bayesian_average(3.0, votes))

    def test_returns_value_when_weighted_n_at_confidence(self) -> None:
        '''At exactly CONFIDENCE the function returns a number.'''
        votes = sample_data.make_quality_votes([0, 0, 10, 0, 0])
        result = aggregate_feedback.bayesian_average(3.0, votes)
        self.assertIsNotNone(result)
        assert result is not None
        self.assertAlmostEqual(result, 2.5)

    def test_returns_value_when_weighted_n_above_confidence(self) -> None:
        '''Large sample dominates the prior. Result converges to 230/60.'''
        votes = sample_data.make_quality_votes([0, 0, 0, 0, 50])
        result = aggregate_feedback.bayesian_average(3.0, votes)
        self.assertIsNotNone(result)
        assert result is not None
        self.assertAlmostEqual(result, 230.0 / 60.0)

    def test_returns_value_when_apriori_is_zero(self) -> None:
        '''A 0.0 prior is a valid value, not a missing one.'''
        votes = sample_data.make_quality_votes([0, 0, 10, 0, 0])
        result = aggregate_feedback.bayesian_average(0.0, votes)
        self.assertIsNotNone(result)
        assert result is not None
        self.assertAlmostEqual(result, 1.0)

    def test_returns_none_when_all_zero_votes(self) -> None:
        '''No votes equals below-confidence; return None.'''
        votes = sample_data.make_quality_votes([0, 0, 0, 0, 0])
        self.assertIsNone(
            aggregate_feedback.bayesian_average(3.0, votes))

    def test_handles_extreme_weighted_sum(self) -> None:
        '''Asymptotically the result stays bounded by the top score (4).'''
        votes = sample_data.make_quality_votes([0, 0, 0, 0, 10_000])
        result = aggregate_feedback.bayesian_average(3.0, votes)
        self.assertIsNotNone(result)
        assert result is not None
        self.assertGreater(result, 3.99)
        self.assertLess(result, 4.0)


class GetWeightingFactorTest(unittest.TestCase):
    '''Tests for `aggregate_feedback.get_weighting_factor`.'''

    def test_returns_unranked_when_score_is_none(self) -> None:
        '''A user with no score falls back to the unranked weight.'''
        result = aggregate_feedback.get_weighting_factor(
            None,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS['user-rank-unranked'],
        )

    def test_score_below_all_cutoffs_returns_unranked(self) -> None:
        '''A score under every cutoff falls through to the unranked weight.'''
        result = aggregate_feedback.get_weighting_factor(
            -1.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS['user-rank-unranked'],
        )

    def test_international_master_threshold(self) -> None:
        '''Score above the international master cutoff returns that weight.'''
        result = aggregate_feedback.get_weighting_factor(
            2000.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS[
                'user-rank-international-master'],
        )

    def test_master_threshold(self) -> None:
        '''Score above the master cutoff returns the master weight.'''
        result = aggregate_feedback.get_weighting_factor(
            1600.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS['user-rank-master'],
        )

    def test_expert_threshold(self) -> None:
        '''Score above the expert cutoff returns the expert weight.'''
        result = aggregate_feedback.get_weighting_factor(
            1200.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS['user-rank-expert'],
        )

    def test_specialist_threshold(self) -> None:
        '''Score above the specialist cutoff returns the specialist weight.'''
        result = aggregate_feedback.get_weighting_factor(
            900.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS['user-rank-specialist'],
        )

    def test_beginner_threshold(self) -> None:
        '''Score above the beginner cutoff returns the beginner weight.'''
        result = aggregate_feedback.get_weighting_factor(
            600.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS['user-rank-beginner'],
        )

    def test_score_exactly_at_cutoff_boundary(self) -> None:
        '''Score equal to a cutoff still qualifies (the predicate is `<=`).'''
        result = aggregate_feedback.get_weighting_factor(
            1100.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.WEIGHTING_FACTORS['user-rank-expert'],
        )

    def test_before_ac_weighting_factors_table(self) -> None:
        '''Sanity check the same lookup works against the before-AC table.'''
        result = aggregate_feedback.get_weighting_factor(
            2000.0,
            sample_data.SAMPLE_RANK_CUTOFFS,
            aggregate_feedback.BEFORE_AC_WEIGHTING_FACTORS,
        )
        self.assertEqual(
            result,
            aggregate_feedback.BEFORE_AC_WEIGHTING_FACTORS[
                'user-rank-international-master'],
        )


class GetMostVotedTagsTest(unittest.TestCase):
    '''Tests for `aggregate_feedback.get_most_voted_tags`.'''

    def test_returns_none_below_min_points(self) -> None:
        '''Below MIN_POINTS the function should not commit to any tags.'''
        result = aggregate_feedback.get_most_voted_tags(
            {'math': 5.0}, 5)
        self.assertIsNone(result)

    def test_single_dominant_tag_is_returned(self) -> None:
        '''When one tag dominates, only that tag is kept.'''
        result = aggregate_feedback.get_most_voted_tags(
            sample_data.SAMPLE_TAG_VOTES_SINGLE_DOMINANT,
            21,
        )
        self.assertIsNotNone(result)
        assert result is not None
        self.assertEqual(list(result), ['math'])

    def test_at_min_proportion_keeps_both_tags(self) -> None:
        '''A tag exactly at 0.25 of the maximum survives because of `>=`.'''
        result = aggregate_feedback.get_most_voted_tags(
            sample_data.SAMPLE_TAG_VOTES_AT_MIN_PROPORTION,
            25,
        )
        self.assertIsNotNone(result)
        assert result is not None
        self.assertEqual(set(result), {'math', 'graph-theory'})

    def test_returns_none_when_more_than_max_topics_match(self) -> None:
        '''If too many tags qualify, return None to avoid noisy tagging.'''
        result = aggregate_feedback.get_most_voted_tags(
            sample_data.SAMPLE_TAG_VOTES_EXCEED_LIMIT,
            50,
        )
        self.assertIsNone(result)

    def test_empty_votes_returns_none(self) -> None:
        '''An empty vote map yields no tags instead of crashing on max().'''
        result = aggregate_feedback.get_most_voted_tags(
            {}, aggregate_feedback.MIN_POINTS)
        self.assertIsNone(result)


if __name__ == '__main__':
    unittest.main()
