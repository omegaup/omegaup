#!/usr/bin/env python3

import dataclasses
import datetime
import itertools
import os
import random
import string
import sys

from typing import Callable, Dict, List

import pytest

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
    return lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user='root',
            password='omegaup',
            host='mysql',
            database='omegaup-test',
            port=13306,
            mysql_config_file=lib.db.default_config_file_path() or ''))


def create_contest(dbconn: lib.db.Connection) -> Contest:
    contest = Contest()

    with dbconn.cursor() as cur:
        # setting foreign key check = 0 because of dependencies
        cur.execute('SET foreign_key_checks = 0;')
        cur.execute('TRUNCATE TABLE Plagiarisms;')
        cur.execute('TRUNCATE TABLE Submission_Log;')
        cur.execute('TRUNCATE TABLE Submissions;')
        cur.execute('SET foreign_key_checks = 1;')

        owner_username = ''.join(random.choices(string.ascii_letters, k=20))
        cur.execute(
            '''
                INSERT INTO `Identities`
                    (`username`)
                VALUES
                    (%s);
            ''',
            (owner_username, ),
        )
        owner_identity_id: int = cur.lastrowid

        cur.execute(
            '''
                INSERT INTO `Users`
                    (`main_identity_id`)
                VALUES
                    (%s);
            ''',
            (owner_identity_id, ),
        )
        owner_user_id: int = cur.lastrowid

        cur.execute(
            '''
                INSERT INTO `ACLs`
                    (`owner_id`)
                VALUES
                    (%s);
            ''',
            (
                owner_user_id,
            ),
        )
        acl_id: int = cur.lastrowid

        scoreboard_url: str = ''.join(
            random.choices(string.ascii_letters, k=30))
        scoreboard_url_admin: str = ''.join(
            random.choices(string.ascii_letters, k=30))
        cur.execute(
            '''
                INSERT INTO `Problemsets`
                    (`acl_id`, `scoreboard_url`,
                    `scoreboard_url_admin`)
                VALUES
                    (%s, %s, %s);
            ''',
            (
                acl_id,
                scoreboard_url,
                scoreboard_url_admin,
            ),
        )
        problemset_id: int = cur.lastrowid

        # create a contest
        current_time = datetime.datetime.now()
        start_time = current_time - datetime.timedelta(minutes=30)
        finish_time = current_time - datetime.timedelta(minutes=5)
        alias: str = ''.join(random.choices(string.digits, k=8))
        cur.execute(
            '''
                INSERT INTO `Contests`
                (
                    `title`, `alias`,
                    `description`,
                    `start_time`, `finish_time`,
                    `check_plagiarism`, `problemset_id`,
                    `acl_id`
                )
                VALUES
                (%s, %s, %s, %s, %s, %s, %s, %s);
            ''',
            (
                alias,
                alias,
                "For Plagiarism tests",
                start_time,
                finish_time,
                1,
                problemset_id,
                acl_id,
            ),
        )
        contest.contest_id = cur.lastrowid

        # add problemset to contest
        cur.execute(
            '''
                UPDATE `Problemsets`
                SET `contest_id` = %s
                WHERE problemset_id = %s;
            ''', (
                contest.contest_id,
                problemset_id,
            ))

        problem_ids: List[int] = []
        no_of_problems: int = 3  # same as above case
        for _ in range(no_of_problems):
            problem_commit = ''.join(random.choices('0123456789abcdef', k=40))
            problem_version = ''.join(random.choices('0123456789abcdef', k=40))
            problem_alias = ''.join(random.choices(string.ascii_letters, k=20))
            cur.execute(
                '''
                    INSERT INTO `Problems`
                    (`acl_id`, `title`, `alias`, `commit`, `current_version`)
                    VALUES
                    (%s, %s, %s, %s, %s)
                ''', (
                    acl_id,
                    problem_alias,
                    problem_alias,
                    problem_commit,
                    problem_version,
                ))
            problem_id = cur.lastrowid
            cur.execute(
                '''
                    INSERT INTO `Problemset_Problems`
                    (`problemset_id`, `problem_id`, `version`)
                    VALUES
                    (%s, %s, %s)
                ''', (
                    problemset_id,
                    problem_id,
                    problem_version,
                ))
            problem_ids.append(problem_id)

        # add submissions to the contest
        guid = 1
        no_of_users: int = 3
        guids: List[str] = []
        for _ in range(no_of_users):
            username = ''.join(random.choices(string.ascii_letters, k=20))
            cur.execute(
                '''
                    INSERT INTO `Identities`
                        (`username`)
                    VALUES
                        (%s);
                ''',
                (username, ),
            )
            identity_id: int = cur.lastrowid

            for problem_id in problem_ids:
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
                        %s);
                    ''', (
                        identity_id,
                        problem_id,
                        problemset_id,
                        f'{guid:032x}',
                        "cpp20-gcc",
                        "ready",
                        "AC",
                        "test",
                    ))
                guids.append(f'{guid:032x}')
                guid += 1
        contest.guids = guids
    dbconn.conn.commit()

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
