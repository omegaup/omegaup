#!/usr/bin/python3

'''Builds the user recommendation model.

This script reads user submissions and builds a recommendation model for
which problems to attempt after a user has just sucessfully solved a problem.

The model is a probability distribution of which problems a random user would
attempt to solve next, based solely on the sequence of recently solved problems
up to the latest one.
'''

import argparse
import logging
import sqlite3
import warnings

from typing import List, Tuple

import MySQLdb.connections

import numpy as np
import pandas as pd

import lib.db
import lib.logs


# Default training parameters.
_TRAIN_FRACTION = 0.8
_NUM_FOLLOWUPS = 3
_FOLLOWUP_DECAY = 0.4


# Type aliases for type checking
ProblemList = List[int]


def mean_average_precision(predicted: ProblemList,
                           expected: ProblemList,
                           k: int) -> float:
    '''Compute MAP@k - https://goo.gl/KMghXR'''
    if not predicted or not expected:
        return None
    num_problems = min(len(predicted), k)
    return sum((predicted[:num_problems] == expected[:num_problems]) *
               (1. / np.arange(1, num_problems + 1)))


def get_first_run_per_problem(runs: pd.DataFrame) -> pd.DataFrame:
    '''Get ordered lists of AC runs keyed by user.

    Args:
        runs (pd.DataFrame): Unordered DataFrame with AC runs with at least
                             identity_id, problem_id and time columns.
    Returns:
        pd.DataFrame indexed by identity_id with ordered list of
        recomendations.
    '''
    first_ac = runs.groupby(['identity_id', 'problem_id']).apply(
        lambda x: x.sort_values(['time']).head(1))
    logging.info('Found %d first AC runs', len(first_ac))

    return first_ac.reset_index(drop=True).groupby(['identity_id']).apply(
        lambda x: x.sort_values(['time']))


class TrainingConfig:
    '''A class to store the configuration for training a model.'''
    def __init__(self, args: argparse.Namespace):
        self.train_fraction = args.train_fraction
        self.num_followups = args.num_followups
        self.followup_decay = args.followup_decay


class Model:
    '''A class that represents a prediction model.
    '''
    def __init__(self, config: TrainingConfig):
        self.config = config

        # Set of users from the training data.
        self.users = None

        # Train/test runs
        self.train_runs = None
        self.test_runs = None
        self.train_ac = None
        self.test_ac = None

        # Actual recommendation model
        self.model = None

    def load(
            self,
            dbconn: MySQLdb.connections.Connection
    ) -> Tuple[pd.DataFrame, pd.Series, pd.Series]:
        '''Load runs and split them into test/train sets by user.

        Relevant runs are AC runs that were not part of a problemset.

        Ignoring problem sets helps remove bias in recommendations
        introduced by problem ordering in contests and tests.

        The split is done based on `self.config.train_fraction`.
        '''
        runs = pd.read_sql_query("""
            SELECT
                  s.identity_id
                , s.problem_id
                , s.time
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                    s.problemset_id IS NULL
                AND s.type = "normal"
                AND r.status = "ready"
                AND r.verdict = "AC";
            """, dbconn)
        logging.info('Found %d runs', len(runs))

        # Split dataset into test/train
        users = pd.Series(runs.identity_id.unique())
        train_users = users.sample(frac=self.config.train_fraction,
                                   random_state=self.rng_seed)
        test_users = users.drop(train_users.index)
        logging.info('Training users: %d', len(train_users))
        logging.info('Testing users: %d', len(test_users))

        return runs, train_users, test_users

    def save(self, output_path: str) -> None:
        '''Save a model to an Sqlite3 db in `output_path`.

        The DB contains a single table `ProblemRecommendations` with 3 columns:
        - solved_problem_id (int): The ID of the problem the user just solved.
        - recommended_problem_id (int): One recommendation.
        - score (float): A relative weight for this recommendation vs
                         others.

        The DB also has an index on `solved` for efficient lookups.
        '''
        with sqlite3.connect(output_path) as conn:
            try:
                cur = conn.cursor()
                cur.executescript(
                    '''CREATE TABLE ProblemRecommendations (
                        solved_problem_id INTEGER,
                        recommended_problem_id INTEGER,
                        score REAL
                    );
                    CREATE INDEX Recs_index ON ProblemRecommendations(solved);
                    ''')
                for solved_problem_id, recs in self.model:
                    for recommended_problem_id, score in recs:
                        cur.execute('''
                            INSERT INTO
                                ProblemRecommendations
                            VALUES
                                (?, ?, ?)
                            ''',
                                    (solved_problem_id, recommended_problem_id,
                                     score))
            finally:
                conn.commit()

    def generate_weighted_pairs(self) -> pd.DataFrame:
        '''Assumes runs are sorted by submission time'''
        tuples = []
        for _, problems in self.train_ac.groupby('identity_id'):
            num_problems = len(problems)
            # TODO: Figure out how to ask Pandas nicely for this.
            for i in range(num_problems):
                source = problems.iloc[i]['problem_id']
                weight = 1.0
                for j in range(1, self.config.num_followups + 1):
                    if i + j >= num_problems:
                        break
                    current = problems.iloc[i + j]['problem_id']
                    tuples.append((source, current, weight))
                    weight *= self.config.followup_decay

        output = pd.DataFrame(tuples)
        output.columns = ('source', 'target', 'weight')
        return output

    def recommend(self,
                  latest_problem: int,
                  banned_problems: ProblemList,
                  k: int) -> ProblemList:
        '''Recommends the a problem given that a user just solved last_problem.

        Args:
            model:
            latest_problem (int): The problem after which to make a
                                  recomendation.
            banned_problems (list(int)): A list or problems that shouldn't be
                                         recommended, for example, because the
                                         user already solved (or tried) them.

        Returns:
            list(int): A list of recommended problem_id to try next of the
                       recommended problem or None if no recommendation can be
                       made.
        '''
        try:
            recs = self.model.loc[latest_problem].reset_index(
                'target').sort_values(y='weight', ascending=False)['target']
            unsolved_recs = recs[~recs.isin(banned_problems)]
            return None if unsolved_recs.empty else unsolved_recs.iloc[0:k]
        except KeyError:
            return None

    def evaluate(self, k: int = None) -> float:
        '''Compute a score about how good the model is.'''
        if k is None:
            k = self.config.num_followups
        score = 0
        user_count = 0
        for _, runs in self.test_ac.groupby('identity_id'):
            problems = runs['problem_id']
            num_problems = len(problems)
            if num_problems <= 1:
                continue

            user_count += 1
            cur_score = 0
            for i in range(1, num_problems):
                recs = self.recommend(problems[i - 1], problems[:i - 1], k)
                # TODO: Use mean_average_precision() instead of manually
                #       computing something like it here.
                if recs is None:
                    continue
                rank = 1
                for predicted, expected in zip(recs, problems[i:i + k]):
                    if predicted == expected:
                        cur_score += 1. / rank
                    rank += 1

            score += cur_score / (num_problems - 1)

        return score / user_count

    def build(self, dbconn: MySQLdb.connections.Connection) -> None:
        '''Builds a recommendation model.'''
        runs, train_users, test_users = self.load(dbconn)

        # All AC runs groups by user.
        self.train_runs = runs[runs.identity_id.isin(train_users)]
        self.test_runs = runs[runs.identity_id.isin(test_users)]

        # Get the sequence of first AC runs grouped by user.
        self.train_ac = get_first_run_per_problem(self.train_runs)
        self.test_ac = get_first_run_per_problem(self.test_runs)

        weighted_pairs = self.generate_weighted_pairs()
        self.model = weighted_pairs.groupby(['source', 'target']).aggregate(
            sum)
        logging.info('Trained')


def build_parser() -> argparse.ArgumentParser:
    '''Returns a argparse.ArgumentParser for this tool.'''
    parser = argparse.ArgumentParser(
        description='Builds problem recommendation model.')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    # Model params
    model_args = parser.add_argument_group('Model')
    model_args.add_argument('--num_followups', type=int,
                            default=_NUM_FOLLOWUPS,
                            help='Number of problems to count')
    model_args.add_argument('--followup_decay', type=float,
                            default=_FOLLOWUP_DECAY,
                            help='The decay factor in followup problems\'s'
                                 'weight')

    # Training params
    training_args = parser.add_argument_group('Training')
    training_args.add_argument('--train_frac', type=float,
                               default=_TRAIN_FRACTION,
                               help='Fraction of data to use for training, '
                                    'leaving the rest for testing.')
    training_args.add_argument('--min_map_score', type=float, default=0.3,
                               help='Minimum MAP score to consider the '
                                    'training sucessful. Use to ensure we '
                                    'don\'t push bad models to prod.')
    # Output
    parser.add_argument('--output', type=str, help='Name of the output file '
                                                   'to write the model to.')

    return parser


def main():
    '''Main entrypoint.'''
    parser = build_parser()
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(args)
    warnings.filterwarnings('ignore', category=dbconn.Warning)
    try:
        model = Model(TrainingConfig(args))
        model.build(dbconn)

        score = model.evaluate()
        logging.info('Model MAP score: %f', eval)
        if score >= args.min_map_score:
            # Save current model
            model.save(args.output)
        else:
            logging.error('Model NOT saved. Resulting accuracy was too low: '
                          '%f below %f', score, args.min_map_score)
    except:  # noqa: bare-except
        logging.exception('Failed to update recommendation model.')
        raise
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
