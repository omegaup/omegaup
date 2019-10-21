#!/usr/bin/python3
# type: ignore

'''Replays a contest.'''

import argparse
import getpass
import hashlib
import sys
import time
import urllib

import MySQLdb


def main():
    '''Main entrypoint.'''
    # pylint: disable=too-many-locals,too-many-statements

    parser = argparse.ArgumentParser(description='Replay a contest')

    parser.add_argument('--user', type=str, help='MySQL username',
                        required=True)
    parser.add_argument('--database', type=str, help='MySQL database',
                        required=True)
    parser.add_argument('--password', type=str, help='MySQL password')
    parser.add_argument('contest', type=str, help='Contest alias')

    args = parser.parse_args()
    password = args.password
    if not password:
        password = getpass.getpass()

    db = MySQLdb.connect(
        host='localhost',
        user=args.user,
        passwd=password,
        db=args.database
    )
    cur = db.cursor()

    # Get contest ID
    if cur.execute('SELECT contest_id FROM Contests WHERE alias = %s',
                   args.contest) != 1:
        print("Failed to load contest %s" % args.contest, file=sys.stderr)
        sys.exit(1)

    contest_alias = args.contest
    contest_id = cur.fetchone()[0]

    # Create new contest
    start_time = int(time.time())
    new_alias = "%s_%d" % (contest_alias, start_time)
    scoreboard_token = hashlib.md5(
        '%s_scoreboard_admin' % new_alias).hexdigest()[:30]
    cur.execute(
        '''
        INSERT INTO Contests(
            title, description, start_time, finish_time, director_id,
            rerun_id, alias, feedback, penalty_type, penalty_calc_policy,
            scoreboard_url, scoreboard_url_admin
        ) VALUES (
            %s, %s, %s, %s, 1, 0, %s, "yes", "none", "sum", %s, %s
        );
        ''',
        (new_alias, "Replay of %s" % contest_alias,
         time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(start_time)),
         time.strftime('%Y-%m-%d %H:%M:%S',
                       time.gmtime(start_time + 3600 * 5)),
         new_alias, hashlib.md5(new_alias + '_scoreboard').hexdigest()[:30],
         scoreboard_token))
    new_id = cur.lastrowid

    # Add old problems
    cur.execute(
        '''
        INSERT INTO
            Contest_Problems
        SELECT
            %s AS contest_id, problem_id, points, `order`
        FROM
            Contest_Problems
        WHERE
            contest_id = %s;
        ''',
        (new_id, contest_id)
    )
    db.commit()

    # Allow user to open the contest to see the shiny display
    print('http://localhost:8080/arena/%s/scoreboard/%s?ws=on' %
          (new_alias, scoreboard_token), file=sys.stderr)
    print('Press Enter to continue...', end=' ', file=sys.stderr)
    input()

    # Replay all runs, one after the other
    num_rows = cur.execute(
        'SELECT * FROM Runs WHERE contest_id = %s;',
        (contest_id))
    idx = 0
    times = []
    relevant_users = {}
    t0_all = time.time()
    for row in cur.fetchall():
        # Add user to Contest_Users if first time sending
        if row[1] not in relevant_users:
            relevant_users[row[1]] = True
            cur.execute(
                '''
                INSERT INTO Contests_Users (user_id, contest_id)
                VALUES (%s, %s);
                ''',
                (row[1], new_id)
            )

        # Add run
        cur.execute(
            '''
            INSERT INTO Runs (
                user_id, problem_id, contest_id, guid, language, status,
                verdict, runtime, memory, score, contest_score, ip,
                submit_delay, test, judged_by, time
            ) VALUES (
                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                %s
            );
            ''',
            ((row[1], row[2], new_id,
              hashlib.md5(new_alias + row[4]).hexdigest())
             + row[5:13] + row[14:]
             + (time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime()),)))

        run_id = cur.lastrowid
        db.commit()
        idx += 1
        print('%2.3f%%\r' % (100.0 * idx / num_rows), end=' ', file=sys.stderr)

        # Force scoreboard regeneration
        t0 = time.time()
        response = urllib.request.urlopen(
            'http://localhost/api/scoreboard/refresh/',
            urllib.parse.urlencode({'token': 'secret', 'alias': new_alias,
                                    'run': str(run_id)})
        ).read()
        t1 = time.time()
        assert response == '{"status":"ok"}', response
        times.append(t1 - t0)
    t1_all = time.time()

    cur.close()
    db.close()

    # Print some stats
    print(times)
    print(t1_all - t0_all)
    print((t1_all - t0_all) / num_rows)


if __name__ == '__main__':
    main()

# vim: expandtab shiftwidth=4 tabstop=4
