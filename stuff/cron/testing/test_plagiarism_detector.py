#!/usr/bin/env python3

'''Tests for the plagiarism detector.'''

import dataclasses
import datetime
import itertools
import os
import random
import json
import string
import sys

from typing import Dict, List, Set

import pytest

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "../"))

import lib.db
import cron.plagiarism_detector

# Constants

_OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..'))
LOCAL_DOWNLOADER_DIR = os.path.join(_OMEGAUP_ROOT, "testdata")
PLAGIARISM_THRESHOLD = 90


@dataclasses.dataclass()
class Contest:
    '''Represents a contest.'''

    contest_id: int = 0
    guids: List[str] = dataclasses.field(default_factory=list)


@pytest.fixture(scope='session')
def dbconn() -> lib.db.Connection:
    '''Fixture that creates a database connection.'''

    return lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user='root',
            password='omegaup',
            host='mysql',
            database='omegaup-test',
            port=13306,
            mysql_config_file=lib.db.default_config_file_path() or ''))


def create_contest(
    dbconn: lib.db.Connection,  # pylint: disable=redefined-outer-name
) -> Contest:
    '''Create a contest.'''

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
            (owner_user_id, ),
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
        no_of_problems: int = 3
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


def test_get_contest(
    dbconn: lib.db.Connection,  # pylint: disable=redefined-outer-name
) -> None:
    '''Tests getting a contest from the database.'''

    contest = create_contest(dbconn)

    get_contests_detector = cron.plagiarism_detector.get_contests(dbconn)

    contests: Set[int] = set(c['contest_id'] for c in get_contests_detector)

    assert contest.contest_id in contests


def test_submission_ids(
    dbconn: lib.db.Connection,  # pylint: disable=redefined-outer-name
) -> None:
    '''Tests getting the submission IDs from a contest.'''

    contest = create_contest(dbconn)
    submissions = cron.plagiarism_detector.get_submissions_for_contest(
        dbconn, contest.contest_id)
    guids = set(submission['guid'] for submission in submissions)
    assert guids == set(contest.guids)


@pytest.mark.skip(reason="Disabled temporarily because it's failing in CI.")
def test_plagiarism_detector(
    dbconn: lib.db.Connection,  # pylint: disable=redefined-outer-name
) -> None:
    '''Test running the plagiarism detector.'''

    contest = create_contest(dbconn)
    submission_ids: Dict[str, int] = {}

    submissions = cron.plagiarism_detector.get_submissions_for_contest(
        dbconn, contest.contest_id)
    for submission in submissions:
        submission_ids[submission['guid']] = submission['submission_id']

    download: cron.plagiarism_detector.SubmissionDownloader = (
        cron.plagiarism_detector.LocalSubmissionDownloader(
            LOCAL_DOWNLOADER_DIR))

    cron.plagiarism_detector.run_detector_for_contest(dbconn, download,
                                                      contest.contest_id)

    with dbconn.cursor(dictionary=True) as cur:
        cur.execute(
            '''
                SELECT
                    `submission_id_1`,
                    `submission_id_2`,
                    `score_1`,
                    `score_2`,
                    `contents`
                FROM `Plagiarisms`
                WHERE `contest_id` = %s;
            ''', (contest.contest_id, ))

        plagiarized_result = cur.fetchall()
        plagiarized_submission_ids = set(
            itertools.chain.from_iterable(
                (result['submission_id_1'], result['submission_id_2'])
                for result in plagiarized_result))

        # TODO: Convert the graph of plagiarized submissions into disjoint sets
        # and check groups of plagiarized submissions instead of only whether a
        # submission was plagiarized or not.

        assert plagiarized_submission_ids == set((
            submission_ids[f'{1:032x}'],
            submission_ids[f'{2:032x}'],
            submission_ids[f'{3:032x}'],
            submission_ids[f'{4:032x}'],
            submission_ids[f'{5:032x}'],
            submission_ids[f'{6:032x}'],
            submission_ids[f'{9:032x}'],
        ))

        # Either one of the score should be >= threshold value
        for score in plagiarized_result:
            assert score['score_1'] >= PLAGIARISM_THRESHOLD or score[
                'score_2'] >= PLAGIARISM_THRESHOLD

        # Range of Lines Test.

        # hardcoded expected ranges.
        # notice both ranges are same due to exact same files being present

        expected_pair_range = set((((0, 41), ), ((0, 33), (33, 39), (39, 76)),
                                   ((0, 33), (33, 35), (39, 46), (48, 64))))

        found_pair_ranges = set()
        for content in plagiarized_result:
            range_of_lines = tuple(
                tuple(sub) for sub in json.loads(content['contents'])['file1'])
            found_pair_ranges.add(range_of_lines)

        assert expected_pair_range == found_pair_ranges
