#!/usr/bin/env python3

from typing import (List, Callable, Tuple, Iterable, Set, Dict)
import pytest
import dataclasses
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


@dataclasses.dataclass()
class Contest:
    contest_id: int = 0
    guids: List[str] = dataclasses.field(default_factory=list)


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


def create_contest(dbconn: lib.db.Connection) -> Contest:

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
    no_of_users: int = 3  # we can increase the number of user of further testing
    no_of_problems: int = 3  # same as above case
    guids: List[str] = []
    contest = Contest()

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
        contest.contest_id = cur.lastrowid
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
        cur.execute('''SET foreign_key_checks = 0;''')
        cur.execute('''TRUNCATE TABLE Plagiarisms;''')
        cur.execute('''TRUNCATE TABLE Submission_Log;''')
        cur.execute('''TRUNCATE TABLE Submissions;''')
        cur.execute('''SET foreign_key_checks = 1;''')

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
                guids.append(f'{guid:032x}')
                guid += 1

    dbconn.conn.commit()
    contest.guids = guids
    return contest


def test_get_contest(dbconn: lib.db.Connection) -> None:

    contest = create_contest(dbconn)

    get_contests_detector = cron.plagiarism_detector.get_contests(dbconn)

    contests: List[int] = [c['contest_id'] for c in get_contests_detector]

    assert contest.contest_id in contests


def test_submission_ids(dbconn: lib.db.Connection) -> None:

    contest = create_contest(dbconn)
    submissions = cron.plagiarism_detector.get_submissions_for_contest(
        dbconn, contest.contest_id)
    for submission in submissions:
        assert submission['guid'] in contest.guids


def test_plagiarism_detector(dbconn: lib.db.Connection) -> None:

    contest = create_contest(dbconn)
    submission_ids: Dict[str, int] = {}

    submissions = cron.plagiarism_detector.get_submissions_for_contest(
        dbconn, contest.contest_id)
    for submission in submissions:
        submission_ids[submission['guid']] = submission['submission_id']

    download: SubmissionDownloader = cron.plagiarism_detector.LocalSubmissionDownloader(
        LOCAL_DOWNLOADER_DIR)

    cron.plagiarism_detector.run_detector_for_contest(dbconn, download,
                                                      contest.contest_id)

    with dbconn.cursor() as cur:
        cur.execute(
            '''
            SELECT `submission_id_1`,
                    `submission_id_2`
            FROM `Plagiarisms`
            WHERE `contest_id` =  %s; 
                    ''', (contest.contest_id, ))
        plagiarized_matches = cur.fetchall()
        plagiarized_submission_ids = set(
            itertools.chain.from_iterable(
                (submission_id1, submission_id2)
                for (submission_id1, submission_id2) in plagiarized_matches))

    # TODO: Convert the graph of plagiarized submissions into disjoint sets and
    # check groups of plagiarized submissions instead of only whether a submission
    # was plagiarized or not.

    assert plagiarized_submission_ids == set((
        submission_ids[f'{1:032x}'],
        submission_ids[f'{2:032x}'],
        submission_ids[f'{3:032x}'],
        submission_ids[f'{4:032x}'],
        submission_ids[f'{5:032x}'],
        submission_ids[f'{6:032x}'],
        submission_ids[f'{9:032x}'],
    ))
