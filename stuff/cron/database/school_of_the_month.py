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
    '''Returns a list of elegible schools of the month candidates'''
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
                INNER JOIN
                    `Identities` AS `i`
                    ON `i`.`identity_id` = `su`.`identity_id`
                INNER JOIN
                    `Users` AS `u` ON `u`.`user_id` = `i`.`user_id`
                WHERE
                    `su`.`verdict` = "AC"
                    AND `p`.`visibility` >= 1
                    AND `p`.`quality_seal` = 1
                    AND `su`.`school_id` IS NOT NULL
                    AND `u`.`main_email_id` IS NOT NULL
                    AND `i`.`user_id` IS NOT NULL
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


def update_schools_solved_problems(
    cur: mysql.connector.cursor.MySQLCursorDict) -> None:
    '''Updates the solved problems count by each school the last 6 months'''

    logging.info('Updating schools solved problems...')

    months = 6  # in case this parameter requires adjustments
    cur.execute('DELETE FROM `Schools_Problems_Solved_Per_Month`')
    cur.execute(
        '''
        INSERT INTO
            `Schools_Problems_Solved_Per_Month` (
                `school_id`,
                `time`,
                `problems_solved`
            )
        SELECT
            `sc`.`school_id`,
            STR_TO_DATE(
                CONCAT (
                    YEAR(`su`.`time`), '-', MONTH(`su`.`time`), '-01'
                ),
                "%Y-%m-%d"
            ) AS `time`,
            COUNT(DISTINCT `su`.`problem_id`) AS `problems_solved`
        FROM
            `Submissions` AS `su`
        INNER JOIN
            `Runs` AS `r` ON `r`.run_id = `su`.current_run_id
        INNER JOIN
            `Schools` AS `sc` ON `sc`.`school_id` = `su`.`school_id`
        INNER JOIN
            `Problems` AS `p` ON `p`.`problem_id` = `su`.`problem_id`
        WHERE
            `su`.`time` >= CURDATE() - INTERVAL %(months)s MONTH
            AND `r`.`verdict` = "AC"
            AND `p`.`visibility` >= 1
            AND NOT EXISTS (
                SELECT
                    *
                FROM
                    `Submissions` AS `sub`
                WHERE
                    `sub`.`problem_id` = `su`.`problem_id`
                    AND `sub`.`identity_id` = `su`.`identity_id`
                    AND `sub`.`verdict` = "AC"
                    AND `sub`.`time` < `su`.`time`
            )
        GROUP BY
            `sc`.`school_id`,
            `time`
        ORDER BY
            `time` ASC;
    ''', {'months': months})
    