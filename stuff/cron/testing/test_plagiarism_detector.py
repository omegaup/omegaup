#!/usr/bin/env python3

from typing import (List, Callable, Tuple, Iterable, Set)
import pytest
import datetime
import string
import time
import itertools
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


# global variables
contest_id: int = 0
GUID: List[str] = []
first_submission_id: int = 0


def create_contest(dbconn: lib.db.Connection) -> None:

    current_time = datetime.datetime.now()
    start_time = current_time - datetime.timedelta(minutes=30)
    finish_time = current_time - datetime.timedelta(minutes=5)
    alias: str = ''.join(random.choices(string.digits, k=8))
    description: str = "For Plagiarism tests"
    acl_id: int = 65551  # Maybe create a New ACL
    check_plagiarism: int = 1
    scoreboard_url: str = ''.join(random.choices(string.ascii_letters, k=30))
    scoreboard_url_admin: str = ''.join(
        random.choices(string.ascii_letters, k=30))
    guid: int = 1  #counter for GUID LIST
    language: str = "cpp20-gcc"
    status: str = "ready"
    verdict: str = "AC"
    ttype: str = "test"
    global GUID  # needed for assertions
    no_of_users: int = 3  # we can increase the number of user of further testing
    no_of_problems: int = 3  # same as above case
    global contest_id

    # create Problemset for contest
    with dbconn.cursor() as cur:
        cur.execute(
            '''
                    INSERT INTO `Problemsets`
                        (`acl_id`, `scoreboard_url`, 
                        `scoreboard_url_admin`)
                    VALUES
                        (%s, %s, %s);
                    ''', (
                acl_id,
                scoreboard_url,
                scoreboard_url_admin,
            ))
        problemset_id: int = cur.lastrowid
    dbconn.conn.commit()

    # add 3 problems to problemset
    for problem in range(1, no_of_problems + 1):
        version = ''.join(random.choices(string.ascii_letters, k=40))
        with dbconn.cursor() as cur:
            cur.execute(
                '''
                INSERT INTO `Problemset_Problems`
                (`problemset_id`, `problem_id`, `version`)
                VALUES
                (%s, %s, %s)
                ''', (
                    problemset_id,
                    problem,
                    version,
                ))
    dbconn.conn.commit()

    # create a contest
    with dbconn.cursor() as cur:
        cur.execute(
            '''
                    INSERT INTO `Contests`
                    (`title`, `alias`,
                        `description`, 
                    `start_time`, `finish_time`,
                        `check_plagiarism`, `problemset_id`,
                        `acl_id`
                    )
                    VALUES
                    (%s, %s, %s, %s, %s, %s, %s, %s)
                    ''', (
                alias,
                alias,
                description,
                start_time,
                finish_time,
                check_plagiarism,
                problemset_id,
                acl_id,
            ))
        global contest_id
        contest_id = cur.lastrowid
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

    # setting foreign key check = 0 because of dependencies
    with dbconn.cursor() as cur:
        cur.execute('''
                SET foreign_key_checks = 0;
                ''')

    with dbconn.cursor() as cur:
        cur.execute('''
                TRUNCATE TABLE Plagiarisms;
                ''')

    with dbconn.cursor() as cur:
        cur.execute('''
                TRUNCATE TABLE Submission_Log;
                ''')

    # setting foreign key check = 1
    with dbconn.cursor() as cur:
        cur.execute('''
                SET foreign_key_checks = 1;
                ''')

    # replacing the guid name for new tests to run.
    guid = 1
    for user in range(1, no_of_users + 1):
        for problem in range(1, no_of_problems + 1):
            gguid = f'{guid:032x}'
            with dbconn.cursor(buffered=True) as cur:
                cur.execute(
                    ''' 
                        DELETE FROM `Submissions`
                        WHERE `guid` = %s
                        ;
                        ''', (gguid, ))
            guid += 1
        dbconn.conn.commit()

    # add submissions to the contest
    guid = 1
    for identity in range(1, no_of_users + 1):
        for problem in range(1, no_of_problems + 1):
            with dbconn.cursor() as cur:
                cur.execute(
                    '''
                        INSERT INTO `Submissions`
                        (`identity_id`,
                            `problem_id`, `problemset_id`, 
                            `guid`, `language`, `status`, 
                            `verdict`, `type`
                        )
                        VALUES
                        (%s, %s, %s, %s, %s, %s, %s,
                        %s)
                            ''', (
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


def test_get_contests(dbconn: lib.db.Connection) -> None:

    create_contest(dbconn)
    get_contests_detector = cron.plagiarism_detector.get_contests(dbconn)

    contests: List[int] = [
        contest['contest_id'] for contest in get_contests_detector
    ]
    assert contest_id in contests


def test_submission_ids(dbconn: lib.db.Connection) -> None:

    global GUID
    global contest_id
    submissions = cron.plagiarism_detector.get_submissions_for_contest(
        dbconn, contest_id)

    submission_ids_for_contest: List[int] = []
    for submission in submissions:
        assert submission['guid'] in GUID
        submission_ids_for_contest.append(submission['submission_id'])

    global first_submission_id
    first_submission_id = submission_ids_for_contest[0]


def test_plagiarism_detector_result(dbconn: lib.db.Connection) -> None:

    global contest_id
    download: SubmissionDownloader = cron.plagiarism_detector.LocalSubmissionDownloader(
        LOCAL_DOWNLOADER_DIR)

    cron.plagiarism_detector.run_detector_for_contest(dbconn, download,
                                                      contest_id)

    with dbconn.cursor() as cur:
        cur.execute(
            '''
            SELECT `submission_id_1`,
                    `submission_id_2`
            FROM `Plagiarisms`
            WHERE `contest_id` =  %s; 
                    ''', (contest_id, ))
        plagiarized_matches = cur.fetchall()
        plagiarized_submission_ids = set(
            itertools.chain.from_iterable(
                (submission_id1, submission_id2)
                for (submission_id1, submission_id2) in plagiarized_matches))

    # hardcoded
    bad_submission_ids: List[int] = [
        first_submission_id + 6, first_submission_id + 7
    ]
    good_submission_ids: List[int] = []

    for submission_id in range(first_submission_id, first_submission_id + 9):
        if (submission_id not in bad_submission_ids):
            good_submission_ids.append(submission_id)

    for submission_id in good_submission_ids:
        assert submission_id in plagiarized_submission_ids
    for submission_id in bad_submission_ids:
        assert submission_id not in plagiarized_submission_ids

    # hardcoded
    good_matches: List[Tuple[int, int]] = [
        (first_submission_id, first_submission_id + 3),
        (first_submission_id + 1, first_submission_id + 4),
        (first_submission_id + 2, first_submission_id + 5),
        (first_submission_id + 2, first_submission_id + 8),
        (first_submission_id + 5, first_submission_id + 8),
    ]
    bad_matches: List[Tuple[int, int]] = []
    for submission_id1 in range(first_submission_id, first_submission_id + 9):
        for submission_id2 in range(submission_id1 + 1,
                                    first_submission_id + 9):
            if (submission_id1, submission_id2) not in good_matches:
                bad_matches.append((submission_id1, submission_id2))

    for match in good_matches:
        assert match in plagiarized_matches
    for match in bad_matches:
        assert match not in plagiarized_matches
