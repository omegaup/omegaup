#!/usr/bin/env python3

''' Main Plagiairism Detector Script. 

This script gets all the contest that finised in the last 15 minutes and acesses those contests and gets
runs copydetect on submissions recieved. 

Finally it pushes the necessary data to the database

'''

import argparse
import calendar
import collections
import datetime
import json
import logging
import operator
import os
import sys
from typing import (DefaultDict, Dict, Mapping, NamedTuple, Optional, Sequence,
                    Tuple, Set)

from mysql.connector import errorcode
sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position



# SQL Queries

CONTESTS_TO_RUN_PLAGIARISM_ON = """ SELECT c.`contest_id`, c.`alias`, c.`problemset_id`
                                    FROM `Contests` as c
                                    WHERE
                                        c.`check_plagiarism` = 1 AND
                                        c.`contest_id` NOT IN
                                            (SELECT p.`contest_id` 
                                            FROM `Plagiarisms` as p);
                                """
GET_CONTEST_SUBMISSION_IDS= """SELECT Contests.contest_id, s.submission_id, s.problemset_id,
                                s.problem_id, s.verdict, s.guid
                                FROM Submissions as s 
                                INNER JOIN Contests ON Contests.problemset_id = s.problemset_id 
                                WHERE Contests.contest_id = %s;
                            """


'''
    Function to get the submission files of a contest from S3. 
        The files are stored as follow:-
            /stuff/cron/Submissions/Contest_id/problem_id/language 
'''

def get_submission_id(dbconn: lib.db.Connection, contest: int) -> None:
    with dbconn.cursor() as cur:
        cur.execute(GET_CONTEST_SUBMISSION_IDS, (contest,))
        submission_ids = cur.fetchall()

def get_contests(dbconn: lib.db.Connection) -> None:
    with dbconn.cursor() as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON)
        contests = cur.fetchall()
        contests = [tuple(i) for i in contests]
        for i in range(0, len(contests)):
            get_submission_id(dbconn, contests[i][0])
        
def main() -> None:
    '''Main entrypoint. '''
    parser = argparse.ArgumentParser(
    description='Runs the Plagiarism Detector')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    args.verbose = True
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))

    try:
        logging.debug('Debug Start')
        get_contests(dbconn)
    except:
        logging.exception('Failed to generate Plagiarism Table')

if __name__ == '__main__':
    main()