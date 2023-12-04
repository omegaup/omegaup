#!/usr/bin/env python3

'''Mysql queries to generate messages for contests'''

import datetime
from typing import List, NamedTuple

import mysql.connector
import mysql.connector.cursor


class ContestCertificate(NamedTuple):
    '''Relevant information for contests.'''
    certificate_cutoff: int
    alias: str
    scoreboard_url: str
    contest_id: int


def get_contests(
        *,
        cur: mysql.connector.cursor.MySQLCursorDict,
        date_lower_limit: datetime.datetime,
        date_upper_limit: datetime.datetime,
) -> List[ContestCertificate]:
    '''Get contests information'''

    cur.execute(
        '''
        SELECT
            certificate_cutoff,
            c.contest_id,
            alias,
            scoreboard_url
        FROM
            Contests c
        INNER JOIN
            Problemsets p
        ON
            c.problemset_id = p.problemset_id
        WHERE
            finish_time >= %s AND finish_time <= %s;
        ''', (date_lower_limit,
              date_upper_limit.replace(hour=23, minute=59, second=59))
    )
    data: List[ContestCertificate] = []
    for row in cur:
        contest = ContestCertificate(
            certificate_cutoff=row['certificate_cutoff'],
            alias=row['alias'],
            scoreboard_url=row['scoreboard_url'],
            contest_id=row['contest_id'],
        )
        data.append(contest)
    return data


def get_users_ids(
    cur: mysql.connector.cursor.MySQLCursorDict,
    usernames: List[str]
) -> List[int]:
    '''Returns a list of ids of the contestants.'''
    users_ids: List[int] = []
    for username in usernames:
        cur.execute('''
            SELECT
                user_id
            FROM
                Identities
            WHERE
                username = %s''', (username,))
        result = cur.fetchone()
        users_ids.append(result['user_id'])
    return users_ids


def get_contest_title(
    cur: mysql.connector.cursor.MySQLCursorDict,
    contest_id: int
) -> str:
    '''Returns the title of the contest.'''
    cur.execute('''
        SELECT
            title
        FROM
            Contests
        WHERE
            contest_id = %s''', (contest_id,))
    result = cur.fetchone()
    return str(result['title'])
