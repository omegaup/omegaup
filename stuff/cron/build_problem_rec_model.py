#!/usr/bin/env python3
'''Builds the user recommendation model.

This script reads user submissions and builds a recommendation model for
which problems to attempt after a user has just successfully solved a problem.

The model is a probability distribution of which problems a random user would
attempt to solve next, based solely on the sequence of recently solved problems
up to the latest one.
'''

import argparse
import collections
import logging
import os
import os.path
import sqlite3
import sys
from typing import (cast, DefaultDict, Dict, List, Mapping, Optional, Sequence,
                    Set, Tuple)

import numpy as np  # type: ignore
import pandas as pd  # type: ignore

sys.path.insert(0,
                os.path.dirname(os.path.dirname(os.path.realpath(__file__))))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

# Default training parameters.
_TRAIN_FRACTION = 0.8
_NUM_FOLLOWUPS = 3
_FOLLOWUP_DECAY = 0.4

# Type aliases for type checking
ProblemList = List[int]
ProblemSet = Set[int]


def mean_average_precision(
    predicted: ProblemList,
    expected: ProblemList,
    k: int,
) -> Optional[float]:
    '''Compute MAP@k - https://goo.gl/KMghXR'''
    if not predicted or not expected:
        return None
    num_problems = min(len(predicted), k)
    return cast(
        float,
        sum((predicted[:num_problems] == expected[:num_problems]) *
            (1. / np.arange(1, num_problems + 1))))


def load_sqlite(database: str) -> pd.DataFrame:
    '''Load runs from SQLite.'''
    logging.info('Reading runs from SQLite')
    dbconn = sqlite3.connect(database)
    try:
        runs = pd.read_sql_query(
            """
            SELECT
                identity_id,
                problem_id
            FROM
                Runs;
            """, dbconn)
        logging.info('Found %d runs', len(runs))
        return runs
    finally:
        dbconn.close()


def load_mysql(args: argparse.Namespace) -> pd.DataFrame:
    '''Load runs from MySQL.

    Relevant runs are AC runs that were not part of a problemset.

    Ignoring problem sets helps remove bias in recommendations
    introduced by problem ordering in contests and tests.
    '''
    dbconn = lib.db.connect_readonly(
        lib.db.DatabaseConnectionArguments.from_args_readonly(args))
    if not dbconn:
        dbconn = lib.db.connect(
            lib.db.DatabaseConnectionArguments.from_args(args))
    try:
        logging.info('Reading runs from MySQL')
        runs: pd.DataFrame = pd.read_sql_query(
            """
            SELECT
                s.identity_id,
                s.problem_id,
                MIN(s.time) `time`
            FROM
                Submissions s
            WHERE
                s.problemset_id IS NULL AND
                s.type = "normal" AND
                s.status = "ready" AND
                s.verdict = "AC"
            GROUP BY
                s.identity_id,
                s.problem_id
            ORDER BY
                s.identity_id ASC,
                s.time ASC;
            """, dbconn)

        # MySQL needs to select any columns that appear in the ORDER BY
        # section, but we want to not propagate the values of that column to
        # the rest of the script.
        runs.drop(['time'], axis=1, inplace=True)

        # There's no need to preserve the original identities, so we will
        # anonymize them to be able to (more) safely share the database
        # (although that still wouldn't make the dataset k-anonymous or robust
        # against differential attacks).
        identity_map: Dict[int, int] = {}
        for identity_id in runs['identity_id']:
            if identity_id in identity_map:
                continue
            identity_map[identity_id] = len(identity_map)
        runs.loc[:, 'identity_id'] = runs.identity_id.apply(
            lambda x: identity_map[x])

        logging.info('Found %d runs', len(runs))
        return runs
    finally:
        dbconn.conn.close()


def train_test_split(
    runs: pd.DataFrame,
    train_fraction: float = _TRAIN_FRACTION,
    random_seed: Optional[int] = None,
) -> Tuple[pd.Series, pd.Series]:
    '''Splits runs into test/train sets by user.

    The split is done based on `train_fraction`.
    '''

    # Split dataset into test/train
    users = pd.Series(runs.identity_id.unique())
    train_users = users.sample(frac=train_fraction, random_state=random_seed)
    test_users = users.drop(train_users.index)

    return train_users, test_users


def generate_model(
    train_ac: Mapping[int, Sequence[int]],
    num_followups: int = _NUM_FOLLOWUPS,
    followup_decay: float = _FOLLOWUP_DECAY
) -> Mapping[int, Sequence[Tuple[int, float]]]:
    '''Assumes runs are sorted by submission time'''
    tuples: List[Tuple[int, int, float]] = []
    for problems in train_ac.values():
        for i, source in enumerate(problems):
            for j in range(1, min(num_followups + 1, len(problems) - i)):
                tuples.append(
                    (source, problems[i + j], followup_decay**(j - 1)))

    weighted_pairs = pd.DataFrame(tuples,
                                  columns=('source', 'target', 'weight'))
    logging.info('Weighted pairs: %d', len(tuples))
    model: DefaultDict[int, List[Tuple[int,
                                       float]]] = collections.defaultdict(list)
    for (recommended_problem_id,
         solved_problem_id), score in weighted_pairs.groupby(
             ['source', 'target']).aggregate(sum).itertuples():
        model[recommended_problem_id].append((solved_problem_id, score))
    # Sort by score descending.
    for recommendations in model.values():
        recommendations.sort(key=lambda x: x[1], reverse=True)
    return model


class TrainingConfig:
    '''A class to store the configuration for training a model.'''

    def __init__(
        self,
        train_fraction: float = _TRAIN_FRACTION,
        rng_seed: int = 0,
        num_followups: int = _NUM_FOLLOWUPS,
        followup_decay: float = _FOLLOWUP_DECAY,
    ):
        self.train_fraction: float = train_fraction
        assert 0 < self.train_fraction < 1
        self.rng_seed: Optional[int] = rng_seed
        self.num_followups: int = num_followups
        self.followup_decay: float = followup_decay


class Model:
    '''A class that represents a prediction model.
    '''

    def __init__(self, config: TrainingConfig, runs: pd.DataFrame):
        self.config = config

        train_users, test_users = train_test_split(runs, config.train_fraction,
                                                   config.rng_seed)
        logging.info('Training users: %d', len(train_users))
        logging.info('Testing users: %d', len(test_users))

        # All AC runs groups by user.
        train_ac = runs[runs.identity_id.isin(train_users)]
        logging.info('Found %d first AC runs', len(train_ac))
        test_ac = runs[runs.identity_id.isin(test_users)]
        logging.info('Found %d first AC runs', len(test_ac))

        train_ac_map = {
            identity_id: list(runs['problem_id'])
            for identity_id, runs in train_ac.groupby('identity_id')
        }
        self.test_ac_map = {
            identity_id: list(runs['problem_id'])
            for identity_id, runs in test_ac.groupby('identity_id')
        }

        self.model = generate_model(train_ac_map, config.num_followups,
                                    config.followup_decay)
        logging.info('Trained')

    def save(self, output_path: str) -> None:
        '''Save a model to an Sqlite3 db in `output_path`.

        The DB contains a single table `ProblemRecommendations` with 3 columns:
        - solved_problem_id (int): The ID of the problem the user just solved.
        - recommended_problem_id (int): One recommendation.
        - score (float): A relative weight for this recommendation vs
                         others.

        The DB also has an index on `solved` for efficient lookups.
        '''
        if os.path.isfile(output_path):
            os.unlink(output_path)
        with sqlite3.connect(output_path) as conn:
            try:
                cur = conn.cursor()
                cur.executescript('''CREATE TABLE ProblemRecommendations (
                        solved_problem_id INTEGER,
                        recommended_problem_id INTEGER,
                        score REAL
                    );
                    ''')
                cur.executescript('''CREATE INDEX Recs_index
                    ON ProblemRecommendations(solved_problem_id);
                    ''')
                for solved_problem_id, recommendations in self.model.items():
                    for recommended_problem_id, score in recommendations:
                        cur.execute(
                            '''
                            INSERT INTO
                                ProblemRecommendations
                            VALUES
                                (?, ?, ?);
                            ''',
                            (solved_problem_id, recommended_problem_id, score))
            finally:
                conn.commit()

    def recommend(
        self,
        latest_problem: int,
        banned_problems: ProblemSet,
        k: int,
    ) -> Optional[ProblemList]:
        '''Recommends the a problem given that a user just solved last_problem.

        Args:
            model:
            latest_problem: The problem after which to make a recomendation.
            banned_problems: A list or problems that shouldn't be recommended,
                             for example, because the user already solved (or
                             tried) them.

        Returns:
            A list of recommended problem_id to try next of the recommended
            problem or None if no recommendation can be made.
        '''
        return [
            problem_id
            for (problem_id, _) in self.model.get(latest_problem, [])
            if problem_id not in banned_problems
        ][:k] or None

    def evaluate(self, k: Optional[int] = None) -> float:
        '''Compute a score about how good the model is.'''
        if k is None:
            k = self.config.num_followups
        score = 0.
        user_count = 0
        for problems in self.test_ac_map.values():
            num_problems = len(problems)
            if num_problems <= 1:
                continue

            user_count += 1
            cur_score = 0.
            for i in range(1, num_problems):
                recs = self.recommend(problems[i - 1], set(problems[:i - 1]),
                                      k)
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


def build_parser() -> argparse.ArgumentParser:
    '''Returns a argparse.ArgumentParser for this tool.'''
    parser = argparse.ArgumentParser(
        description='Builds problem recommendation model.')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    # Model params
    model_args = parser.add_argument_group('Model')
    model_args.add_argument('--num-followups',
                            type=int,
                            default=_NUM_FOLLOWUPS,
                            help='Number of problems to count')
    model_args.add_argument('--followup-decay',
                            type=float,
                            default=_FOLLOWUP_DECAY,
                            help='The decay factor in followup problems\'s'
                            'weight')

    # Training params
    training_args = parser.add_argument_group('Training')
    training_args.add_argument('--train-fraction',
                               type=float,
                               default=_TRAIN_FRACTION,
                               help='Fraction of data to use for training, '
                               'leaving the rest for testing.')
    training_args.add_argument('--rng-seed',
                               type=int,
                               default=None,
                               help='Seed value for the Random Number '
                               'Generator so that tests behave '
                               'deterministically.')
    training_args.add_argument('--min-map-score',
                               type=float,
                               default=0.3,
                               help='Minimum MAP score to consider the '
                               'training successful. Use to ensure we '
                               'don\'t push bad models to prod.')
    # Input/Output
    io_args = parser.add_argument_group('Input/Output')
    io_args.add_argument('--sqlite-database',
                         type=str,
                         help=('Path of the sqlite3 database to read runs '
                               'from instead of MySQL.'))
    io_args.add_argument('--save-sqlite-database',
                         type=str,
                         help='Path of the sqlite3 database to save runs to')
    io_args.add_argument('--num-rows',
                         type=int,
                         help='Number of rows to consider.')
    io_args.add_argument('--output',
                         type=str,
                         required=True,
                         help='Name of the output file to write the model to.')

    return parser


def main() -> None:
    '''Main entrypoint.'''
    parser = build_parser()
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    try:
        if args.sqlite_database:
            runs = load_sqlite(args.sqlite_database)
        else:
            runs = load_mysql(args)
        if args.save_sqlite_database:
            with sqlite3.connect(args.save_sqlite_database) as conn:
                runs.to_sql('Runs', con=conn, if_exists='replace')
        if args.num_rows is not None:
            runs = runs[:args.num_rows]

        model = Model(
            TrainingConfig(train_fraction=args.train_fraction,
                           rng_seed=args.rng_seed,
                           num_followups=args.num_followups,
                           followup_decay=args.followup_decay), runs)

        score = model.evaluate()
        logging.info('Model MAP score: %f', score)
        if score >= args.min_map_score:
            # Save current model
            model.save(args.output)
        else:
            logging.error(
                'Model NOT saved. Resulting accuracy was too low: '
                '%f below %f', score, args.min_map_score)
    except Exception:
        logging.exception('Failed to update recommendation model.')
        raise
    finally:
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
