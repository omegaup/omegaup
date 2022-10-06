#!/usr/bin/env python3


''' Main Plagiairism Detector Script. 

This script gets all the contest that finised in the last 15 minutes and acesses those contests and gets
runs copydetect on submissions recieved. 

Finally it pushes the necessary data to the database

'''

import argparse
import boto3 # type: ignore
import calendar
import collections
import datetime
import json
import logging
import operator
import os
import sys
import shutil
from typing import (DefaultDict, Dict, Mapping, NamedTuple, Optional, Sequence,
                    Tuple, Set, Any)

from mysql.connector import errorcode
from copydetect import CopyDetector  # type: ignore
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

# Constants
# Test files list
TEST_FILES = ['test1.cpp', 'test2.cpp', 'test3.cpp', 'test4.cpp' ,
                'test5.cpp', 'test6.cpp', 'test7.cpp', 'test8.cpp', 'test9.cpp']

def run_copy_detect(dbconn: lib.db.Connection, 
    submission_ids: Sequence[Any], contest:int, problem_id: str) -> None:

    detector_result = []
    if os.path.exists("/opt/omegaup/stuff/cron/temp_directory/problem"+problem_id+"/"):
        detector = CopyDetector(test_dirs=
                                ["/opt/omegaup/stuff/cron/temp_directory/problem"+problem_id+"/"],
                                extensions=["cpp"], display_t=0.9, autoopen = False, 
                                disable_filtering=True)
        detector.run()
        detector_result = list(detector.get_copied_code_list())
    
def get_submission_files(dbconn: lib.db.Connection, submission_ids: Sequence[Any], contest: int) -> None:
    # For tests. 
    current_directory = os.getcwd()
    current_directory += "/stuff/cron"
    final_directory = os.path.join(current_directory, r'temp_directory')
    if not os.path.exists(final_directory):
        os.makedirs(final_directory)
    
    # set to have unique problem_id only
    problem_id = set()
    for i in range(0, len(submission_ids)):
        problem_id.add(submission_ids[i][3])
    
    # converting the set to list for making it iterable. 
    problem_ids = list(problem_id)

    for i in range(0, len(problem_ids)):

        if not os.path.exists(current_directory + '/temp_directory/problem' + str(problem_ids[i])+"/"):
            os.mkdir(current_directory + '/temp_directory/problem'+ str(problem_ids[i])+"/")

        for j in range(0, len(submission_ids)):
            random = j%9
            if(submission_ids[j][3] == problem_ids[i]):
                shutil.copyfile(current_directory 
                        + '/test_files/' 
                        + TEST_FILES[random], current_directory
                        + '/temp_directory/problem'
                        + str(problem_ids[i])+"/"
                        + str(submission_ids[j][5])+'.cpp')
        
        #  Now we can run Copydetect on these files. 
        run_copy_detect(dbconn, submission_ids, contest, str(problem_ids[i]))
        # delete the directory for submssions of next problem to be added.
        shutil.rmtree('/opt/omegaup/stuff/cron/temp_directory/problem'+str(problem_ids[i])+"/")
    # delete temp_directory
    shutil.rmtree(current_directory+'/temp_directory')
    
def get_submission_ids(dbconn: lib.db.Connection, contest: int) -> None:
    with dbconn.cursor() as cur:
        cur.execute(GET_CONTEST_SUBMISSION_IDS, (contest,))
        submission_id = cur.fetchall()

    submission_ids = [list(ele) for ele in submission_id]

    if(not len(submission_ids) == 0):
        get_submission_files(dbconn, submission_ids, contest)

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
        get_contests(dbconn)
    except:
        logging.exception('Failed to generate Plagiarism Table')

if __name__ == '__main__':
    main()