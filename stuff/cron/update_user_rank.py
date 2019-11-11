#!/usr/bin/python3

'''Updates the user ranking.'''

import argparse
import logging
import os
import sys
from typing import Sequence, NamedTuple

import MySQLdb
import MySQLdb.cursors

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


class Cutoff(NamedTuple):
    '''Cutoff percentile for user ranking.'''
    percentile: float
    classname: str


def update_user_rank(cur: MySQLdb.cursors.BaseCursor) -> Sequence[float]:
    '''Updates the user ranking.'''

    cur.execute('DELETE FROM `User_Rank`;')
    logging.info('Updating accepted stats for problems...')
    cur.execute('''
        UPDATE
            Problems p
        SET
            p.accepted = (
                SELECT
                    COUNT(DISTINCT s.identity_id)
                FROM
                    Submissions s
                INNER JOIN
                    Runs r
                ON
                    r.run_id = s.current_run_id
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = s.identity_id
                INNER JOIN
                    Users u
                ON
                    u.user_id = i.user_id
                WHERE
                    s.problem_id = p.problem_id AND r.verdict = 'AC'
                    AND NOT EXISTS (
                        SELECT
                            pf.problem_id, pf.user_id
                        FROM
                            Problems_Forfeited pf
                        WHERE
                            pf.problem_id = p.problem_id AND
                            pf.user_id = u.user_id
                    )
                    AND NOT EXISTS (
                        SELECT
                            a.acl_id
                        FROM
                            ACLs a
                        WHERE
                            a.acl_id = p.acl_id AND
                            a.owner_id = u.user_id
                    )
            );
    ''')
    logging.info('Updating user rank...')
    cur.execute('''
        SELECT
            i.username,
            i.name,
            i.country_id,
            i.state_id,
            i.school_id,
            up.identity_id,
            i.user_id,
            COUNT(p.problem_id) problems_solved_count,
            SUM(ROUND(100 / LOG(2, p.accepted+1) , 0)) score
        FROM
        (
            SELECT DISTINCT
                s.identity_id,
                s.problem_id
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                r.verdict = 'AC' AND s.type = 'normal'
        ) AS up
        INNER JOIN
            Problems p ON p.problem_id = up.problem_id AND p.visibility > 0
        INNER JOIN
            Identities i ON i.identity_id = up.identity_id
        INNER JOIN
            Users u ON u.user_id = i.user_id
        WHERE
            u.is_private = 0
            AND NOT EXISTS (
                SELECT
                    pf.problem_id, pf.user_id
                FROM
                    Problems_Forfeited pf
                WHERE
                    pf.problem_id = p.problem_id AND pf.user_id = u.user_id
            )
            AND NOT EXISTS (
                SELECT
                    a.acl_id
                FROM
                    ACLs a
                WHERE
                    a.acl_id = p.acl_id AND
                    a.owner_id = u.user_id
            )
        GROUP BY
            identity_id
        ORDER BY
            score DESC;
    ''')
    rank = 0
    prev_score = None
    # MySQL has no good way of obtaining percentiles, so we'll store the sorted
    # list of scores in order to calculate the cutoff scores later.
    scores = []
    for row in cur:
        if row['score'] != prev_score:
            rank += 1
        scores.append(row['score'])
        prev_score = row['score']
        cur.execute('''
                    INSERT INTO
                        User_Rank (user_id, rank, problems_solved_count, score,
                                   username, name, country_id, state_id,
                                   school_id)
                    VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s);''',
                    (row['user_id'], rank, row['problems_solved_count'],
                     row['score'], row['username'], row['name'],
                     row['country_id'], row['state_id'], row['school_id']))
    return scores


def update_user_rank_cutoffs(cur: MySQLdb.cursors.BaseCursor,
                             scores: Sequence[float]) -> None:
    '''Updates the user ranking cutoff table.'''

    cur.execute('DELETE FROM `User_Rank_Cutoffs`;')
    logging.info('Updating ranking cutoffs...')
    cutoffs = [
        Cutoff(.01, 'user-rank-international-master'),
        Cutoff(.09, 'user-rank-master'),
        Cutoff(.15, 'user-rank-expert'),
        Cutoff(.35, 'user-rank-specialist'),
        Cutoff(.40, 'user-rank-beginner'),
    ]
    if not scores:
        return
    for cutoff in cutoffs:
        # Scores are already in descending order. That will also bias the
        # cutoffs towards higher scores.
        cur.execute('''
                    INSERT INTO
                        User_Rank_Cutoffs (score, percentile, classname)
                    VALUES(%s, %s, %s);''',
                    (scores[int(len(scores) * cutoff.percentile)],
                     cutoff.percentile, cutoff.classname))


def main() -> None:
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            scores = update_user_rank(cur)
            update_user_rank_cutoffs(cur, scores)
        dbconn.commit()
    except:  # noqa: bare-except
        logging.exception('Failed to update user ranking')
    finally:
        dbconn.close()
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
