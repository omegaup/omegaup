#!/usr/bin/env python3

from typing import List
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
import os
import random
sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                "."))
import lib.db  # pylint: disable=wrong-import-position
import omegaup.api
import test_constants
from plagiarism_detector import get_contests # can only import if in the same directory.

UPDATE_CHECK_PLAGIARISM = '''
                            UPDATE Contests
                            SET check_plagiarism = 1
                            WHERE alias = %s;

'''

@pytest.fixture(scope='session')
def dbconn() -> lib.db.Connection:
    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user='root',
            password='omegaup',
            host='mysql',
            database='omegaup',
            port= 13306,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )
    return dbconn

def test_get_contests(dbconn: lib.db.Connection) -> None:

    
    client = omegaup.api.Client(api_token=test_constants.API_TOKEN,
                                url=test_constants.OMEGAUP_API_ENDPOINT)
    current_time = datetime.datetime.now() - datetime.timedelta(minutes=30)
    future_time = datetime.datetime.now()
    alias = ''.join(random.choices(string.digits, k=8))

    # Creating a contest and then adding some users
    client.contest.create(
        title=alias,
        alias=alias,
        description='Test contest',
        start_time=time.mktime(current_time.timetuple()),
        finish_time=time.mktime(future_time.timetuple()),
        window_length=0,
        scoreboard=100,
        points_decay_factor=0,
        partial_score=True,
        submissions_gap=1200,
        penalty=0,
        feedback='detailed',
        penalty_type='contest_start',
        languages='py2,py3',
        penalty_calc_policy='sum',
        admission_mode='private',
        show_scoreboard_after=True,
    )
    with dbconn.cursor(dictionary=True) as cur:
        cur.execute(UPDATE_CHECK_PLAGIARISM, (str(alias),))

    usernames: List[str] = []
    for number in range(3):
        user = f'test_user_{number}'
        client.contest.addUser(contest_alias=alias, usernameOrEmail=user)
        usernames.append(user)
        
    assert get_contests(dbconn) == []


# create a contest - DONE
      # from creating the contest we will need make a contest specific to our needs using SQL queries! 
      # add 3 different problems to the contest. 
      # add 3 different users to the contest.  - DONE
      # for each user add 3 submission to each problem. 
      # for all of this we will need to manually call a SQL query unlike how we do this is PHP using DAO's. 
 # run the plagiarism_detector.py
 # then check if there are correct amount of entries in the database for each contest
       # will again use SQL queries. 
 # then check if there are all values correctly in the database.