#!/usr/bin/python3

'''Mysql consults to generate messages'''

import datetime
from typing import Any, Dict
import mysql.connector
import mysql.connector.cursor


def get_coder_of_the_month(cur: mysql.connector.cursor.MySQLCursorDict,
                           category: str) -> Dict[str, Any]:
    '''Get coder of the month'''
    today = datetime.date.today()
    first_day_of_current_month = today.replace(day=1)
    if first_day_of_current_month.month == 12:
        first_day_of_next_month = datetime.date(
            first_day_of_current_month.year + 1,
            1,
            1)
    else:
        first_day_of_next_month = datetime.date(
            first_day_of_current_month.year,
            first_day_of_current_month.month + 1,
            1)
    cur.execute(
        '''
                SELECT
                    user_id, time, category
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = %s AND
                    `selected_by` IS NOT NULL AND
                    `category` = %s;
        ''', (first_day_of_next_month, category))
    for row in cur:
        data = {"user_id": row['user_id'],
                "time": row['time'],
                "category": row['category']}
    return data
