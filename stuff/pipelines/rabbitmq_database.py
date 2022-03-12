#!/usr/bin/python3

'''Mysql consults to generate messages'''

import datetime
from typing import Any, Dict, Optional
from dataclasses import dataclass, field
import logging
import mysql.connector
import mysql.connector.cursor
from mysql.connector import errors, errorcode
from verification_code import generate_code


def get_coder_of_the_month(
        category: str,
        cur: Optional[mysql.connector.cursor.MySQLCursorDict] = None
) -> Dict[str, Any]:
    '''Get coder of the month'''
    if cur is None:
        return {}
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


def verificate_coder_of_the_month(
        cur: mysql.connector.cursor.MySQLCursorDict,
        user_id: str) -> bool:
    '''verificate if certificate exist'''
    cur.execute('''
                SELECT
                    COUNT(*) AS `count`
                FROM
                    `Certificates`
                WHERE
                    `identity_id` = %s AND
                    `certificate_type` = 'coder_of_the_month' OR
                    `certificate_type` = 'coder_of_the_month_female' AND
                    MONTH(timestamp) = MONTH(CURDATE()) AND
                    YEAR(timestamp) = YEAR(CURDATE());
                ''', (user_id,))
    for row in cur:
        if row['count'] > 0:
            logging.info('Skipping because already exist certificate')
            return True
    return False


@dataclass
class MESSAGE:
    '''class to save message and use it in client test.
       Avoiding connecting to the database.'''
    message: Dict[str, Any] = field(default_factory=dict)


new_message = MESSAGE()


def insert_coder_of_the_month(
        data: Dict[str, Any],
        cur: Optional[mysql.connector.cursor.MySQLCursorDict] = None,
        dbconn: Optional[mysql.connector.MySQLConnection] = None
) -> None:
    '''Insert Certificates table'''
    if cur is None or dbconn is None:
        new_message.message = data
        return
    if verificate_coder_of_the_month(cur, data["user_id"]):
        return
    code_verification = 'XQ92QMMMXF'
    while True:
        try:
            if data["category"] == "all":
                cur.execute('''
                            INSERT INTO
                                `Certificates` (`identity_id`,
                                            `certificate_type`,
                                            `verification_code`)
                        VALUES(%s, %s, %s);''',
                            (data["user_id"],
                             'coder_of_the_month', code_verification))
            else:
                cur.execute('''
                            INSERT INTO
                                `Certificates` (`identity_id`,
                                            `certificate_type`,
                                            `verification_code`)
                        VALUES(%s, %s, %s);''',
                            (data["user_id"],
                             'coder_of_the_month_female', code_verification))
            dbconn.commit()
            break
        except errors.IntegrityError as err:
            logging.info(type(err))
            dbconn.rollback()
            if err.errno != errorcode.ER_DUP_ENTRY:
                raise
            code_verification = generate_code()
            logging.exception(
                'Verification codes had a conflict'
            )
