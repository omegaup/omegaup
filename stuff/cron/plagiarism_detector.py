#!/usr/bin/env python3
'''Main Plagiairism Detector Script.

This script gets all the contest that finished in the last 15 minutes, runs
copydetect on their submissions, and inserts the reports into the database.
'''

import argparse
import json
import logging
import os
import shutil
import sys
import tempfile
import typing
from typing import (Callable, Dict, Iterable, List, NamedTuple, Sequence,
                    Tuple, TypedDict)

import boto3  # type: ignore
import copydetect  # type: ignore

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


class Results(NamedTuple):
    """Represents the results of analyzing plagiarism for two submissions."""
    contest_id: int
    score_1: int
    score_2: int
    submission_id_1: int
    submission_id_2: int
    contents: str


class CopyDetectorResult(NamedTuple):
    """Represents the results of running copydetect on two files."""
    test_similarity: float
    reference_similarity: float
    test_filename: str
    reference_filename: str
    highlighted_test_code: str
    highlighted_reference_code: str


# SQL Queries

CONTESTS_TO_RUN_PLAGIARISM_ON = """
    SELECT c.`contest_id`, c.`problemset_id`, c.`alias`
    FROM `Contests` as c
    WHERE
        c.`check_plagiarism` = 1 AND
        c.`finish_time` > NOW() - INTERVAL 120 MINUTE AND
        c.`finish_time` < NOW() AND
        c.`contest_id` NOT IN
            (SELECT p.`contest_id`
            FROM `Plagiarisms` as p);
"""


class Contest(TypedDict):
    """Represents a contest fom the database."""
    contest_id: int
    problemset_id: int


GET_CONTEST_SUBMISSION_IDS = """
    SELECT
        c.contest_id, s.identity_id,
        s.submission_id, s.problemset_id,
        s.problem_id, s.verdict, s.guid, s.language
    FROM Submissions as s
    INNER JOIN Contests c ON c.problemset_id = s.problemset_id
    WHERE c.contest_id = %s AND s.verdict = "AC";
"""


class Submission(TypedDict):
    """Represents a submission fom the database."""
    contest_id: int
    submission_id: int
    problemset_id: int
    problem_id: int
    verdict: str
    guid: str
    language: str


INSERT_INTO_PLAGIARISMS = """
    INSERT INTO `Plagiarisms`
        (
            `contest_id`,
            `score_1`,
            `score_2`,
            `submission_id_1`,
            `submission_id_2`,
            `contents`
        )
    VALUES
        (%s, %s, %s, %s, %s, %s)
"""


UPDATE_PLAGIARISM_FLAG = """
    UPDATE `Contests`
    SET `check_plagiarism` = 1
    WHERE `alias` = %s
"""

# Constants
START_RED = "<span class='highlight-red'>"
START_GREEN = "<span class='highlight-green'>"
END = "</span>"
C_LANGS = [
    'c', 'c11-gcc', 'c11-clang', 'cpp', 'cpp11', 'cpp11-gcc', 'cpp11-clang',
    'cpp17-gcc', 'cpp17-clang', 'cpp20-gcc', 'cpp20-clang'
]

SubmissionDownloader = Callable[[str, str], None]


class S3SubmissionDownloader:
    """A SubmissionDownloader that can download files from an S3 bucket."""

    def __init__(self, bucket_name: str = 'omegaup-submissions') -> None:
        self._bucket = boto3.client('s3').Bucket(bucket_name)

    def __call__(self, guid: str, destination: str) -> None:
        self._bucket.download_file(guid, os.path.join(destination))


class LocalSubmissionDownloader:
    """A SubmissionDownloader that gets files from a local directory."""

    def __init__(self, dirname: str) -> None:
        self._dir = dirname

    def __call__(self, guid: str, destination: str) -> None:
        shutil.copyfile(os.path.join(self._dir, f'{guid[:2]}/{guid[2:]}'),
                        os.path.join(destination))


def get_range(code: Sequence[str]) -> Tuple[Tuple[int, int], ...]:
    """
    Function to get the range of lines that are plagiarised.
    The length of each list should be even.
    [1, 4, 7, 9] So, the ranges are 1-4 and 7-9.
    """

    code_range_list: List[int] = []
    for line_number, line in enumerate(code):

        # If the color is red, then it will be the same for the entire 'code'.
        # that's why we don't really make a distinction between them.

        if START_RED in line or START_GREEN in line:
            code_range_list.append(line_number)

        # TODO: replace this with
        # code_range_list.append((last_start_line, line_number))
        if END in line:
            code_range_list.append(line_number)

    # return at one time either the Red lines or green lines range
    match_pair_of_lines: Tuple[Tuple[int, int], ...] = ()
    for i in range(0, len(code_range_list), 2):
        match_pair_of_lines += ((code_range_list[i], code_range_list[i + 1]), )
    return match_pair_of_lines


def filter_and_format_result(dbconn: lib.db.Connection, contest_id: int,
                             submissions: Iterable[Submission],
                             results: Iterable[CopyDetectorResult]) -> None:
    """Given a list of submissions and results, format them."""

    # For inserting the result in database we need submission_id, but the
    # result contains guid (the only thing we can have access to from
    # detector).  so we make a dict to map the guid to submission_id from the
    # submissions.

    guid_and_submission_id_dict: Dict[str, int] = {}

    for submission in submissions:
        guid_and_submission_id_dict[
            submission['guid']] = submission['submission_id']

    updated_result: List[Results] = []
    for result in results:
        submission_id_1 = guid_and_submission_id_dict[os.path.splitext(
            os.path.basename(result.test_filename))[0]]
        submission_id_2 = guid_and_submission_id_dict[os.path.splitext(
            os.path.basename(result.reference_filename))[0]]
        score_1 = result.test_similarity
        score_2 = result.reference_similarity
        code_1 = result.highlighted_test_code
        code_2 = result.highlighted_reference_code
        if submission_id_1 > submission_id_2:
            submission_id_1, submission_id_2 = submission_id_2, submission_id_1
            score_1, score_2 = score_2, score_1
            code_1, code_2 = code_2, code_1

        updated_result.append(
            Results(
                contest_id=contest_id,
                score_1=int(100 * score_1),
                score_2=int(100 * score_2),
                submission_id_1=submission_id_1,
                submission_id_2=submission_id_2,
                contents=json.dumps({
                    'file1': get_range(code_1.split('\n')),
                    'file2': get_range(code_2.split('\n'))
                }),
            ))
    # add to the database.
    with dbconn.cursor() as cur:
        cur.executemany(INSERT_INTO_PLAGIARISMS, updated_result)
    dbconn.conn.commit()


def run_copy_detect(dbconn: lib.db.Connection, dirname: str, contest_id: int,
                    submissions: Iterable[Submission]) -> None:
    """Run copydetect over a list of submissions."""

    # we will run detector for each problem.
    for problem in os.listdir(dirname):
        detector = copydetect.CopyDetector(
            test_dirs=[os.path.join(dirname, problem)],
            extensions=["cpp", "py", "py3", "java"],
            display_t=0.9,
            autoopen=False,
            disable_filtering=True)
        detector.run()
        copydetector_result: List[CopyDetectorResult] = [
            CopyDetectorResult(
                test_similarity=test_sim,
                reference_similarity=ref_sim,
                test_filename=test_f,
                reference_filename=ref_f,
                highlighted_test_code=hl_code_1,
                highlighted_reference_code=hl_code_2,
            ) for test_sim, ref_sim, test_f, ref_f, hl_code_1, hl_code_2,
            overlap in detector.get_copied_code_list()
        ]

        filter_and_format_result(dbconn, contest_id, submissions,
                                 copydetector_result)


def download_submission_files(dirname: str,
                              download: SubmissionDownloader,
                              submission_ids: Iterable[Submission]) -> None:
    """Given a list of submissions, download them into a directory."""

    for submission in submission_ids:
        lang = submission['language']
        if lang in C_LANGS:
            lang = "cpp"

        submission_path = os.path.join(dirname, str(submission['problem_id']),
                                       f'{submission["guid"]}.{lang}')
        os.makedirs(os.path.dirname(submission_path), exist_ok=True)
        download(submission['guid'], submission_path)


def turn_on_plagiarism_flag(dbconn: lib.db.Connection, alias: str) -> None:
    """For OFMI contests, turn on the flag of check_plagiarism"""
    with dbconn.cursor() as cur:
        cur.execute(UPDATE_PLAGIARISM_FLAG, alias)
        dbconn.conn.commit()


def get_contests(dbconn: lib.db.Connection) -> Iterable[Contest]:
    """Get all contests that need to be analyzed."""

    with dbconn.cursor(dictionary=True) as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON)
        return typing.cast(Iterable[Contest], cur.fetchall())


def get_submissions_for_contest(dbconn: lib.db.Connection,
                                contest_id: int) -> Iterable[Submission]:
    """Given a list of contests, get all submissions."""

    with dbconn.cursor(dictionary=True) as cur:
        cur.execute(GET_CONTEST_SUBMISSION_IDS, (contest_id, ))
        return typing.cast(Iterable[Submission], cur.fetchall())


def run_detector_for_contest(dbconn: lib.db.Connection,
                             download: SubmissionDownloader,
                             contest_id: int) -> None:
    """Run copydetect for a contest."""

    with tempfile.TemporaryDirectory(prefix='plagiarism_detector') as tempdir:
        submissions = get_submissions_for_contest(dbconn, contest_id)
        download_submission_files(tempdir, download, submissions)
        run_copy_detect(dbconn, tempdir, contest_id, submissions)


def main() -> None:
    ''' Main entrypoint. '''
    parser = argparse.ArgumentParser(
        description='Runs the Plagiarism Detector')
    parser.add_argument('--local-downloader-dir')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    args.verbose = True
    lib.logs.init(parser.prog, args)

    logging.info('started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))

    if args.local_downloader_dir is not None:
        download: SubmissionDownloader = LocalSubmissionDownloader(
            args.local_downloader_dir)
    else:
        download = S3SubmissionDownloader()

    # TODO: Remove this line once the contests have been checked
    turn_on_plagiarism_flag(dbconn, '3AOFMI')
    turn_on_plagiarism_flag(dbconn, '3aOFMIDIA2')

    for contest in get_contests(dbconn):
        run_detector_for_contest(dbconn, download, contest['contest_id'])

    dbconn.conn.commit()


if __name__ == '__main__':
    main()
