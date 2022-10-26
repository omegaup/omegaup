#!/usr/bin/env python3
''' Main Plagiairism Detector Script. 

This script gets all the contest that finised in the last 15 minutes and acesses those contests and gets
runs copydetect on submissions recieved. 

Finally it pushes the necessary data to the database

'''

import argparse
import calendar
import collections
import datetime
import json
import logging
import operator
import os
import shutil
import sys
import tempfile
import typing
from typing import (Callable, DefaultDict, Dict, Iterable, List, Mapping,
                    NamedTuple, Optional, Sequence, Set, Tuple, TypedDict)

import boto3  # type: ignore
import copydetect  # type: ignore

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position


class Results(NamedTuple):
    contest_id: int
    score_1: int
    score_2: int
    submission_id_1: int
    submission_id_2: int
    contents: str


class CopyDetectorResult(NamedTuple):
    test_similarity: float
    reference_similarity: float
    test_filename: str
    reference_filename: str
    highlighted_test_code: str
    highlighted_reference_code: str


# SQL Queries

CONTESTS_TO_RUN_PLAGIARISM_ON = """ SELECT c.`contest_id`, c.`problemset_id`, c.`alias`
                                    FROM `Contests` as c
                                    WHERE
                                        c.`check_plagiarism` = 1 AND
                                        c.`finish_time` > NOW() - INTERVAL 20 MINUTE AND
                                        c.`finish_time` < NOW() AND
                                        c.`contest_id` NOT IN
                                            (SELECT p.`contest_id` 
                                            FROM `Plagiarisms` as p);
                                """


class Contest(TypedDict):
    contest_id: int
    problemset_id: int


GET_CONTEST_SUBMISSION_IDS = """ SELECT c.contest_id, s.submission_id, s.problemset_id,
                                s.problem_id, s.verdict, s.guid, s.language
                                FROM Submissions as s 
                                JOIN Contests c ON c.problemset_id = s.problemset_id 
                                WHERE c.contest_id = %s;
                            """


class Submission(TypedDict):
    contest_id: int
    submission_id: int
    problemset_id: int
    problem_id: int
    verdict: str
    guid: str
    language: str


INSERT_INTO_PLAGIARISMS = """  
                                INSERT INTO `Plagiarisms`
                                (`contest_id`, `score_1`, `score_2` , `submission_id_1` , `submission_id_2`, `contents`)
                                VALUES
                                (%s, %s, %s, %s, %s, %s)
                            """

# Constants
START_RED = "<span class='highlight-red'>"
START_GREEN = "<span class='highlight-green'>"
END = "</span>"

SubmissionDownloader = Callable[[str, str, str], None]


class S3SubmissionDownloader:
    ''' A SubmissionDownloader that can download files from an S3 bucket.  '''

    def __init__(self, bucket_name: str = 'omegaup-backup') -> None:
        self._bucket = boto3.client('s3').Bucket(bucket_name)

    def __call__(self, guid: str, destination_dir: str, language: str) -> None:
        self._bucket.download_file(
            f'omegaup/submissions/{guid[:2]}/{guid[2:]}',
            os.path.join(destination_dir, f'{guid}.{language}'))


class LocalSubmissionDownloader:

    def __init__(self, dir: str) -> None:
        self._dir = dir

    def __call__(self, guid: str, destination_dir: str, language: str) -> None:
        shutil.copyfile(os.path.join(self._dir, f'{guid[:2]}/{guid[2:]}'),
                        os.path.join(destination_dir, f'{guid}.{language}'))


def get_range(code: Sequence[str]) -> Sequence[int]:

    """
    Function to get the range of lines that are plagiarised. 
    The length of each list should be even. 
    [1, 4, 7, 9] So, the ranges are 1-4 and 7-9. 
    """

    code_range: List[int] = []
    for line_number, line in enumerate(code):

        # If the color is red, then it will be the same for the entire 'code'.
        # that's why we don't really make a distinction between them.

        if START_RED in line or START_GREEN in line:
            code_range.append(line_number)

        # We have to check for END separetly so that there is always a end for a start
        if END in line:
            code_range.append(line_number)

    # return at one time either the Red lines or green lines range
    return code_range


def filter_and_format_result(dbconn: lib.db.Connection, contest_id: int,
                             submissions: Iterable[Submission],
                             results: Iterable[CopyDetectorResult]) -> None:
    """
        For inserting the result in database we need submission_id, but the result
        contains guid[2:](the only thing we can have access to from detector). 
        so we make a dict to map the guid to submission_id from the submissions. 
    """

    guid_and_submission_id_dict: Dict[str, int] = {}

    for submission in submissions:
        guid_and_submission_id_dict[submission['guid']] = submission['submission_id']

    updated_result: List[Results] = []
    for result in results:
        updated_result.append(
            Results(
                contest_id=contest_id,
                score_1=int(100 * result[0]),
                score_2=int(100 * result[1]),
                submission_id_1=guid_and_submission_id_dict[os.path.splitext(
                    os.path.basename(result.test_filename))[0]],
                submission_id_2=guid_and_submission_id_dict[os.path.splitext(
                    os.path.basename(result.test_filename))[0]],
                contents=json.dumps({
                    'file1': get_range(result[4].split('\n')),
                    'file2': get_range(result[5].split('\n'))
                }),
            ))
    # add to the database.
    with dbconn.cursor() as cur:
        cur.executemany(INSERT_INTO_PLAGIARISMS, updated_result)


def run_copy_detect(dbconn: lib.db.Connection, dir: str, contest_id: int,
                    submissions: Iterable[Submission]) -> None:

    # we will run detector for each problem.
    for problem in os.listdir(dir):
        detector = copydetect.CopyDetector(
            test_dirs=[os.path.join(dir, problem)],
            extensions=["cpp", "py", "py3", "java", "c"],
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


def download_submission_files(dbconn: lib.db.Connection, dir: str,
                              download: SubmissionDownloader,
                              submission_ids: Iterable[Submission]) -> None:

    for submission in submission_ids:
        submission_path = os.path.join(dir, str(submission['problem_id']))
        os.makedirs(os.path.dirname(submission_path), exist_ok=True)
        download(submission['guid'], submission_path, submission['language'])


def get_contests(dbconn: lib.db.Connection) -> Iterable[Contest]:

    with dbconn.cursor(dictionary=True) as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON)
        return typing.cast(Iterable[Contest], cur.fetchall())


def get_submissions_for_contest(dbconn: lib.db.Connection,
                                contest_id: int) -> Iterable[Submission]:

    with dbconn.cursor(dictionary=True) as cur:
        cur.execute(GET_CONTEST_SUBMISSION_IDS, (contest_id, ))
        return typing.cast(Iterable[Submission], cur.fetchall())


def run_detector_for_contest(dbconn: lib.db.Connection,
                             download: SubmissionDownloader,
                             contest_id: int) -> None:

    with tempfile.TemporaryDirectory(prefix='plagiarism_detector') as dir:
        submissions = get_submissions_for_contest(dbconn, contest_id)
        download_submission_files(dbconn, dir, download, submissions)
        run_copy_detect(dbconn, dir, contest_id, submissions)


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

    if args.local_downloader_dir != None:
        download: SubmissionDownloader = LocalSubmissionDownloader(
            args.local_downloader_dir)
    else:
        download = S3SubmissionDownloader()

    for contest in get_contests(dbconn):
        run_detector_for_contest(dbconn, download, contest['contest_id'])

    dbconn.conn.commit()


if __name__ == '__main__':
    main()
