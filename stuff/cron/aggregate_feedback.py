#!/usr/bin/env python3

import argparse
import calendar
import collections
import datetime
import json
import logging
import operator
import os
import sys
from typing import DefaultDict, Dict, Mapping, NamedTuple, Optional, Sequence, Set, Tuple

# Import necessary MySQL connector modules and configurations
from mysql.connector import errorcode

# Insert the directory containing the custom libraries to the sys path
sys.path.insert(0, os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))

# Import custom libraries for database connection and logging
import lib.db
import lib.logs

# Define constants and configurations
CONFIDENCE = 10
MIN_POINTS = 10
PROBLEM_TAG_VOTE_MIN_PROPORTION = 0.25
MAX_NUM_TOPICS = 5
VOTES_NUM = 5
QUALITYNOMINATION_QUESTION_CHANGE_ID = 18663

# SQL Queries
GET_ALL_SCORES_AND_SUGGESTIONS = """
    SELECT qn.contents, ur.score
    FROM QualityNominations AS qn
    LEFT JOIN User_Rank AS ur ON ur.user_id = qn.user_id
    WHERE qn.nomination = 'suggestion'
    AND qn.qualitynomination_id > %s
    AND qn.qualitynomination_id IN (
        SELECT MAX(qualitynomination_id)
        FROM QualityNominations
        WHERE user_id = qn.user_id AND problem_id = qn.problem_id
    );"""

GET_PROBLEM_SCORES_AND_SUGGESTIONS = """
    SELECT qn.contents, ur.score
    FROM QualityNominations AS qn
    LEFT JOIN User_Rank AS ur ON ur.user_id = qn.user_id
    WHERE qn.nomination = 'suggestion'
    AND qn.qualitynomination_id > %s
    AND qn.problem_id = %s
    AND qn.qualitynomination_id IN (
        SELECT MAX(qualitynomination_id)
        FROM QualityNominations
        WHERE user_id = qn.user_id AND problem_id = qn.problem_id
    );"""

WEIGHTING_FACTORS = {
    'user-rank-unranked': 2,
    'user-rank-beginner': 2,
    'user-rank-specialist': 3,
    'user-rank-expert': 4,
    'user-rank-master': 5,
    'user-rank-international-master': 6,
}

BEFORE_AC_WEIGHTING_FACTORS = {
    'user-rank-unranked': 0,
    'user-rank-beginner': 0,
    'user-rank-specialist': 0,
    'user-rank-expert': 0,
    'user-rank-master': 0,
    'user-rank-international-master': 0,
}


# Define a class to represent votes
class Votes:
    def __init__(self, count: int = 0, weighted_sum: int = 0) -> None:
        self.count = count
        self.weighted_sum = weighted_sum


# Define a named tuple to represent rank cutoff
class RankCutoff(NamedTuple):
    classname: str
    score: float


def fill_rank_cutoffs(dbconn: lib.db.Connection) -> Sequence[RankCutoff]:
    # Retrieve rank cutoffs from the database and fill the collection
    with dbconn.cursor() as cur:
        cur.execute("""SELECT urc.classname, urc.score
                       FROM User_Rank_Cutoffs AS urc
                       ORDER BY urc.percentile ASC;""")
        return [RankCutoff(classname, score) for classname, score in cur.fetchall()]


# Define a function to get the weighting factor based on user score and rank cutoffs
def get_weighting_factor(score: Optional[float], rank_cutoffs: Sequence[RankCutoff], weighting_factors: Mapping[str, int]) -> int:
    if score is None:
        return weighting_factors['user-rank-unranked']
    for cutoff in rank_cutoffs:
        if cutoff.score <= score:
            return weighting_factors[cutoff.classname]
    return weighting_factors['user-rank-unranked']


# Define a function to calculate the Bayesian average of an observation
def bayesian_average(apriori_average: Optional[float], values: Sequence[Votes]) -> Optional[float]:
    weighted_n = 0
    weighted_sum = 0
    for i, vote in enumerate(values):
        weighted_n += vote.weighted_sum
        weighted_sum += i * vote.weighted_sum

    if weighted_n < CONFIDENCE or apriori_average is None:
        return None

    return (CONFIDENCE * apriori_average + weighted_sum) / float(CONFIDENCE + weighted_n)


# Define a function to get the most voted tags for a problem
def get_most_voted_tags(problem_tag_votes: Mapping[str, float], problem_tag_votes_n: int) -> Optional[Sequence[str]]:
    if problem_tag_votes_n < MIN_POINTS:
        return None
    maximum = problem_tag_votes[max(problem_tag_votes, key=lambda x: problem_tag_votes.get(x, 0))]
    final_tags = [tag for (tag, votes) in problem_tag_votes.items() if votes >= PROBLEM_TAG_VOTE_MIN_PROPORTION * maximum]
    if len(final_tags) >= MAX_NUM_TOPICS:
        return None
    return final_tags


# Define a function to replace voted tags for a problem
def replace_voted_tags(dbconn: lib.db.Connection, problem_id: int, problem_tags: Sequence[str]) -> None:
    try:
        with dbconn.cursor() as cur:
            cur.execute("""DELETE FROM Problems_Tags WHERE problem_id = %s AND source = 'voted';""", (problem_id,))
            get_warnings = dbconn.conn.get_warnings
            try:
                dbconn.conn.get_warnings = True
                cur.execute("""INSERT IGNORE INTO Problems_Tags(problem_id, tag_id, source)
                               SELECT %s AS problem_id, t.tag_id AS tag_id, 'voted' AS source
                               FROM Tags AS t WHERE t.name IN (%s);""",
                            (problem_id, ) + tuple(problem_tags))
                for level, code, message in (cur.fetchwarnings() or []):
                    if code == errorcode.ER_DUP_ENTRY:
                        continue
                    logging.warning('Warning while updated tags in problem %d: %r', problem_id, (level, code, message))
            finally:
                dbconn.conn.get_warnings = get_warnings
            dbconn.conn.commit()
    except Exception as e:
        logging.exception('Failed to replace voted tags')
        dbconn.conn.rollback()


# Define a function to aggregate feedback for a problem
def aggregate_problem_feedback(dbconn: lib.db.Connection, problem_id: int, rank_cutoffs: Sequence[RankCutoff],
                               global_quality_average: Optional[float], global_difficulty_average: Optional[float]) -> None:
    quality_votes = [Votes() for _ in range(VOTES_NUM)]
    difficulty_votes = [Votes() for _ in range(VOTES_NUM)]
    problem_tag_votes: Dict[str, int] = collections.defaultdict(int)
    problem_tag_votes_n = 0

    with dbconn.cursor() as cur:
        cur.execute(GET_PROBLEM_SCORES_AND_SUGGESTIONS, (QUALITYNOMINATION_QUESTION_CHANGE_ID, problem_id,))
        for row in cur.fetchall():
            contents = json.loads(row[0])
            before_ac = contents.get('before_ac', False)
            user_score = row[1]
            weighting_factor = get_weighting_factor(user_score, rank_cutoffs, WEIGHTING_FACTORS if not before_ac else BEFORE_AC_WEIGHTING_FACTORS)
            if 'quality' in contents and contents['quality'] is not None:
                quality_votes[contents['quality']].count += 1 if not before_ac else 0
                quality_votes[contents['quality']].weighted_sum += weighting_factor
            if 'difficulty' in contents and contents['difficulty'] is not None:
                difficulty_votes[contents['difficulty']].count += 1 if not before_ac else 0
                difficulty_votes[contents['difficulty']].weighted_sum += weighting_factor
            if 'tags' in contents and contents['tags']:
                for tag in contents['tags']:
                    problem_tag_votes[tag] += weighting_factor
                    problem_tag_votes_n += weighting_factor

    # Calculate Bayesian averages for quality and difficulty
    problem_quality = bayesian_average(global_quality_average, quality_votes)
    problem_difficulty = bayesian_average(global_difficulty_average, difficulty_votes)

    if problem_quality is not None:
        logging.debug('quality=%f', problem_quality)
        with dbconn.cursor() as cur:
            cur.execute("""UPDATE Problems AS p SET p.quality = %s, p.quality_histogram = %s WHERE p.problem_id = %s;""",
                        (problem_quality, json.dumps([vote.count for vote in quality_votes]), problem_id))
    if problem_difficulty is not None:
        logging.debug('difficulty=%f', problem_difficulty)
        with dbconn.cursor() as cur:
            cur.execute("""UPDATE Problems AS p SET p.difficulty = %s, p.difficulty_histogram = %s WHERE p.problem_id = %s;""",
                        (problem_difficulty, json.dumps([vote.count for vote in difficulty_votes]), problem_id))
    dbconn.conn.commit()

    # Replace voted tags for the problem
    problem_tags = get_most_voted_tags(problem_tag_votes, problem_tag_votes_n)
    if problem_tags:
        replace_voted_tags(dbconn, problem_id, problem_tags)


# Define a function to aggregate feedback for all problems
def aggregate_feedback(dbconn: lib.db.Connection) -> None:
    rank_cutoffs = fill_rank_cutoffs(dbconn)
    global_quality_average, global_difficulty_average = get_global_quality_and_difficulty_average(dbconn, rank_cutoffs)

    with dbconn.cursor() as cur:
        cur.execute("""SELECT DISTINCT qn.problem_id FROM QualityNominations AS qn WHERE qn.nomination = 'suggestion'
                       AND qn.qualitynomination_id > %s;""", (QUALITYNOMINATION_QUESTION_CHANGE_ID,))
        for row in cur.fetchall():
            aggregate_problem_feedback(dbconn, row[0], rank_cutoffs, global_quality_average, global_difficulty_average)


# Define a function to get the global quality and difficulty averages
def get_global_quality_and_difficulty_average(dbconn: lib.db.Connection, rank_cutoffs: Sequence[RankCutoff]) -> Tuple[Optional[float], Optional[float]]:
    with dbconn.cursor() as cur:
        cur.execute(GET_ALL_SCORES_AND_SUGGESTIONS, (QUALITYNOMINATION_QUESTION_CHANGE_ID,))
        quality_sum, quality_n, difficulty_sum, difficulty_n = 0, 0, 0, 0
        for contents_str, user_score in cur.fetchall():
            try:
                contents = json.loads(contents_str)
            except json.JSONDecodeError:
                logging.exception('Failed to parse contents')
                continue

            before_ac = contents.get('before_ac', False)
            weighting_factor = get_weighting_factor(user_score, rank_cutoffs, WEIGHTING_FACTORS if not before_ac else BEFORE_AC_WEIGHTING_FACTORS)

            if 'quality' in contents and contents['quality'] is not None:
                quality_sum += contents['quality'] * weighting_factor
                quality_n += weighting_factor

            if 'difficulty' in contents and contents['difficulty'] is not None:
                difficulty_sum += contents['difficulty'] * weighting_factor
                difficulty_n += weighting_factor

        global_quality_average = quality_sum / quality_n if quality_n > 0 else None
        global_difficulty_average = difficulty_sum / difficulty_n if difficulty_n > 0 else None

        return global_quality_average, global_difficulty_average


# Define the main function
def main() -> None:
    # Parse command-line arguments
    parser = argparse.ArgumentParser(description='Aggregate feedback for all problems')
    parser.add_argument('--config', type=str, help='Path to configuration file')
    args = parser.parse_args()

    # Load configuration from file
    config = lib.config.load(args.config) if args.config else {}

    # Set up logging
    lib.logs.setup_logging(config)

    # Connect to the database
    try:
        dbconn = lib.db.connect(config)
    except Exception:
        logging.exception('Failed to connect to the database')
        sys.exit(1)

    # Aggregate feedback for all problems
    aggregate_feedback(dbconn)

    # Close the database connection
    dbconn.close()


if __name__ == '__main__':
    main()
