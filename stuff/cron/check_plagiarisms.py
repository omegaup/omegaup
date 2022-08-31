#!/usr/bin/env python3

''' Main Plagiairism Detector Script. 

This script gets all the contest that finised in the last 15 minutes and acesses those contests and gets
runs copydetect on submissions recieved. 

Finally it pushes the necessary data to the database

'''

import argparse
import calendar
import collections
from copydetect import CopyDetector
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

CONTESTS_TO_RUN_PLAGIARISM_ON = """ SELECT c.`contest_id`, c.`alias`
                                    FROM `Contests` as c
                                    WHERE
                                        c.`check_plagiarism` = 1 AND
                                        c.`contest_id` NOT IN
                                            (SELECT p.`contest_id` 
                                            FROM `Plagiarisms` as p);
                                """

# Contants
MINUTES = 20
START_RED = "<span class='highlight-red'>" # where the flagging starts for red. 
START_GREEN = "<span class='highlight-green'" # where the flagging starts for green. 
END = "</span>" # where the flagging ends.



'''
    Function to get the submission files of a contest from S3. 
        The files are stored as follow:-
            /stuff/cron/Submissions/Contest_id/problem_id/language 
'''



def return_range(code_list_splitted):
    
    '''
    returns a list of integers that are in pair. 
    example:- [0, 25, 28, 40, 42, 45]
    meaning, first 0 to 25 lines are flagged. Then lines 28 to 40 are flagged. 
    '''

    content = [] # range of lines of code.
    # get the range. 
    for i in range(0,len(code_list_splitted)):
        if START_RED in code_list_splitted[i]:
            content.append(i)
        if START_GREEN in code_list_splitted[i]:
            content.append(i)
        if END in code_list_splitted[i]:
            content.append(i)

    return content


def run_copydetect(dbconn: lib.db.Connection, contest)->None:
    detector = CopyDetector(test_dirs=["/opt/omegaup/stuff/cron/submission/" + contest[1] + "/" + "extremos"+"/"],extensions=["cpp"], display_t=0.9, autoopen = False, disable_filtering=True)
    detector.run()
    detector_result = list(detector.get_copied_code_list())
    result = []
    for i in range(len(detector_result)):
        if(detector_result[i][0] > 0.90000000000 and detector_result[i][1] > 0.900000000000):
            temp = []
            temp.append(detector_result[i][0]) # percentage match in first code
            temp.append(detector_result[i][1]) # percentage match in second code
            temp.append(detector_result[i][2]) # address of file one. Will be needed to extract information like the GUID. 
            temp.append(detector_result[i][3]) # address of file two. 
            temp.append(return_range(detector_result[i][4].split('\n'))) # Gets the range of lines that are marked red. 
            temp.append(return_range(detector_result[i][5].split('\n'))) # Gets the range of lines that are marked green. 
            result.append(temp)
    
    # Add the result in a new Plagiarism Table

    for i in range(0,len(result)):
        submission_id_1  = (result[i][0]);
        with dbconn.cursor() as cur:
            cur.execute("""
                            INSERT INTO `Plagiarisms`
                            (`plagiarism_id`, `contest_id`, submission_id_1, submission_id_2, score_1, score_2, contents)
                            VALUES
                            (1, %s, 90, 91, 0, 1, "hey");
                        """,(contest))

def get_contests(dbconn: lib.db.Connection) -> None:
    with dbconn.cursor() as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON)
        contests = cur.fetchall()
        contests = [list(i) for i in contests]
        for i in range(0, len(contests)):
            run_copydetect(dbconn, contests[i])
        
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