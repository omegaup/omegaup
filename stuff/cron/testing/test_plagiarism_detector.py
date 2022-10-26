#!/usr/bin/env python3

from typing import List
import pytest
import argparse
import calendar
import collections
import datetime
import json
import logging
import string
import time
import operator
import sys
import unittest
import os
import random

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                "../"))

import lib.db
import omegaup.api
import test_constants
import cron.plagiarism_detector # type: ignore

# Constants

GUID = ["00000000000000000000000000000001", "00000000000000000000000000000002",
        "00000000000000000000000000000003", "00000000000000000000000000000004",
        "00000000000000000000000000000005", "00000000000000000000000000000006",
        "00000000000000000000000000000007", "00000000000000000000000000000008",
        "00000000000000000000000000000009"
        ]
# SQL Queries
CREATE_A_TEST_CONTEST = '''
                            INSERT INTO `Contests`
                            (`title`, `alias`, `description`, 
                            `start_time`, `finish_time`,
                             `check_plagiarism`, `problemset_id`,
                             `acl_id`
                            )
                            VALUES
                            (%s, %s, %s, %s, %s, %s, %s, %s)
                            '''

ADD_A_SUBMISSION_TO_THE_CONTEST = '''
                                INSERT INTO `Submissions`
                                (`submission_id`, `identity_id`,
                                  `problem_id`, `problemset_id`, 
                                   `guid`, `language`, `status`, 
                                   `verdict`, `type`
                                )
                                VALUES
                                (%s, %s, %s, %s, %s, %s, %s, %s,
                                %s)
                            '''

@pytest.fixture(scope='session')
def dbconn() -> lib.db.Connection:
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user='root',
            password='omegaup',
            host='mysql',
            database='omegaup',
            port= 13306,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )
    return dbconn

def test_plagiarism_detector(dbconn: lib.db.Connection) -> None:

    current_time = datetime.datetime.now()
    start_time= current_time - datetime.timedelta(minutes = 30)
    alias = ''.join(random.choices(string.digits, k=8))
    description = "For Plagiarism tests"
    problemset_id = 5 # Because it already contains 3 problems
    acl_id = 65551
    check_plagiarism = 1
    aliases = [alias]

    with dbconn.cursor() as cur:
        cur.execute(CREATE_A_TEST_CONTEST, 
            (alias, alias, description,
            start_time, current_time,
            check_plagiarism, 
            problemset_id, acl_id,))

    # add submissions to the contest
    problems: List[int] = [1, 3, 5] # Problems inside Problemset
    submission_id: int = int(69) # counter for submission_id
    guid: int = 0 #counter for GUID
    language = "cpp20-gcc"
    status = "ready"
    verdict = "AC"
    ttype = "test"

    for identity in range(3, 6):
        for problem in problems:
            with dbconn.cursor() as cur:
                cur.execute(
                        ADD_A_SUBMISSION_TO_THE_CONTEST, 
                        (submission_id, identity, problem, 
                        problemset_id, GUID[guid], language,
                        status,verdict, ttype,))
                submission_id+=1
                guid+=1

    dbconn.conn.commit()
    result = cron.plagiarism_detector.get_contests(dbconn)

    for res in result:
        assert res['alias'] in aliases
        submissions = cron.plagiarism_detector.get_submissions_for_contest(dbconn, res['contest_id'])
        for sub in submissions:
            assert sub['guid'] in GUID


    
        
        