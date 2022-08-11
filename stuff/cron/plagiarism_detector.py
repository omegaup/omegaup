#!/usr/bin/env python3

''' Main Plagiairism Detector Script. 

This script gets all the contest that finised in the last 15 minutes and acesses those contests and gets
runs copydetect on submissions recieved. 

Finally it pushes the necessary data to the database

'''

import argparse
import calendar
import collections
import copydetect
import json
import logging
import operator
import os
import sys
from typing import (DefaultDict, Dict, Mapping, NamedTuple, Optional, Sequence,
                    Tuple, Set)

from mysql.connector import errorcode
from copydetect import CopyDetector
from datetime import datetime
from datetime import timedelta
sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position



# SQL Queries

CONTESTS_TO_RUN_PLAGIARISM_ON = """ SELECT c.`contest_id`
                                    FROM `Contests` as c
                                    WHERE (c.`check_plagiarism` = 1 AND c.`finish_time` BETWEEN %s AND %s) 
                                        AND c.`contest_id` NOT IN
                                            (SELECT p.`contest_id` 
                                            FROM `Plagiarisms` as p);
                                """

MINUTES = 20

def get_contests(dbconn: lib.db.Connection) -> None:
    now = datetime.now()
    date_format_str = '%d/%m/%Y %H:%M:%S.%f'

    final_time = now - timedelta(minutes=MINUTES)
    final_time_str = final_time.strftime('%d/%m/%Y %H:%M:%S.%f')

    with dbconn.cursor() as cur:
        cur.execute(CONTESTS_TO_RUN_PLAGIARISM_ON,(final_time_str, now))
        contests = cur.fetchall()


def main() -> None:
    '''Main entrypoint. '''
    parser = argparse.ArgumentParser(
    description='Runs the Plagiarism Detector')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))

    try:
        get_contests(dbconn)
    except:
        logging.exception('Failed to generate Plagiarism Table')

if __name__ == '__main__':
    main()