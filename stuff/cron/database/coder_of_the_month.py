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


def get_last_12_coders_of_the_month(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    category: str,
) -> List[str]:
    '''Returns the last 12 coders of the month, to avoid repeating users'''

    # Note: This query should be always syncronized with the one in the
    # function getCodersOfTheMonth located in the /DAO/CoderOfTheMonth.php file
    sql = '''
          SELECT
              cm.time,
              i.username,
              IFNULL(i.country_id, 'xx') AS country_id,
              e.email,
              IFNULL(ur.classname, 'user-rank-unranked') AS classname
          FROM
              Coder_Of_The_Month cm
          INNER JOIN
              Users u ON u.user_id = cm.user_id
          INNER JOIN
              Identities i ON i.identity_id = u.main_identity_id
          LEFT JOIN
              Emails e ON e.user_id = u.user_id
          LEFT JOIN
              User_Rank ur ON ur.user_id = cm.user_id
          WHERE
              (cm.selected_by IS NOT NULL
              OR (
                  cm.`ranking` = 1 AND
                  NOT EXISTS (
                      SELECT
                          *
                      FROM
                          Coder_Of_The_Month
                      WHERE
                          time = cm.time AND
                          selected_by IS NOT NULL AND
                          category = %s
                  )
              ))
              AND cm.category = %s
              AND cm.time <= %s
              AND cm.time > DATE_SUB(%s, INTERVAL 12 MONTH)
          ORDER BY
              cm.time DESC
          LIMIT
              0, 12;
    '''
    cur_readonly.execute(sql, (category, category,
                               first_day_of_current_month,
                               first_day_of_current_month))

    coders = []
    for row in cur_readonly.fetchall():
        coders.append(row['username'])

    return coders


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
    category: str,
    last_12_coders: List[str],
) -> List[UserRank]:
    '''Returns the list of eligible users for coder of the month'''

    last_12_coders_str = ', '.join(f"'{coder}'" for coder in last_12_coders)

    if category == 'female':
        gender_clause = " AND i.gender = 'female'"
    else:
        gender_clause = ""

    if not last_12_coders:
        last_12_coders_clause = ''
    else:
        last_12_coders_clause = f'AND i.username NOT IN ({last_12_coders_str})'
    logging.info(
        'Getting the list of eligible users in the category [%s] for coder of '
        'the month', category
    )
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
                -- Exclude site-admins (acl_id = 1 is SYSTEM_ACL,
                -- role_id = 1 is ADMIN_ROLE)
                -- TODO: Replace magic numbers with constants
                AND i.user_id NOT IN (
                    SELECT ur.user_id
                    FROM User_Roles ur
                    WHERE ur.acl_id = 1 AND ur.role_id = 1
                )
                {last_12_coders_clause}
                {gender_clause}
            GROUP BY
                i.identity_id;
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
) -> Dict[int, Problem]:
    '''Returns the list of eligible problems for coder of the month'''

    logging.info(
        'Getting the list of eligible problems for coder of the month'
    )
    sql = '''
        SELECT DISTINCT
            p.problem_id,
            p.alias,
            IFNULL(ROUND(100 / LOG(2, p.accepted+1)), 0) AS score
        FROM
            Problems p
        WHERE
            p.visibility >= 1 AND p.quality_seal = 1;
    '''
    cur_readonly.execute(sql)

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

    problems_admins = get_problems_admins(cur_readonly, problem_ids_str)

    cur_readonly.execute(f'''
            WITH
                ProblemsForfeitedByUser AS (
                    SELECT
                        pf.user_id,
                        pf.problem_id,
                        pf.forfeited_date
                    FROM
                        Problems_Forfeited pf
                    WHERE
                        forfeited_date IS NULL
                )
            SELECT
                s.identity_id,
                s.problem_id,
                MIN(s.time) AS first_time_solved
            FROM
                Submissions s
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            LEFT JOIN
                ProblemsForfeitedByUser pfbu
            ON
                pfbu.user_id = i.user_id
                AND pfbu.problem_id = s.problem_id
            WHERE
                s.identity_id IN ({identity_ids_str})
                AND s.problem_id IN ({problem_ids_str})
                AND s.verdict = 'AC'
                AND s.type = 'normal'
                AND pfbu.forfeited_date IS NULL
            GROUP BY
                s.identity_id, s.problem_id;
    ''')

    # Populate user_problems dictionary with the problems solved by each user
    for row in cur_readonly.fetchall():
        identity_id = row['identity_id']
        problem_id = row['problem_id']
        solved = row['first_time_solved'].date()
        assert identity_id in user_problems, (
            'Identity %s not found in user_problems', identity_id)
        # Filter the problems solved for the first time in the selected month
        if first_day_of_current_month <= solved < first_day_of_next_month:
            # Filter the problems that are not administred by the user
            if identity_id not in problems_admins.get(problem_id, []):
                user_problems[identity_id]['solved'].append(problem_id)

    return user_problems


def get_problems_admins(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    problem_ids_str: str,
) -> Dict[int, List[int]]:
    '''Get the list of problems admins'''

    cur_readonly.execute(f'''
        SELECT
            p.problem_id,
            ai.identity_id
        FROM
            Problems AS p
        INNER JOIN
            ACLs AS a ON a.acl_id = p.acl_id
        INNER JOIN
            Identities AS ai ON a.owner_id = ai.user_id
        WHERE
            p.problem_id IN ({problem_ids_str})
        UNION DISTINCT
        SELECT
            p.problem_id,
            uri.identity_id
        FROM
            Problems AS p
        INNER JOIN
            User_Roles ur ON ur.acl_id = p.acl_id
            AND ur.role_id = 1
        INNER JOIN
            Identities uri ON ur.user_id = uri.user_id
        WHERE
            p.problem_id IN ({problem_ids_str})
        UNION DISTINCT
        SELECT
            p.problem_id,
            gi.identity_id
        FROM
            Problems AS p
        INNER JOIN
            Group_Roles gr ON gr.acl_id = p.acl_id
            AND gr.role_id = 1
        INNER JOIN
            Groups_Identities gi ON gi.group_id = gr.group_id
        WHERE
            p.problem_id IN ({problem_ids_str})
        ORDER BY
            problem_id;
    ''')

    problems_admins: Dict[int, List[int]] = {}

    for row in cur_readonly.fetchall():
        problem_id = row['problem_id']
        if problem_id not in problems_admins:
            problems_admins[problem_id] = []
        identity_id = row['identity_id']
        problems_admins[problem_id].append(identity_id)

    return problems_admins

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
