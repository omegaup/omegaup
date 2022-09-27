#!/usr/bin/env python3


''' Main Plagiairism Detector Script. 

This script gets all the contest that finised in the last 15 minutes and acesses those contests and gets
runs copydetect on submissions recieved. 

Finally it pushes the necessary data to the database

'''

import argparse
import boto3
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


_OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))


# SQL Queries

CONTESTS_TO_RUN_PLAGIARISM_ON = """ SELECT c.`contest_id`, c.`alias`, c.`problemset_id`
                                    FROM `Contests` as c
                                    WHERE
                                        c.`check_plagiarism` = 1 AND
                                        c.`contest_id` NOT IN
                                            (SELECT p.`contest_id` 
                                            FROM `Plagiarisms` as p);
                                """
GET_CONTEST_SUBMISSION_IDS= """ SELECT c.contest_id, s.submission_id, s.problemset_id,
                                s.problem_id, s.verdict, s.guid
                                FROM Submissions as s 
                                INNER JOIN Contests c ON c.problemset_id = s.problemset_id 
                                WHERE c.contest_id = %s;
                            """


'''
    Function to get the submission files of a contest from S3. 
        The files are stored as follow:-
            /stuff/cron/Submissions/Contest_id/problem_id/language 
'''


def get_submission_files(dbconn: lib.db.Connection) -> None:

    current_directory = os.getcwd()
    current_directory += "/stuff/cron"
    final_directory = os.path.join(current_directory, r'temp_directory')
    if not os.path.exists(final_directory):
        os.makedirs(final_directory)
        
    f = open("stuff/cron/temp_directory/test.log", "w")
    f.write(_OMEGAUP_ROOT + "/tests/controllers/submissions/" + "\n")
    f.write(os.listdir(_OMEGAUP_ROOT + "/tests/controllers/submissions/"))
    f.write("\n" + "finished")

    # session = boto3.Session(
    #     aws_access_key_id="AKIA2RD4KJNQKME5QDFO",
    #     aws_secret_access_key="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"
    #     )
    # s3 = session.resource('s3')
    
    # os.chdir(final_directory)
    # print(os.getcwd())
    # for i in range(0, len(submission_ids)):
    #     if not os.path.exists("problem" + str(submission_ids[i][3])):
    #         problem_directory = os.path.join(final_directory, "problem" + str(submission_ids[i][3]))
    #         os.makedirs(problem_directory)
    #     file_name = submission_ids[i][5] + ".cpp"
    #     os.chdir("problem" + str(submission_ids[i][3]))
    #     # s3.Bucket('omegaup-test-mohit').download_file('omegaup/submissions/omi-2021-extremos-020a0f906823c91ec17e968d2c93.cpp', 
    #     #                                                 file_name)'omegaup/submissions/omi-2021-extremos-020a0f906823c91ec17e968d2c93.cpp', 
    #     #                                                 file_name)
    #     os.chdir(final_directory)

    # os.chdir("/opt/omegaup")
def get_submission_ids(dbconn: lib.db.Connection, contest: int) -> None:
    with dbconn.cursor() as cur:
        cur.execute(GET_CONTEST_SUBMISSION_IDS, (contest,))
        submission_ids = cur.fetchall()
    get_submission_files(dbconn, submission_ids)

def get_contests(dbconn: lib.db.Connection) -> None:
    with dbconn.cursor() as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON)
        contests = cur.fetchall()
        contests = [tuple(i) for i in contests]
        for i in range(0, len(contests)):
            get_submission_ids(dbconn, contests[i][0])
        
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
        get_submission_files(dbconn)
    except:
        logging.exception('Failed to generate Plagiarism Table')

if __name__ == '__main__':
    main()