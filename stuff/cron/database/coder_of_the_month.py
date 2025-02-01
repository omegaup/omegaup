#!/usr/bin/env python3

'''Mysql queries to calculate the coder of the month candidates and winners'''

import datetime
import logging
from typing import Dict, List, NamedTuple, Optional, TypedDict

import mysql.connector
import mysql.connector.cursor


class Problem(NamedTuple):
    '''Information for solved problems in the selected month'''
    problem_id: int
    alias: str
    score: float


class UserRank(NamedTuple):
    '''User information for coder of the month candidates'''
    user_id: int
    identity_id: int
    username: str
    country_id: str
    school_id: Optional[int]
    problems_solved: int
    score: float
    classname: str


class UserProblems(TypedDict):
    '''Problems solved by a user and their calculated score'''
    solved: List[int]
    score: float


def get_first_day_of_next_month(
    first_day_of_current_month: datetime.date
) -> datetime.date:
    '''Get the first day of the next month'''

    if first_day_of_current_month.month == 12:
        first_day_of_next_month = datetime.date(
            first_day_of_current_month.year + 1, 1, 1)
    else:
        first_day_of_next_month = datetime.date(
            first_day_of_current_month.year,
            first_day_of_current_month.month + 1, 1)

    return first_day_of_next_month


def check_existing_coder_of_the_month(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_the_next_month: datetime.date,
    category: str,
) -> bool:
    '''Make sure there are not already selected coder of the month'''

    logging.info('Checking if coder of the month already exists')

    cur_readonly.execute(
        '''
            SELECT
                COUNT(*) AS `count`
            FROM
                `Coder_Of_The_Month`
            WHERE
                `time` = %s AND
                `selected_by` IS NOT NULL AND
                `category` = %s;
            ''', (first_day_of_the_next_month, category))

    count = int(cur_readonly.fetchone()['count'])

    return count > 0


def remove_coder_of_the_month_candidates(
    cur: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_the_next_month: datetime.date,
    category: str,
) -> None:
    '''Remove coder of the month candidates for the next month'''

    logging.info('Removing coder of the month candidates')

    cur.execute(
        '''
            DELETE FROM
                `Coder_Of_The_Month`
            WHERE
                `time` = %s AND
                `category` = %s;
            ''', (first_day_of_the_next_month, category))


def insert_coder_of_the_month_candidates(
    cur: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_next_month: datetime.date,
    ranking: int,
    category: str,
    candidate: UserRank,
) -> None:
    '''Insert coder of the month candidates'''

    cur.execute(
        '''
            INSERT INTO
                `Coder_Of_The_Month` (
                    `user_id`,
                    `time`,
                    `ranking`,
                    `school_id`,
                    `category`,
                    `score`,
                    `problems_solved`
                )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s
            );
            ''',
        (candidate.user_id, first_day_of_next_month, ranking,
         candidate.school_id, category, candidate.score,
         candidate.problems_solved))


def get_cotm_eligible_users(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    first_day_of_next_month: datetime.date,
    gender_clause: str,
) -> List[UserRank]:
    '''Returns the list of eligible users for coder of the month'''

    logging.info('Getting the list of eligible users for coder of the month')
    sql = f'''
            SELECT DISTINCT
                IFNULL(i.user_id, 0) AS user_id,
                i.identity_id,
                i.username,
                IFNULL(i.country_id, 'xx') AS country_id,
                isc.school_id,
                IFNULL(
                    (
                        SELECT urc.classname FROM
                            User_Rank_Cutoffs urc
                        WHERE
                            urc.score <= (
                                    SELECT
                                        ur.score
                                    FROM
                                        User_Rank ur
                                    WHERE
                                        ur.user_id = i.user_id
                                )
                        ORDER BY
                            urc.percentile ASC
                        LIMIT
                            1
                    ),
                    'user-rank-unranked'
                ) AS classname
            FROM
                Identities i
            INNER JOIN
                Submissions s
            ON
                s.identity_id = i.identity_id
            INNER JOIN
                Problems p
            ON
                p.problem_id = s.problem_id
            LEFT JOIN
                Identities_Schools isc
            ON
                isc.identity_school_id = i.current_identity_school_id
            WHERE
                s.verdict = 'AC' AND s.type= 'normal' AND s.time >= %s AND
                s.time <= %s AND p.visibility >= 1 AND p.quality_seal = 1 AND
                i.user_id IS NOT NULL
                {gender_clause}
            GROUP BY
                i.identity_id
            LIMIT 100;
            '''
    cur_readonly.execute(sql, (
        first_day_of_current_month,
        first_day_of_next_month,
    ))

    usernames: List[UserRank] = []
    for row in cur_readonly.fetchall():
        usernames.append(UserRank(
            user_id=row['user_id'],
            identity_id=row['identity_id'],
            username=row['username'],
            country_id=row['country_id'],
            school_id=row['school_id'],
            classname=row['classname'],
            problems_solved=0,
            score=0.0,
        ))

    return usernames


def get_eligible_problems(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    first_day_of_next_month: datetime.date,
) -> Dict[int, Problem]:
    '''Returns the list of eligible problems for coder of the month'''

    logging.info(
        'Getting the list of eligible problems for coder of the month'
    )
    sql = '''
        SELECT DISTINCT
            p.problem_id,
            p.alias,
            IFNULL(SUM(ROUND(100 / LOG(2, p.accepted+1) , 0)), 0) AS score
        FROM
            Submissions s
        INNER JOIN
            Problems p
        ON
            p.problem_id = s.problem_id
        WHERE
            s.verdict = 'AC' AND s.type= 'normal' AND s.time >= %s AND
            s.time < %s AND p.visibility >= 1 AND p.quality_seal = 1
        GROUP BY
            p.problem_id;
        '''
    cur_readonly.execute(sql, (
        first_day_of_current_month,
        first_day_of_next_month,
    ))

    problems: Dict[int, Problem] = {}
    for row in cur_readonly.fetchall():
        problems[row['problem_id']] = Problem(
            problem_id=row['problem_id'],
            alias=row['alias'],
            score=row['score'],
        )

    return problems


def get_user_problems(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    identity_ids_str: str,
    problem_ids_str: str,
    eligible_users: List[UserRank],
    first_day_of_current_month: datetime.date,
) -> Dict[int, UserProblems]:
    '''Returns the problems solved by a user'''

    # Initialize a dictionary to store the problems solved and the score for
    # each user
    user_problems: Dict[int, UserProblems] = {
        user.identity_id:
            {'solved': [], 'score': 0.0} for user in eligible_users}

    first_day_of_next_month = get_first_day_of_next_month(
        first_day_of_current_month)

    cur_readonly.execute(f'''
            SELECT
                identity_id,
                problem_id,
                MIN(time) AS first_time_solved
            FROM
                Submissions
            WHERE
                identity_id IN ({identity_ids_str})
                AND problem_id IN ({problem_ids_str})
                AND verdict = 'AC'
                AND type = 'normal'
            GROUP BY
                identity_id, problem_id;
    ''')

    # Populate user_problems dictionary with the problems solved by each user
    for row in cur_readonly.fetchall():
        identity_id = row['identity_id']
        problem_id = row['problem_id']
        solved = row['first_time_solved'].date()
        assert identity_id in user_problems, (
            'Identity %s not found in user_problems', identity_id)
        if first_day_of_current_month <= solved < first_day_of_next_month:
            user_problems[identity_id]['solved'].append(problem_id)

    return user_problems


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
