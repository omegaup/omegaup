'''Typed sample fixtures shared across cron unit tests.'''
from typing import Dict, List, Sequence

from cron import aggregate_feedback
from cron import utils

SAMPLE_RANK_CUTOFFS: Sequence[aggregate_feedback.RankCutoff] = (
    aggregate_feedback.RankCutoff(
        classname='user-rank-international-master', score=1900.0),
    aggregate_feedback.RankCutoff(
        classname='user-rank-master', score=1500.0),
    aggregate_feedback.RankCutoff(
        classname='user-rank-expert', score=1100.0),
    aggregate_feedback.RankCutoff(
        classname='user-rank-specialist', score=800.0),
    aggregate_feedback.RankCutoff(
        classname='user-rank-beginner', score=500.0),
    aggregate_feedback.RankCutoff(
        classname='user-rank-unranked', score=0.0),
)


def make_quality_votes(
        counts_by_score: Sequence[int]) -> List[aggregate_feedback.Votes]:
    '''Build a Votes list of length VOTES_NUM from per-score weighted sums.'''
    if len(counts_by_score) != aggregate_feedback.VOTES_NUM:
        raise ValueError(
            f'expected {aggregate_feedback.VOTES_NUM} buckets, '
            f'got {len(counts_by_score)}')
    return [
        aggregate_feedback.Votes(count=ws, weighted_sum=ws)
        for ws in counts_by_score
    ]


SAMPLE_USER_RANK = utils.UserRank(
    user_id=42,
    identity_id=42,
    username='alice',
    country_id='MX',
    school_id=1,
    problems_solved=120,
    score=1450.0,
    classname='user-rank-expert',
)


SAMPLE_TAG_VOTES_SINGLE_DOMINANT: Dict[str, float] = {
    'math': 20.0,
    'graph-theory': 1.0,
}

SAMPLE_TAG_VOTES_AT_MIN_PROPORTION: Dict[str, float] = {
    'math': 20.0,
    'graph-theory': 5.0,
}

SAMPLE_TAG_VOTES_EXCEED_LIMIT: Dict[str, float] = {
    'math': 10.0,
    'graph-theory': 10.0,
    'greedy': 10.0,
    'dp': 10.0,
    'strings': 10.0,
}
