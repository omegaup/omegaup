'''Helper functions for School of the Month cron job'''
import datetime
import logging
from typing import NamedTuple, List

import mysql.connector
import mysql.connector.cursor


class School(NamedTuple):
    '''
    Represents a school candidate for School of the Month
    '''
    school_id: int
    name: str
    score: float


class ProblemSolved(NamedTuple):
    '''
    Represents problems solved per month for a school
    '''
    school_id: int
    time: datetime.date
    problems_solved: int


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


def delete_problems_solved_per_month(
        cur: mysql.connector.cursor.MySQLCursorDict
) -> None:
    '''Delete all records from Schools_Problems_Solved_Per_Month'''
    logging.info("Deleting existing problems solved per month records")
    cur.execute('DELETE FROM `Schools_Problems_Solved_Per_Month`')


def get_current_problems_solved_per_month(
        cur_readonly: mysql.connector.cursor.MySQLCursorDict,
        months: int
) -> List[ProblemSolved]:
    '''Gets current problems solved per month'''
    logging.info("Getting current problems solved per month")
    sql = '''
        SELECT
        `sc`.`school_id`,
        DATE_FORMAT(`su`.`time`, '%Y-%m-01') AS `time`,
        COUNT(DISTINCT `su`.`problem_id`) AS `problems_solved`
        FROM
            `Submissions` AS `su`
        INNER JOIN
            `Runs` AS `r` ON `r`.`run_id` = `su`.`current_run_id`
        INNER JOIN
            `Schools` AS `sc` ON `sc`.`school_id` = `su`.`school_id`
        INNER JOIN
            `Problems` AS `p` ON `p`.`problem_id` = `su`.`problem_id`
        INNER JOIN
            (
                SELECT
                    `s2`.`identity_id`,
                    `s2`.`problem_id`,
                    MIN(`s2`.`time`) AS `first_ac_time`
                FROM
                    `Submissions` AS `s2`
                INNER JOIN
                    `Runs` AS `r2` ON `r2`.`run_id` = `s2`.`current_run_id`
                WHERE
                    `s2`.`time` >= CURDATE() - INTERVAL %(months)s MONTH
                    AND `r2`.`verdict` = 'AC'
                GROUP BY
                    `s2`.`identity_id`,
                    `s2`.`problem_id`
            ) AS `first_ac`
        ON
            `first_ac`.`identity_id` = `su`.`identity_id`
            AND `first_ac`.`problem_id` = `su`.`problem_id`
            AND `first_ac`.`first_ac_time` = `su`.`time`
        WHERE
            `su`.`time` >= CURDATE() - INTERVAL %(months)s MONTH
            AND `r`.`verdict` = 'AC'
            AND `p`.`visibility` >= 1
        GROUP BY
            `sc`.`school_id`,
            `time`
        ORDER BY
            `time` ASC
    '''
    cur_readonly.execute('EXPLAIN ' + sql, {'months': months})
    for row in cur_readonly.fetchall():
        logging.info(
            "[get_current_problems_solved_per_month] EXPLAIN id=%s "
            "table=%s type=%s key=%s rows=%s Extra=%s",
            row.get('id'), row.get('table'), row.get('type'), row.get(
                'key'), row.get('rows'), row.get('Extra')
        )

    cur_readonly.execute(sql, {'months': months})
    problems: List[ProblemSolved] = []
    for row in cur_readonly.fetchall():
        problems.append(
            ProblemSolved(
                school_id=row['school_id'],
                time=row['time'],
                problems_solved=row['problems_solved'],
            )
        )
    logging.info(
        "Evaluated [get_current_problems_solved_per_month] "
        "for %d problems", len(problems))
    return problems


def insert_updated_problems_solved_per_month(
        cur: mysql.connector.cursor.MySQLCursorDict,
        problems: List[ProblemSolved]
) -> None:
    '''
    Insert updated problems solved per month
    '''
    logging.info("Inserting updated problems solved per month")

    values = [
        (problem.school_id, problem.time, problem.problems_solved)
        for problem in problems
    ]

    cur.executemany(
        '''
        INSERT INTO
            `Schools_Problems_Solved_Per_Month` (
                `school_id`,
                `time`,
                `problems_solved`
            )
        VALUES (
            %s,
            %s,
            %s
        );
        ''', values)

    logging.info("Successfully inserted updated problems solved per month")


def get_school_of_the_month_candidates(
        cur_readonly: mysql.connector.cursor.MySQLCursorDict,
        first_day_of_next_month: datetime.date,
        first_day_of_current_month: datetime.date
) -> List[School]:
    '''Returns a list of elegible schools of the month candidates'''
    logging.info("Getting school of the month candidates")
    sql = '''
            SELECT
                s.school_id,
                s.name,
                IFNULL(SUM(ROUND(100 / LOG(2, p.accepted + 1), 0)), 0.0)
                AS score
            FROM Submissions AS su
            JOIN Problems AS p
                ON p.problem_id = su.problem_id
                AND p.visibility >= 1
                AND p.quality_seal = 1
            JOIN Schools AS s
                ON s.school_id = su.school_id
            JOIN Identities AS i
                ON i.identity_id = su.identity_id
            JOIN Users AS u
                ON u.user_id = i.user_id
                AND u.main_email_id IS NOT NULL
            LEFT JOIN (
                SELECT DISTINCT school_id
                FROM School_Of_The_Month
                WHERE (selected_by IS NOT NULL OR ranking = 1)
                    AND time >= DATE_SUB(%s, INTERVAL 1 YEAR)
            ) AS recent_winners
                ON recent_winners.school_id = su.school_id
            LEFT JOIN Problems_Forfeited AS pf
                ON pf.user_id = i.user_id
                AND pf.problem_id = su.problem_id
            WHERE
                su.verdict = 'AC'
                AND su.type = 'normal'
                AND su.time BETWEEN %s AND %s
                AND su.school_id IS NOT NULL
                AND i.user_id IS NOT NULL
                -- Exclude site-admins (acl_id = 1 is SYSTEM_ACL,
                -- role_id = 1 is ADMIN_ROLE)
                -- TODO: Replace magic numbers with constants
                AND i.user_id NOT IN (
                    SELECT ur.user_id
                    FROM User_Roles AS ur
                    WHERE ur.acl_id = 1 AND ur.role_id = 1
                )
                -- Exclude problems where the identity is admin/owner
                AND NOT EXISTS (
                    -- problem owner
                    SELECT 1 FROM ACLs AS a
                    WHERE a.acl_id = p.acl_id AND a.owner_id = i.user_id
                    UNION
                    -- direct problem admin (role_id = 1 is ADMIN_ROLE)
                    SELECT 1 FROM User_Roles AS ur
                    WHERE ur.acl_id = p.acl_id AND ur.role_id = 1
                        AND ur.user_id = i.user_id
                    UNION
                    -- group problem admin (role_id = 1 is ADMIN_ROLE)
                    SELECT 1
                    FROM Group_Roles AS gr
                    INNER JOIN Groups_Identities AS gi
                        ON gi.group_id = gr.group_id
                    WHERE gr.acl_id = p.acl_id AND gr.role_id = 1
                        AND gi.identity_id = su.identity_id
                )
                AND recent_winners.school_id IS NULL
                AND pf.problem_id IS NULL
                AND NOT EXISTS (
                    SELECT 1
                    FROM Submissions AS su_prev
                    WHERE
                        su_prev.school_id = su.school_id
                        AND su_prev.problem_id = su.problem_id
                        AND su_prev.verdict = 'AC'
                        AND su_prev.type = 'normal'
                        AND (
                            su_prev.time < su.time OR
                            (su_prev.time = su.time AND
                            su_prev.submission_id < su.submission_id)
                        )
                )
            GROUP BY s.school_id
            ORDER BY score DESC
            LIMIT 100;
    '''

    cur_readonly.execute('EXPLAIN ' + sql, (first_day_of_next_month,
                                            first_day_of_current_month,
                                            first_day_of_next_month))

    for row in cur_readonly.fetchall():
        logging.info(
            "[get_school_of_the_month_candidates] EXPLAIN "
            "id=%s table=%s type=%s key=%s rows=%s Extra=%s",
            row.get('id'), row.get('table'), row.get('type'), row.get(
                'key'), row.get('rows'), row.get('Extra')
        )

    cur_readonly.execute(
        sql, (first_day_of_next_month, first_day_of_current_month,
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
    logging.info(
        "Evaluated [get_school_of_the_month_candidates] "
        "for %d schools", len(candidates))
    return candidates


def insert_school_of_the_month_candidates(
    cur: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_next_month: datetime.date,
    candidates: list[School],
) -> None:
    '''Insert school of the month candidates'''
    logging.info("Inserting school of the month candidates")
    if not candidates:
        return

    rows = [
        (row.school_id, first_day_of_next_month, index + 1, row.score)
        for index, row in enumerate(candidates)
    ]

    cur.executemany(
        '''
        INSERT INTO
            `School_Of_The_Month` (
                `school_id`,
                `time`,
                `ranking`,
                `score`
            )
        VALUES(
            %s,
            %s,
            %s,
            %s
        );
        ''', rows)


def get_last_12_schools_of_the_month(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date) -> List[School]:
    '''Get last 12 school of the month winners'''
    logging.info("Getting last 12 school of the month winners")
    sql = '''
        SELECT
            `sotm`.`school_id`,
            `sch`.`name`,
            `sotm`.`score`
        FROM
            `School_Of_The_Month` AS `sotm`
        INNER JOIN
            `Schools` AS `sch` ON `sch`.`school_id` = `sotm`.`school_id`
        WHERE
            (
                `sotm`.`selected_by` IS NOT NULL
                OR (
                    `sotm`.`ranking` = 1 AND
                    NOT EXISTS (
                        SELECT
                            *
                        FROM
                            `School_Of_The_Month`
                        WHERE
                            `time` = `sotm`.`time` AND
                            `selected_by` IS NOT NULL
                    )
                )
            )
            AND `sotm`.`time` <= %s
            AND `sotm`.`time` > DATE_SUB(%s, INTERVAL 12 MONTH)
        ORDER BY
            `sotm`.`time` DESC
        LIMIT
            0, 12;
    '''
    cur_readonly.execute(sql, (first_day_of_current_month,
                               first_day_of_current_month))
    schools: List[School] = []
    for row in cur_readonly.fetchall():
        schools.append(
            School(
                school_id=row['school_id'],
                name=row['name'],
                score=row['score'],
            )
        )
    return schools


def get_candidate_schools_list(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    first_day_of_next_month: datetime.date
) -> List[School]:
    '''
    Returns a list of candidate schools before calculating scores and
    filtering out the last 12 recent winners.
    '''
    logging.info("Getting candidate schools list")
    sql = '''
        SELECT
            `s`.`school_id`,
            `s`.`name`
        FROM
            `Schools` AS `s`
        INNER JOIN (
            SELECT DISTINCT
                `su`.`school_id`
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
                AND `su`.`time` BETWEEN %s AND %s
        ) AS `eligible_schools`
            ON `eligible_schools`.`school_id` = `s`.`school_id`
        ORDER BY
            `s`.`school_id`;
    '''
    cur_readonly.execute(
        sql, (first_day_of_current_month, first_day_of_next_month))
    candidates: List[School] = []
    for row in cur_readonly.fetchall():
        candidates.append(
            School(
                school_id=row['school_id'],
                name=row['name'],
                score=0.0,  # Score will be calculated in python
            )
        )
    return candidates
