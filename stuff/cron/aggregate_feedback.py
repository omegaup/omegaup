#!/usr/bin/python3

'''Aggregates user feedback.

This script reads all user feedback submissions, and updates average difficulty
and rating based on bayesian averages.
'''

import argparse
import collections
import configparser
import getpass
import json
import logging
import os
import warnings

import MySQLdb
import MySQLdb.constants.ER

CONFIDENCE = 5
MIN_NUM_VOTES = 5
PROBLEM_TAG_VOTE_MIN_PROPORTION = 0.25
MAX_NUM_TOPICS = 5

# Before this id the questions were different
QUALITYNOMINATION_QUESTION_CHANGE_ID = 18663


def get_global_quality_and_difficulty_average(dbconn):
    '''Gets the global quality and difficulty average based on user feedback.

    This will be used as the prior belief when updating each individual
    problem's quality and difficulty ratings.
    '''

    with dbconn.cursor() as cur:
        cur.execute("""SELECT qn.`contents`
                       FROM `QualityNominations` as qn
                       WHERE `nomination` = 'suggestion'
                        AND qn.`qualitynomination_id` > %s;""",
                    (QUALITYNOMINATION_QUESTION_CHANGE_ID,))
        quality_sum = 0
        quality_n = 0
        difficulty_sum = 0
        difficulty_n = 0

        for row in cur:
            try:
                contents = json.loads(row[0])
            except json.JSONDecodeError:  # pylint: disable=no-member
                # Travis uses Python <3.5, which does not yet have
                # json.JSONDecodeError.
                logging.exception('Failed to parse contents')
                continue

            if 'quality' in contents:
                quality_sum += contents['quality']
                quality_n += 1
            if 'difficulty' in contents:
                difficulty_sum += contents['difficulty']
                difficulty_n += 1

    global_quality_average = None
    if quality_n:
        global_quality_average = quality_sum / float(quality_n)
    global_difficulty_average = None
    if difficulty_n:
        global_difficulty_average = difficulty_sum / float(difficulty_n)
    return (global_quality_average, global_difficulty_average)


def get_problem_aggregates(dbconn, problem_id):
    '''Gets the aggregates for a particular problem.'''

    with dbconn.cursor() as cur:
        cur.execute("""SELECT qn.`contents`
                       FROM `QualityNominations` as qn
                       WHERE qn.`nomination` = 'suggestion'
                         AND qn.`qualitynomination_id` > %s
                         AND qn.`problem_id` = %s;""",
                    (QUALITYNOMINATION_QUESTION_CHANGE_ID, problem_id,))
        quality_sum = 0
        difficulty_sum = 0
        # quality_votes and difficulty_votes contain the votes for each rating
        # and the total number of votes in their last position
        quality_votes = [0, 0, 0, 0, 0, 0]
        difficulty_votes = [0, 0, 0, 0, 0, 0]
        problem_tag_votes = collections.defaultdict(int)
        problem_tag_votes_n = 0
        for row in cur:
            contents = json.loads(row[0])
            if 'quality' in contents:
                quality_sum += contents['quality']
                quality_votes[5] += 1
                quality_votes[contents['quality']] += 1
            if 'difficulty' in contents:
                difficulty_sum += contents['difficulty']
                difficulty_votes[5] += 1
                difficulty_votes[contents['difficulty']] += 1
            if 'tags' in contents and contents['tags']:
                for tag in contents['tags']:
                    problem_tag_votes[tag] += 1
                    problem_tag_votes_n += 1

    return (quality_sum, difficulty_sum, quality_votes, difficulty_votes,
            problem_tag_votes, problem_tag_votes_n)


def bayesian_average(apriori_average, value_sum, values_n):
    '''Gets the Bayesian average of an observation based on a prior value.'''

    if values_n < CONFIDENCE or apriori_average is None:
        return None
    return (CONFIDENCE * apriori_average + value_sum) / (CONFIDENCE + values_n)


def get_most_voted_tags(problem_tag_votes, problem_tag_votes_n):
    '''Gets the most voted tags for each problem.

    This returns the list of user-suggested problem tags, provided that:
    * At least a minimum amount of votes have been cast, to make it more
      robust.
    * The number of votes cast for a particular tag is at least a certain
      proportion of the most voted for tag.
    * No more than a certain number of tags have been chosen (to avoid noise).
    '''

    if problem_tag_votes_n < MIN_NUM_VOTES:
        return None
    maximum = problem_tag_votes[max(problem_tag_votes,
                                    key=problem_tag_votes.get)]
    final_tags = [tag for (tag, votes) in problem_tag_votes.items()
                  if votes >= PROBLEM_TAG_VOTE_MIN_PROPORTION * maximum]
    if len(final_tags) >= MAX_NUM_TOPICS:
        return None
    return final_tags


def replace_autogenerated_tags(dbconn, problem_id, problem_tags):
    '''Replace the autogenerated tags for problem_id with problem_tags.'''

    try:
        logging.debug('Replacing problem %d tags with %r', problem_id,
                      problem_tags)
        with dbconn.cursor() as cur:
            cur.execute("""DELETE FROM
                               `Problems_Tags`
                           WHERE
                               `problem_id` = %s AND `autogenerated` = 1;""",
                        (problem_id,))
            cur.execute("""INSERT IGNORE INTO
                               `Problems_Tags`(`problem_id`, `tag_id`,
                                               `public`, `autogenerated`)
                           SELECT
                               %%s AS `problem_id`,
                               `t`.`tag_id` AS `tag_id`,
                               1 AS `public`,
                               1 AS `autogenerated `
                           FROM
                               `Tags` AS `t`
                           WHERE
                               `t`.`name` IN (%s);""" %
                        ', '.join('%s' for _ in problem_tags),
                        (problem_id,) + tuple(problem_tags))
            for msg in cur.messages:
                if isinstance(msg, tuple) and msg[0] == cur.Warning:
                    if msg[1][1] == MySQLdb.constants.ER.DUP_ENTRY:
                        # It is somewhat expected to get duplicate entries.
                        continue
                logging.warning('Warning while updated tags in problem %d: %r',
                                problem_id, msg)
            dbconn.commit()
    except:  # pylint: disable=bare-except
        logging.exception('Failed to replace autogenerated tags')
        dbconn.rollback()


def aggregate_feedback(dbconn):
    '''Aggregates user feedback.

    This updates problem quality, difficulty, and tags for each problem that
    has user feedback.
    '''

    (global_quality_average,
     global_difficulty_average) = get_global_quality_and_difficulty_average(
         dbconn)

    with dbconn.cursor() as cur:
        cur.execute("""SELECT DISTINCT qn.`problem_id`
                       FROM `QualityNominations` as qn
                       WHERE qn.`nomination` = 'suggestion'
                         AND qn.`qualitynomination_id` > %s;""",
                    (QUALITYNOMINATION_QUESTION_CHANGE_ID,))
        for row in cur:
            problem_id = row[0]
            logging.debug('Aggregating feedback for problem %d', problem_id)

            (problem_quality_sum, problem_difficulty_sum,
             problem_quality_votes, problem_difficulty_votes,
             problem_tag_votes,
             problem_tag_votes_n) = get_problem_aggregates(dbconn, problem_id)

            problem_quality = bayesian_average(
                global_quality_average, problem_quality_sum,
                problem_quality_votes[5])
            problem_difficulty = bayesian_average(global_difficulty_average,
                                                  problem_difficulty_sum,
                                                  problem_difficulty_votes[5])
            if problem_quality is not None and problem_difficulty is not None:
                problem_quality_votes = json.dumps(problem_quality_votes[:-1])
                problem_difficulty_votes = json.dumps(
                    problem_difficulty_votes[:-1])
                logging.debug('Updating problem %d. quality=%f, difficulty=%f',
                              problem_id, problem_quality, problem_difficulty)
                cur.execute("""UPDATE
                                   `Problems` as p
                               SET
                                   p.`quality` = %s, p.`difficulty` = %s,
                                   p.`quality_histogram` = %s,
                                   p.`difficulty_histogram` = %s
                               WHERE
                                   p.`problem_id` = %s;""",
                            (problem_quality, problem_difficulty,
                             problem_quality_votes, problem_difficulty_votes,
                             problem_id))
                dbconn.commit()
            else:
                logging.debug('Not enough information for problem %d',
                              problem_id)

            # TODO(heduenas): Get threshold parameter from DB for each problem
            # independently.
            problem_tags = get_most_voted_tags(problem_tag_votes,
                                               problem_tag_votes_n)
            if problem_tags:
                replace_autogenerated_tags(dbconn, problem_id, problem_tags)


def mysql_connect(args):
    '''Connects to MySQL with the arguments provided.

    Returns a MySQLdb connection.
    '''

    host = args.host
    user = args.user
    password = args.password
    if user is None and os.path.isfile(args.mysql_config_file):
        config = configparser.ConfigParser()
        config.read(args.mysql_config_file)
        # Puppet quotes some configuration entries.
        host = config['client']['host'].strip("'")
        user = config['client']['user'].strip("'")
        password = config['client']['password'].strip("'")
    if password is None:
        password = getpass.getpass()

    assert user is not None, 'Missing --user parameter'
    assert host is not None, 'Missing --host parameter'
    assert password is not None, 'Missing --password parameter'

    return MySQLdb.connect(
        host=host,
        user=user,
        passwd=password,
        db=args.database
    )


def main():
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(
        description='Aggregate user feedback.')

    parser.add_argument('--mysql-config-file',
                        default=os.path.join(os.getenv('HOME') or '.',
                                             '.my.cnf'),
                        help='.my.cnf file that stores credentials')
    parser.add_argument('--quiet', '-q', action='store_true',
                        help='Disables logging')
    parser.add_argument('--verbose', '-v', action='store_true',
                        help='Enables verbose logging')
    parser.add_argument('--logfile', type=str, default=None,
                        help='Enables logging to a file')
    parser.add_argument('--host', type=str, help='MySQL host',
                        default='localhost')
    parser.add_argument('--user', type=str, help='MySQL username')
    parser.add_argument('--password', type=str, help='MySQL password')
    parser.add_argument('--database', type=str, help='MySQL database',
                        default='omegaup')

    args = parser.parse_args()
    logging.basicConfig(filename=args.logfile,
                        format='%%(asctime)s:%s:%%(message)s' % parser.prog,
                        level=(logging.DEBUG if args.verbose else
                               logging.INFO if not args.quiet else
                               logging.ERROR))

    logging.info('Started')
    dbconn = mysql_connect(args)
    warnings.filterwarnings('ignore', category=dbconn.Warning)
    try:
        aggregate_feedback(dbconn)
    except:  # pylint: disable=bare-except
        logging.exception('Failed to update user ranking')
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
