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
from typing import (Any, DefaultDict, Dict, Mapping, NamedTuple, Optional,
                    Sequence, Set, Tuple, Callable, Iterable)

import boto3  # type: ignore
sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position
from copydetect import CopyDetector  # type: ignore
from mysql.connector import errorcode

# SQL Queries

CONTESTS_TO_RUN_PLAGIARISM_ON = """ SELECT c.`contest_id`, c.`alias`, c.`problemset_id`
                                    FROM `Contests` as c
                                    WHERE
                                        c.`check_plagiarism` = 1 AND
                                        c.`contest_id` NOT IN
                                            (SELECT p.`contest_id` 
                                            FROM `Plagiarisms` as p);
                                """
GET_CONTEST_SUBMISSION_IDS= """ SELECT c.contest_id, s.submission_id, s.problemset_id,
                                s.problem_id, s.verdict, s.guid, s.language
                                FROM Submissions as s 
                                INNER JOIN Contests c ON c.problemset_id = s.problemset_id 
                                WHERE c.contest_id = %s;
                            """
INSERT_INTO_PLAGIARISMS = """
                                INSERT INTO `Plagiarisms`
                                (`contest_id`, `submission_id_1` , `submission_id_2`, `score_1`, `score_2` , `contents`)
                                VALUES
                                (1, 2, 3, 5, 5, 6)
                            """

#Constants
START_RED = "<span class='highlight-red'>"
START_GREEN = "<span class='highlight-green'>"
END = "</span>"

SubmissionDownloader = Callable[[str, str], None]

class S3SubmissionDownloader:
    ''' A SubmissionDownloader that can download files from an S3 bucket.  '''

    def __init__(self, bucket_name: str = 'omegaup-backup') -> None:
        self._bucket = boto3.client('s3').Bucket(bucket_name)
    
    def __call__(self, guid: str, destination_path: str) -> None:
        self._bucket.download_file(f'omegaup/submissions/{guid[:2]}/{guid[2:]}', destination_path)
    
class LocalSubmissionDownloader:

    def __init__(self, dir: str) -> None:
        self._dir = dir
    
    def __call__(self, guid: str, destination_path: str) -> None:
        
        shutil.copyfile(os.path.join(self._dir, "test1.cpp"), destination_path)
def get_range(code: Sequence[str]) -> Sequence[int]:

    code_range = []
    for i in range(0,len(code)):
        if START_RED in code[i]:
            code_range.append(i)
        if( START_GREEN in code[i]):
            code_range.append(i)
        if END in code[i]:
            code_range.append(i)

    return code_range

def filter_and_format_result(result: Sequence[Any]) -> Sequence[Any]:
    for r in result:
        r[0] = int(100*r[0])
        r[1] = int(100*r[1])
        r[2] = r[2].split('/')[4].split('.')[0]
        r[3] = r[3].split('/')[4].split('.')[0]
        r[4] = get_range(r[4].split('\n'))
        r[5] = get_range(r[5].split('\n'))
    return result

def run_copy_detect(dir: str, contest_id: int, submissions: Iterable[Tuple[Any, ...]]) -> Dict[Any, Any]:
    result = {}
    for problem in os.listdir(dir):
        detector = CopyDetector(test_dirs=
                                    [(os.path.join(dir, problem))],
                                    extensions=["cpp", "py", "py3", "java", "c"], display_t=0.9, autoopen = False, 
                                    disable_filtering=True)
        detector.run()
        detector_result = detector.get_copied_code_list()
        result[problem] = (filter_and_format_result(detector_result))
    return result
    
def download_submission_files(dbconn: lib.db.Connection, dir: str, 
    download: SubmissionDownloader, submission_ids: Iterable[Tuple[Any, ...]]) -> None:

    for submission in submission_ids:
        submission_path = os.path.join(dir, str(submission[3]), f'{submission[5]}.{submission[6]}')
        os.makedirs(os.path.dirname(submission_path), exist_ok=True)
        download(submission[5], submission_path)

def get_contests(dbconn: lib.db.Connection) -> Iterable[Tuple[Any, ...]]:
    with dbconn.cursor() as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON)
        return cur.fetchall()

def get_submissions_for_contest(dbconn: lib.db.Connection, contest_id: int) -> Iterable[Tuple[Any, ...]]:
    with dbconn.cursor() as cur:
        cur.execute(GET_CONTEST_SUBMISSION_IDS, (contest_id, ))
        return cur.fetchall()

def run_detector_for_contest(dbconn: lib.db.Connection, 
    download: SubmissionDownloader, contest_id: int) -> None:

    with tempfile.TemporaryDirectory(prefix='plagiarism_detector') as dir:
        submissions = get_submissions_for_contest(dbconn, contest_id)
        download_submission_files(dbconn, dir, download, submissions)
        result = run_copy_detect(dir, contest_id, submissions)
        print(result)
    with dbconn.cursor() as cur:
        cur.execute(INSERT_INTO_PLAGIARISMS)

def main() -> None:
    ''' Main entrypoint. '''
    parser = argparse.ArgumentParser(
        description='Runs the Plagiarism Detector'
    )
    parser.add_argument('--local-downloader-dir')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    args.verbose = True
    lib.logs.init(parser.prog, args)

    logging.info('started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))

    if args.local_downloader_dir != None:
        download: SubmissionDownloader = LocalSubmissionDownloader(args.local_downloader_dir)
    else:
        download= S3SubmissionDownloader()

    for contest in get_contests(dbconn):
        run_detector_for_contest(dbconn, download, int(contest[0]))

if __name__ == '__main__':
    main()