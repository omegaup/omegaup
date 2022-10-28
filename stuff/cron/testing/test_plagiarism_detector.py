#!/usr/bin/env python3

from typing import (List, Callable, Tuple, Iterable, Set)
import pytest
import datetime
import string
import time
import sys
import unittest
import os
import typing
import random

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "../"))

import lib.db
import cron.plagiarism_detector  # type: ignore

# Constants

_OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..'))
LOCAL_DOWNLOADER_DIR = os.path.join(_OMEGAUP_ROOT, "testdata")
current_time = datetime.datetime.now()
start_time = current_time - datetime.timedelta(minutes=30)
finish_time = current_time - datetime.timedelta(minutes=5)
alias: str = ''.join(random.choices(string.digits, k=8))
description: str = "For Plagiarism tests"
problemset_id: int
acl_id: int = 65551 # Maybe create a New ACL
check_plagiarism: int = 1
scoreboard_url: str = ''.join(random.choices(string.ascii_letters, k=30))
scoreboard_url_admin: str = ''.join(random.choices(string.ascii_letters, k=30))
submission_id: int = random.randint(100, 500) # counter for submission_id
guid: int = 1  #counter for GUID LIST
language: str = "cpp20-gcc"
status: str = "ready"
verdict: str = "AC"
ttype: str = "test"
GUID: List[str] = [] # needed for assertions
no_of_users: int = 3 # we can increase the number of user of further testing
no_of_problems: int = 3 # same as above case

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
                        (`acl_id`, `scoreboard_url`, 
                        `scoreboard_url_admin`)
                    VALUES
                          (%s, %s, %s)
                    '''
ADD_PROBLEMS_TO_PROBLEMSET = '''
                                INSERT INTO `Problemset_Problems`
                                (`problemset_id`, `problem_id`, `version`)
                                VALUES
                                (%s, %s, %s)
'''
ADD_A_SUBMISSION_TO_THE_CONTEST = '''
                                INSERT INTO `Submissions`
                                (`identity_id`,
                                  `problem_id`, `problemset_id`, 
                                   `guid`, `language`, `status`, 
                                   `verdict`, `type`
                                )
                                VALUES
                                (%s, %s, %s, %s, %s, %s, %s,
                                %s)
                            '''
GET_PLAGIARISM_DETAILS = '''
                        SELECT `submission_id_1`,
                               `submission_id_2`
                        FROM `Plagiarisms`
                        WHERE `contest_id` =  %s; 
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

def create_contest(dbconn: lib.db.Connection) -> None:

    # create Problemset for contest
    with dbconn.cursor() as cur:
        cur.execute(CREATE_PROBLEMSET, (
            acl_id,
            scoreboard_url,
            scoreboard_url_admin,
        ))
    dbconn.conn.commit()

    # get the problemset_id of the recently created problemset
    with dbconn.cursor() as cur:
        cur.execute('''
                    SELECT `problemset_id`
                    FROM `Problemsets`
                    WHERE `scoreboard_url` = %s;
        ''', (scoreboard_url, ))
        problemsets_id: List[int] = typing.cast(List[int], cur.fetchall())

    # notice we will only get 1 id so using 0
    problemset_id = problemsets_id[0]

    # add 3 problems to problemset
    for problem in range(1, no_of_problems+1):
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

    # replacing the guid name for new tests to run. 
    guid = 1
    for user in range(1, no_of_users+1):
        for problem in range(1, no_of_problems+1):
            gguid: str = f'{guid:032x}'
            replace: str = f'{guid:032x}'
            replace_with: str = ''.join(random.choices(string.ascii_letters, k=32))
            with dbconn.cursor(buffered=True) as cur:
                cur.execute('''
                        UPDATE `Submissions`
                        SET `guid` = REPLACE(%s, %s, %s)
                        WHERE `guid` = %s;
                        ''', (gguid, gguid, replace_with, gguid, ))
            guid+=1
            dbconn.conn.commit()

    # add submissions to the contest
    guid = 1
    for identity in range(1, no_of_users+1):
        for problem in range(1, no_of_problems+1):
            with dbconn.cursor() as cur:
                cur.execute(ADD_A_SUBMISSION_TO_THE_CONTEST, (
                    identity,
                    problem,
                    problemset_id,
                    f'{guid:032x}',
                    language,
                    status,
                    verdict,
                    ttype,
                ))
                GUID.append(f'{guid:032x}')
                guid += 1

            dbconn.conn.commit()


def test_plagiarism_detector(dbconn: lib.db.Connection) -> None:

    create_contest(dbconn)
    get_contests_detector = cron.plagiarism_detector.get_contests(dbconn)

    # Some contest that don't meet the criterias
    with dbconn.cursor(dictionary=True) as cur:
        cur.execute('''
                    SELECT `alias`
                    FROM `Contests`
                    WHERE `check_plagiarism`= 0 OR
                    `finish_time` <= NOW() - INTERVAL 20 MINUTE OR
                    `finish_time` > NOW()
        ''')
        result_false_contest_ids = cur.fetchall()

    assert result_false_contest_ids
    assert get_contests_detector

    true_aliases: List[str] = [res['alias'] for res in get_contests_detector]
    false_aliases: List[str] = [res['alias'] for res in result_false_contest_ids]

    assert alias in true_aliases
    assert alias not in false_aliases

    # we just want to run for current contest in the test. 
    # any better idea how we can get the current contest_id apart from executing a sql query? 

    contest_id: int = get_contests_detector[len(get_contests_detector)-1]['contest_id']
    submissions = cron.plagiarism_detector.get_submissions_for_contest(
            dbconn,contest_id)
        
    submission_ids_for_contest: List[int] = []
    for submission in submissions:
        assert submission['guid'] in GUID
        submission_ids_for_contest.append(submission['submission_id'])
    
    download: SubmissionDownloader = cron.plagiarism_detector.LocalSubmissionDownloader(
            LOCAL_DOWNLOADER_DIR)

    cron.plagiarism_detector.run_detector_for_contest(
        dbconn, download, contest_id)
    
    with dbconn.cursor() as cur:
        cur.execute(GET_PLAGIARISM_DETAILS, (contest_id,))
        plag = cur.fetchall()
    
    assert plag

    start_sub_id = submission_ids_for_contest[0]
    end_sub_id = start_sub_id + no_of_problems*no_of_users
    bad_sub_id = [
                start_sub_id + 2*no_of_problems,
                start_sub_id + 2*no_of_problems + 1
    ]

    good_sub_ids: Set[Tuple[int, int]] = set() # expected submission_ids
    bad_sub_ids: Set[Tuple[int, int]] = set()

    for sub_id_1 in range(start_sub_id, end_sub_id - no_of_problems):
        for sub_id_2 in range(sub_id_1+no_of_problems, end_sub_id, no_of_problems):
            if sub_id_2 not in bad_sub_id:
                if(abs(sub_id_1 - sub_id_2)%no_of_problems == 0):
                    good_sub_ids.add((sub_id_1, sub_id_2),)
                    good_sub_ids.add((sub_id_2, sub_id_1),)
                else:
                    bad_sub_ids.add((sub_id_1, sub_id_2),)
                    bad_sub_ids.add((sub_id_2, sub_id_1),)
    
    for p in plag:
        assert p in good_sub_ids
        assert p not in  bad_sub_ids