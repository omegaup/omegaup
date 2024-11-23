#!/usr/bin/env python3

'''Mysql queries to calculate the coder of the month candidates and winners'''

import datetime
import logging
from typing import List, NamedTuple, Optional

import mysql.connector
import mysql.connector.cursor


class Problem(NamedTuple):
    '''Information for solved problems in the selected month'''
    problem_id: int
    alias: str
    accepted: int


class UserRank(NamedTuple):
    '''Information for user rank'''
    user_id: int
    identity_id: int
    username: str
    country_id: str
    school_id: Optional[int]
    problems_solved: int
    score: float
    classname: str


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


def get_coder_of_the_month_candidates(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    first_day_of_next_month: datetime.date,
    category: str,
) -> List[UserRank]:
    ''' Returns the list of candidates for coder of the month'''

    if category == 'female':
        gender_clause = " AND i.gender = 'female'"
    else:
        gender_clause = ""

    sql = f'''
         SELECT DISTINCT
            IFNULL(i.user_id, 0) AS user_id,
            i.username,
            i.identity_id,
            IFNULL(i.country_id, 'xx') AS country_id,
            isc.school_id,
            COUNT(ps.problem_id) ProblemsSolved,
            IFNULL(SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)), 0) AS score,
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
            (
              SELECT DISTINCT
                s.identity_id, s.problem_id
              FROM
                Submissions s
              WHERE
                s.verdict = 'AC' AND s.type= 'normal' AND
                s.time >= %s AND s.time <= %s
            ) AS up
          INNER JOIN
            Problems ps ON
            ps.problem_id = up.problem_id
            AND ps.visibility >= 1
            AND ps.quality_seal = 1
          INNER JOIN
            Identities i ON i.identity_id = up.identity_id
          LEFT JOIN
            Identities_Schools isc ON isc.identity_school_id =
            i.current_identity_school_id
          LEFT JOIN
            (
              SELECT
                user_id,
                MIN(ranking) best_ranking,
                time,
                selected_by
              FROM
                Coder_Of_The_Month
              WHERE
                category = %s
              GROUP BY
                user_id,
                selected_by,
                time
              HAVING
                best_ranking = 1
            ) AS cm on i.user_id = cm.user_id
          WHERE
            (cm.user_id IS NULL OR
            DATE_ADD(cm.time, INTERVAL 1 YEAR) < %s) AND
            i.user_id IS NOT NULL
            {gender_clause}
          GROUP BY
            up.identity_id
          ORDER BY
            score DESC,
            ProblemsSolved DESC
          LIMIT 100;
        '''
    cur_readonly.execute(sql, (
        first_day_of_current_month,
        first_day_of_next_month,
        category,
        first_day_of_next_month,
    ))

    candidates: List[UserRank] = []
    for row in cur_readonly.fetchall():
        candidates.append(UserRank(
            user_id=row['user_id'],
            identity_id=row['identity_id'],
            username=row['username'],
            country_id=row['country_id'],
            school_id=row['school_id'],
            problems_solved=row['ProblemsSolved'],
            score=row['score'],
            classname=row['classname'],
        ))

    return candidates


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
) -> List[Problem]:
    '''Returns the list of eligible problems for coder of the month'''

    logging.info(
        'Getting the list of eligible problems for coder of the month'
    )
    sql = '''
        SELECT DISTINCT
            p.problem_id, p.alias, p.accepted
        FROM
            Submissions s
        INNER JOIN
            Problems p
        ON
            p.problem_id = s.problem_id
        WHERE
            s.verdict = 'AC' AND s.type= 'normal' AND s.time >= %s AND
            s.time <= %s AND p.visibility >= 1 AND p.quality_seal = 1;
        '''
    cur_readonly.execute(sql, (
        first_day_of_current_month,
        first_day_of_next_month,
    ))

    problems: List[Problem] = []
    for row in cur_readonly.fetchall():
        problems.append(Problem(
            problem_id=row['problem_id'],
            alias=row['alias'],
            accepted=row['accepted'],
        ))

    return problems


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
