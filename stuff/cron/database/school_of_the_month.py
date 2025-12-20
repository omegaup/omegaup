import mysql.connector, mysql.connector.cursor
import datetime
import logging
from typing import NamedTuple, List

class School(NamedTuple):
    school_id: int
    country_id: str
    state_id: str
    name: str
    ranking: int
    score: float



def check_existing_school_of_the_next_month(
        cur_readonly: mysql.connector.cursor.MySQLCursorDict,
        first_day_of_next_month: datetime.date
):
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
):
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
):
    logging.info("Getting school of the month candidates")
    cur_readonly.execute(
    '''
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
    ''', (first_day_of_current_month, first_day_of_next_month,
            first_day_of_next_month))
    
    candidates: List[School] = []
    for row in cur_readonly.fetchall():
        candidates.append(
            School(
                school_id=row['school_id'],
                country_id=row['country_id'],
                state_id=row['state_id'],
                name=row['name'],
                ranking=row['ranking'],
                score=row['score'],
            )
        )
    return candidates


# falta 

def insert_school_of_the_month_candidates(
        cur: mysql.connector.cursor.MySQLCursorDict,
        school: School,
        first_day_of_next_month: datetime.date,
        selected_by: int
):
    logging.info("Inserting school of the month candidate: %s", school.name)
    cur.execute(
    '''
        INSERT INTO
            `School_Of_The_Month` (
                `school_id`,
                `time`,
                `ranking`,
                `score`,
                `selected_by`
            ) VALUES (%s, %s, %s, %s, %s);
    ''', (
        school.school_id,
        first_day_of_next_month,
        school.ranking,
        school.score,
        selected_by
    ))

# def select_school():

# def calculate_score():

# def school_candidates():

# 