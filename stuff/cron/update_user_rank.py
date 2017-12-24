#!/usr/bin/python3

'''Updates the user ranking.'''

import argparse
import configparser
import getpass
import logging
import os

import MySQLdb


def update_user_rank(cur):
    '''Updates the user ranking.'''

    cur.execute('TRUNCATE TABLE `User_Rank`;')
    cur.execute('''
        UPDATE
            Problems p
        SET
            p.accepted = (
                SELECT
                    COUNT(DISTINCT r.user_id)
                FROM
                    Runs r
                WHERE
                    r.problem_id = p.problem_id AND r.verdict = 'AC'
            );
    ''')
    cur.execute('''
        SELECT
            username,
            name,
            country_id,
            state_id,
            school_id,
            up.user_id,
            COUNT(ps.problem_id) problems_solved_count,
            SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)) score
        FROM
        (
            SELECT DISTINCT
              r.user_id,
              r.problem_id
            FROM
              Runs r
            WHERE
              r.verdict = 'AC' AND r.test = 0
        ) AS up
        INNER JOIN
            Problems ps ON ps.problem_id = up.problem_id AND ps.visibility > 0
        INNER JOIN
            Users u ON u.user_id = up.user_id
        GROUP BY
            user_id
        ORDER BY
            score DESC;
    ''')
    rank = 0
    prev_score = None
    for row in cur:
        if row['score'] != prev_score:
            rank += 1
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


def mysql_connect(args):
    '''Connects to MySQL with the arguments provided.

    Returns a MySQLdb connection.'''

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
        db=args.database,
    )


def main():
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--mysql-config-file',
                        default=os.path.join(os.getenv('HOME') or '.',
                                             '.my.cnf'),
                        help='.my.cnf file that stores credentials')
    parser.add_argument('--verbose', '-v', action='store_true',
                        help='Enables verbose logging')
    parser.add_argument('--host', type=str, help='MySQL host',
                        default='localhost')
    parser.add_argument('--user', type=str, help='MySQL username')
    parser.add_argument('--password', type=str, help='MySQL password')
    parser.add_argument('--database', type=str, help='MySQL database',
                        default='omegaup')

    args = parser.parse_args()
    logging.basicConfig(level=logging.DEBUG if args.verbose else logging.INFO)

    dbconn = mysql_connect(args)
    try:
        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            update_user_rank(cur)
        dbconn.commit()
    finally:
        dbconn.close()


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
