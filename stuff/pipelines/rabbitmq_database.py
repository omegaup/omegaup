#!/usr/bin/python3

'''Mysql consults to generate messages'''

import datetime
from typing import Any, Dict, List, Optional
import mysql.connector
import mysql.connector.cursor


def get_contest_contestants(
        *,
        date_lower_limit: datetime.date,
        date_upper_limit: datetime.date,
        cur: Optional[mysql.connector.cursor.MySQLCursorDict] = None
) -> List[Dict[str, Any]]:
    '''Get contest users to recieve a certificate'''
    if cur is None:
        return []

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
    data = list()
    for row in cur:
        data.append({
            'certificate_cutoff': row['certificate_cutoff'],
            'alias': row['alias'],
            'scoreboard_url': row['scoreboard_url'],
            'contest_id': row['contest_id'],
        })
    return data
