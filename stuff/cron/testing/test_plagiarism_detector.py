#!/usr/bin/env python3

from typing import List, Callable
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
import cron.plagiarism_detector  # type: ignore

# Constants

GUID = [
    "00000000000000000000000000000001", "00000000000000000000000000000002",
    "00000000000000000000000000000003", "00000000000000000000000000000004",
    "00000000000000000000000000000005", "00000000000000000000000000000006",
    "00000000000000000000000000000007", "00000000000000000000000000000008",
    "00000000000000000000000000000009"
]
LOCAL_DOWNLOADER_DIR = "/opt/omegaup/stuff/cron/testing/testdata/"
# SQL Queries
CREATE_A_TEST_CONTEST = '''
                            INSERT INTO `Contests`
                            (`title`, `alias`,
                             `description`, 
                            `start_time`, `finish_time`,
                             `check_plagiarism`, `problemset_id`,
                             `acl_id`
                            )
                            VALUES
                            (%s, %s, %s, %s, %s, %s, %s, %s)
                            '''
CREATE_PROBLEMSET = '''
                    INSERT INTO `Problemsets`
                        (`problemset_id`,
                        `acl_id`, `scoreboard_url`, 
                        `scoreboard_url_admin`)
                    VALUES
                          (%s, %s, %s, %s)
                    '''
ADD_PROBLEMS_TO_PROBLEMSET = '''
                                INSERT INTO `Problemset_Problems`
                                (`problemset_id`, `problem_id`, `version`)
                                VALUES
                                (%s, %s, %s)
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

SubmissionDownloader = Callable[[str, str, str], None]


@pytest.fixture(scope='session')
def dbconn() -> lib.db.Connection:
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user='root',
            password='omegaup',
            host='mysql',
            database='omegaup',
            port=13306,
            mysql_config_file=lib.db.default_config_file_path() or ''))
    return dbconn


def test_plagiarism_detector(dbconn: lib.db.Connection) -> None:

    current_time = datetime.datetime.now()
    start_time = current_time - datetime.timedelta(minutes=30)
    finish_time = current_time - datetime.timedelta(minutes=5)
    alias = ''.join(random.choices(string.digits, k=8))
    description = "For Plagiarism tests"
    problemset_id = random.randint(90, 199)
    acl_id = 65551
    check_plagiarism = 1
    scoreboard_url = ''.join(random.choices(string.ascii_letters, k=30))
    scoreboard_url_admin = ''.join(random.choices(string.ascii_letters, k=30))
    submission_id: int = int(69)  # counter for submission_id
    guid: int = 0  #counter for GUID
    language = "cpp20-gcc"
    status = "ready"
    verdict = "AC"
    ttype = "test"

    # create Problemset for contest
    with dbconn.cursor() as cur:
        cur.execute(CREATE_PROBLEMSET, (
            problemset_id,
            acl_id,
            scoreboard_url,
            scoreboard_url_admin,
        ))
    dbconn.conn.commit()

    # add 3 problems to problemset
    for problem in range(1, 4):
        version = ''.join(random.choices(string.ascii_letters, k=40))
        with dbconn.cursor() as cur:
            cur.execute(ADD_PROBLEMS_TO_PROBLEMSET,
                        (problemset_id, problem, version))
        dbconn.conn.commit()

    # create a contest
    with dbconn.cursor() as cur:
        cur.execute(CREATE_A_TEST_CONTEST, (
            alias,
            alias,
            description,
            start_time,
            finish_time,
            check_plagiarism,
            problemset_id,
            acl_id,
        ))
    dbconn.conn.commit()

    # add problemset to contest
    with dbconn.cursor() as cur:
        cur.execute(
            '''
                        UPDATE `Problemsets`
                        SET `contest_id` = (
                            SELECT `contest_id` FROM
                            `Contests` 
                            WHERE `alias` = %s
                        )
                        WHERE problemset_id = %s;
                    ''', (
                alias,
                problemset_id,
            ))

    dbconn.conn.commit()

    # add submissions to the contest
    for identity in range(3, 6):
        for problem in range(1, 4):
            with dbconn.cursor() as cur:
                cur.execute(ADD_A_SUBMISSION_TO_THE_CONTEST, (
                    submission_id,
                    identity,
                    problem,
                    problemset_id,
                    GUID[guid],
                    language,
                    status,
                    verdict,
                    ttype,
                ))
                submission_id += 1
                guid += 1
            dbconn.conn.commit()

    dbconn.conn.commit()
    result = cron.plagiarism_detector.get_contests(dbconn)
    print(result)
    for res in result:
        assert alias == res['alias']
        submissions = cron.plagiarism_detector.get_submissions_for_contest(
            dbconn, res['contest_id'])
        print(submissions)
        for sub in submissions:
            assert sub['guid'] in GUID

        download: SubmissionDownloader = cron.plagiarism_detector.LocalSubmissionDownloader(
            LOCAL_DOWNLOADER_DIR)

        cron.plagiarism_detector.run_detector_for_contest(
            dbconn, download, res['contest_id'])
