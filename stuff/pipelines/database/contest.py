#!/usr/bin/python3

'''Mysql queries to generate messages for contests'''

import datetime
from typing import List, NamedTuple

import mysql.connector
import mysql.connector.cursor


class ContestCertificate(NamedTuple):
    '''Certificate cutoff for contests.'''
    certificate_cutoff: int
    alias: str
    scoreboard_url: str
    contest_id: str


def get_contest_contestants(
        *,
        date_lower_limit: datetime.date,
        date_upper_limit: datetime.date,
        cur: mysql.connector.cursor.MySQLCursorDict
) -> List[ContestCertificate]:
    '''Get contest users to recieve a certificate'''

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
        ''', (date_lower_limit, date_upper_limit)
    )
    data: List[ContestCertificate] = list()
    for row in cur:
        data.append({
            'certificate_cutoff': row['certificate_cutoff'],
            'alias': row['alias'],
            'scoreboard_url': row['scoreboard_url'],
            'contest_id': row['contest_id'],
        })
    return data
