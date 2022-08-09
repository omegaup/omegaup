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
import datetime
import json
import logging
import operator
import os
import sys
from typing import (DefaultDict, Dict, Mapping, NamedTuple, Optional, Sequence,
                    Tuple, Set)

from mysql.connector import errorcode
from copydetect import CopyDetector

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


# This Cronjob run after every 15 mins. 

# We will have a contests list of those contests that have ended in the last 20 mins of 
# starting this cronjob. 
contests = []

def get_contests():
    stuff
    # Then we will check if a plagiarism_table already exists for any of the contests in the contests list. And remove those contests. 
    # For those contests that don't have a plagiarism_table we will check if check_plagiarism is true in the database. 



# S3 code will go here to retreive the results
# We will make a folder /omegaup/stuff/cron/submissions/ and in that folder will store different folders for all the contests
# example:- for a contest alias:- Gsoc, the adress for storing files will be 
    # /omegaup/stuff/cron/submissions/Gsoc

def get_submission():
    # get submissions from S3

    for i in range(0, len(contests)):
        os.mkdir(contest[i])


# Then will we will run copydetect on each contest
def return_range(self, code_list_splitted):
    
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


def detector(self, contest_alias):
    # copydetect 
    detector = CopyDetector(test_dirs=["/omegaup/stuff/cron/submissions/"+contest_alia+"/"],extensions=["cpp"], display_t=0.0, autoopen = False, disable_filtering=True, out_file= "result.html")
    detector.run()
    detector_result = list(detector.get_copied_code_list())

    # get results. 
    s = "<span class='highlight-red'>" # where the flagging starts. 
    ss = "</span>" # where the flagging ends. 
    for i in range(len(detector_result)):
        percent_one = detector_result[i][0] # percentage match in first code
        percent_two = detector_result[i][1] # percentage match in second code
        file_one = detector_result[i][2] # address of file one. Will be needed to extract information like the GUID. 
        file_two = detector_result[i][3] # address of file two. 
        code_one = return_range(detector_result[i][4].split('\n')) # Gets the range of lines that are marked red. 
        code_two = retur_range(detector_result[i][5].split('\n')) # Gets teh range of lines that are marked green. 

        # Now push these values to the Plagiarism Table. 


def run_detector_on_each_contests():
    for i in range(0, len(contests)):
        detector(contests[i])


def main() -> None:
    '''Main entrypoint. '''
    parser = argparse.ArgumentParser(
    description='Aggregate user feedback.')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    
    get_contests()
    check_contest_okay()
    if(len(contests)!=0):
        get_submissions()
        run_detector_on_each_contests()



if __name__ == '__main__':
    main()