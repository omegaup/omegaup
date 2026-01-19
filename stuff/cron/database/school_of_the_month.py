'''Helper functions for School of the Month cron job'''
import datetime
import logging
from typing import NamedTuple, List

import mysql.connector
import mysql.connector.cursor


class School(NamedTuple):
    '''
    Docstring for School
    Represents a school candidate for School of the Month
    '''
    school_id: int
    name: str
    score: float


def check_existing_school_of_the_next_month(
        cur_readonly: mysql.connector.cursor.MySQLCursorDict,
        first_day_of_next_month: datetime.date
) -> bool:
    '''Check if school of the month already exists for the next month'''
    logging.info("Checking if school of the month already exists")
    cur_readonly.execute(
        '''
        SELECT
            COUNT(*) AS `count`
        FROM
            `School_Of_The_Month`
        WHERE
            `time` = %s AND
            `selected_by` IS NOT NULL;
        ''', (first_day_of_next_month, ))
    count = int(cur_readonly.fetchone()['count'])

    return count > 0


def remove_school_of_the_month_candidates(
        cur: mysql.connector.cursor.MySQLCursorDict,
        first_day_of_next_month: datetime.date
) -> None:
    '''Remove existing school of the month candidates for the next month'''
    logging.info("Removing school of the month candidates")
    cur.execute(
        '''
    DELETE FROM
        `School_Of_The_Month`
    WHERE
        `time` = %s;
    ''', (first_day_of_next_month, ))


def get_school_of_the_month_candidates(
        cur_readonly: mysql.connector.cursor.MySQLCursorDict,
        first_day_of_next_month: datetime.date,
        first_day_of_current_month: datetime.date
) -> List[School]:
    '''Get school of the month candidates'''
    logging.info("Getting school of the month candidates")
    sql = '''
        SELECT
            `s`.`school_id`,
            `s`.`name`,
            IFNULL(
                SUM(
                    ROUND(
                        100 / LOG(2, `distinct_school_problems`.`accepted`+1),
                        0
                    )
                ),
                0.0
            ) AS `score`
        FROM
            `Schools` AS `s`
        INNER JOIN
            (
                SELECT
                    `su`.`school_id`,
                    `p`.`accepted`,
                    MIN(`su`.`time`) AS `first_ac_time`
                FROM
                    `Submissions` AS `su`
                INNER JOIN
                    `Problems` AS `p` ON `p`.`problem_id` = `su`.`problem_id`
                WHERE
                    `su`.`verdict` = "AC"
                    AND `p`.`visibility` >= 1
                    AND `su`.`school_id` IS NOT NULL
                GROUP BY
                    `su`.`school_id`,
                    `su`.`problem_id`
                HAVING
                    `first_ac_time` BETWEEN %s AND %s
            ) AS `distinct_school_problems`
        ON
            `distinct_school_problems`.`school_id` = `s`.`school_id`
        WHERE
            NOT EXISTS (
                SELECT
                    `sotm`.`school_id`,
                    MAX(`time`) AS `latest_time`
                FROM
                    `School_Of_The_Month` AS `sotm`
                WHERE
                    `sotm`.`school_id` = `s`.`school_id`
                    AND (
                        `sotm`.`selected_by` IS NOT NULL OR
                        `sotm`.`ranking` = 1
                    )
                GROUP BY
                    `sotm`.`school_id`
                HAVING
                    DATE_ADD(`latest_time`, INTERVAL 1 YEAR) >= %s
            )
        GROUP BY
            `s`.`school_id`
        ORDER BY
            `score` DESC
        LIMIT 100;
    '''
    cur_readonly.execute(
        sql, (first_day_of_current_month, first_day_of_next_month,
              first_day_of_next_month))
    candidates: List[School] = []
    for row in cur_readonly.fetchall():
        candidates.append(
            School(
                school_id=row['school_id'],
                name=row['name'],
                score=row['score'],
            )
        )
    return candidates


def insert_school_of_the_month_candidates(
    cur: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_next_month: datetime.date,
    candidates: list[School],
) -> None:
    '''Insert school of the month candidates'''
    logging.info("Inserting school of the month candidates")
    for index, row in enumerate(candidates):
        cur.execute(
            '''
            INSERT INTO
                `School_Of_The_Month` (
                    `school_id`,
                    `time`,
                    `ranking`,
                    `score`
                )
            VALUES (
                %s,
                %s,
                %s,
                %s
            );
            ''', (row.school_id, first_day_of_next_month,
                  index + 1, row.score))
