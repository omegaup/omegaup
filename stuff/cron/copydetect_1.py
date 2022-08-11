#!/usr/bin/env python3

''' Main Plagiairism Detector Script. 

This script gets all the contest that finised in the last 15 minutes and acesses those contests and gets
runs copydetect on submissions recieved. 

Finally it pushes the necessary data to the database

'''

import argparse
import calendar
import collections
import copydetect
import json
import logging
import operator
import os
import sys
from typing import (DefaultDict, Dict, Mapping, NamedTuple, Optional, Sequence,
                    Tuple, Set)

from mysql.connector import errorcode
from copydetect import CopyDetector
from datetime import datetime
from datetime import timedelta
sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position



# SQL Queries

CONTESTS_TO_RUN_PLAGIARISM_ON = """ SELECT c.`contest_id`
                                    FROM `Contests` as c
                                    WHERE (c.`check_plagiarism` = 1 AND c.`finish_time` BETWEEN %s AND %s) 
                                        AND c.`contest_id` NOT IN
                                            (SELECT p.`contest_id` 
                                            FROM `Plagiarisms` as p);
                                """
INSERT_RESULT_TO_PLAGIARISMS = """

                                """



FLAG_START = "<span class='highlight-red'>" # where the flagging starts for getting line numbers of matched code. 
FLAG_END = "</span>" # where the flagging ends. 
MINUTES = 20
contests = []


def get_contests(dbconn: lib.db.Connection) -> None:
    now = datetime.now()
    date_format_str = '%d/%m/%Y %H:%M:%S.%f'

    final_time = now - timedelta(minutes=MINUTES)
    final_time_str = final_time.strftime('%d/%m/%Y %H:%M:%S.%f')

    with dbconn.cursor() as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON,(final_time_str, now))
        contests = cur.fetchall()



# Get Submission files from S#

# def get_submission():


def return_range(self, code_list_splitted) -> List:
    
    '''
    returns a list of integers that are in pair. 
    example:- [0, 25, 28, 40, 42, 45]
    meaning, first 0 to 25 lines are flagged. Then lines 28 to 40 are flagged. 
    '''

    content = [] # range of lines of code.
    content_start = []
    content_end = []
    # get the range. 
    for i in range(0,len(code_list_splitted)):
        if s in code_list_splitted[i]:
            content_start.append(i)
        if ss in code_list_splitted[i]:
            content_end.append(i)
    for j in range(0, len(ans_start)):
        content.append(ans_start[j])
        content.append(ans_end[j]-1)
    return content


def detector(
    dbconn: lib.db.Connection, contest_id) -> None:
    # copydetect 
    detector = CopyDetector(test_dirs=["/omegaup/stuff/cron/submissions/"+contest_id+"/"],extensions=["cpp"], display_t=0.0, autoopen = False, disable_filtering=True, out_file= "result.html")
    detector.run()
    detector_result = list(detector.get_copied_code_list())

    # get results. 
    s = "<span class='highlight-red'>" # where the flagging starts. 
    ss = "</span>" # where the flagging ends. 
    for i in range(len(detector_result)):
        score_1 = detector_result[i][0] # percentage match in first code
        score_2 = detector_result[i][1] # percentage match in second code
        submission_id_1 = detector_result[i][2] # address of file one. Will be needed to extract information like the GUID. 
        submission_id_2 = detector_result[i][3] # address of file two. 
        code_one = return_range(detector_result[i][4].split('\n')) # Gets the range of lines that are marked red. 
        code_two = retur_range(detector_result[i][5].split('\n')) # Gets teh range of lines that are marked green. 

        # Now push these values to the Plagiarism Table. 
        with dbconn.cursor() as cur:
            cur.execute(INSERT_RESULT_TO_PLAGIARISMS,(contest_id, percent_one,percent_two,submission_id_1, submission_id_2,  now))
            contests = cur.fetchall()


def run_detector_on_each_contests():
    for i in range(0, len(contests)):
        detector(dbconn,contests[i])


def main() -> None:
    '''Main entrypoint. '''
    parser = argparse.ArgumentParser(
    description='Runs the Plagiarism Detector')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))

    try:
        get_contests(dbconn)
        if(len(contests)!=0):
            get_submissions()
            run_detector_on_each_contests()
    except:
        logging.exception('Failed to generate Plagiarism Table')

if __name__ == '__main__':
    main()
