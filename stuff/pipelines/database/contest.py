#!/usr/bin/python3

'''Mysql queries to generate messages for contests'''

import datetime
from typing import Dict, List, NamedTuple

import mysql.connector
import mysql.connector.cursor


class ContestCertificate(NamedTuple):
    '''Relevant information for contests.'''
    certificate_cutoff: str
    alias: str
    scoreboard_url: str
    contest_id: str


def get_contest_contestants(
        *,
        cur: mysql.connector.cursor.MySQLCursorDict,
        date_lower_limit: datetime.date,
        date_upper_limit: datetime.date,
) -> List[Dict[str, str]]:
    '''Get contest users to recieve a certificate'''

    date_upper = date_upper_limit + datetime.timedelta(days=1)
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
            finish_time BETWEEN %s AND %s;
        ''', (date_lower_limit, date_upper)
    )
    data: List[Dict[str, str]] = []
    for row in cur:
        contest = ContestCertificate(
            certificate_cutoff=row['certificate_cutoff'],
            alias=row['alias'],
            scoreboard_url=row['scoreboard_url'],
            contest_id=row['contest_id'],
        )
        data.append(contest._asdict())
    return data
