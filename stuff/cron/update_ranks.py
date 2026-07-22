#!/usr/bin/env python3
'''Updates the user ranking.'''

# pylint: disable=too-many-lines

import argparse
import datetime
import json
import logging
import os
import sys
import time
from typing import List, NamedTuple, Sequence, Dict, Set, Optional


class UserRankRow(NamedTuple):
    '''Represents a row for User_Rank inserts.'''

    user_id: int
    ranking: int
    problems_solved_count: int
    score: float
    username: str
    name: str
    country_id: Optional[str]
    state_id: Optional[str]
    school_id: Optional[int]


class AuthorRankRow(NamedTuple):
    '''Represents a row for author ranking inserts.'''

    user_id: int
    username: str
    author_score: float
    author_ranking: int
    name: str
    country_id: Optional[str]
    state_id: Optional[str]
    school_id: Optional[int]


import mysql.connector
import mysql.connector.cursor

from database.coder_of_the_month import check_existing_coder_of_the_month
from database.coder_of_the_month import get_cotm_eligible_users
from database.coder_of_the_month import get_eligible_problems
from database.coder_of_the_month import get_last_12_coders_of_the_month
from database.coder_of_the_month import get_user_problems
from database.coder_of_the_month import remove_coder_of_the_month_candidates
from database.coder_of_the_month import insert_coder_of_the_month_candidates
from database.school_of_the_month import (
    check_existing_school_of_the_next_month,
    remove_school_of_the_month_candidates,
    get_school_of_the_month_candidates,
    insert_school_of_the_month_candidates,
    School,
    delete_problems_solved_per_month,
    get_current_problems_solved_per_month,
    insert_updated_problems_solved_per_month,
    get_last_12_schools_of_the_month,
    get_candidate_schools_list,
)
from utils import (
    UserRank,
    get_first_day_of_next_month,
)

sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position
from cron.constants import (  # pylint: disable=wrong-import-position
    SYSTEM_ACL,
    ADMIN_ROLE,
)


class Cutoff(NamedTuple):
    '''Cutoff percentile for user ranking.'''
    percentile: float
    classname: str


def _default_date() -> datetime.date:
    today = datetime.date.today()
    return today.replace(day=1)


def _parse_date(s: str) -> datetime.date:
    today = datetime.datetime.strptime(s, '%Y-%m-%d').date()
    return today.replace(day=1)


def update_problem_accepted_stats(
    cur: mysql.connector.cursor.MySQLCursorDict,
    dbconn: mysql.connector.MySQLConnection,
) -> None:
    '''Updates the problem accepted stats'''

    logging.info('Updating accepted stats for problems...')
    cur.execute(
        '''
        UPDATE
            `Problems` AS `p`
        LEFT JOIN (
            SELECT
                `s`.`problem_id`,
                COUNT(DISTINCT `s`.`identity_id`) AS `accepted`
            FROM
                `Submissions` AS `s`
            INNER JOIN
                `Identities` AS `i` ON
                `i`.`identity_id` = `s`.`identity_id`
            INNER JOIN
                `Problems` AS `inner_p` ON
                `inner_p`.`problem_id` = `s`.`problem_id`
            WHERE
                `s`.`verdict` = 'AC'
                AND NOT EXISTS (
                    SELECT
                        `pf`.`problem_id`, `pf`.`user_id`
                    FROM
                        `Problems_Forfeited` AS `pf`
                    WHERE
                        `pf`.`problem_id` = `inner_p`.`problem_id` AND
                        `pf`.`user_id` = `i`.`user_id`
                )
                AND NOT EXISTS (
                    SELECT
                        `a`.`acl_id`
                    FROM
                        `ACLs` AS `a`
                    WHERE
                        `a`.`acl_id` = `inner_p`.`acl_id` AND
                        `a`.`owner_id` = `i`.`user_id`
                )
            GROUP BY
                `s`.`problem_id`
        ) AS `stats`
        ON
            `stats`.`problem_id` = `p`.`problem_id`
        SET
            `p`.`accepted` = IFNULL(`stats`.`accepted`, 0);
        ''')
    dbconn.commit()


def update_user_rank(
    cur: mysql.connector.cursor.MySQLCursorDict,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
) -> Sequence[float]:
    '''Updates the user ranking.'''

    logging.info('Updating user rank...')
    cur_readonly.execute('''
        SELECT
            `i`.`username`,
            `i`.`name`,
            `i`.`country_id`,
            `i`.`state_id`,
            `full_isc`.`school_id`,
            `i`.`identity_id`,
            `i`.`user_id`,
            COUNT(`p`.`problem_id`) AS `problems_solved_count`,
            SUM(ROUND(100 / LOG(2, `p`.`accepted` + 1) , 0)) AS `score`
        FROM
        (
            SELECT
                `iu`.`user_id`,
                `s`.`problem_id`
            FROM
                `Submissions` AS `s`
            INNER JOIN
                `Identities` AS `iu`
            ON
                `iu`.identity_id = `s`.identity_id
            WHERE
                `s`.verdict = 'AC' AND
                `s`.type = 'normal' AND
                `iu`.user_id IS NOT NULL
            GROUP BY
                `iu`.user_id, `s`.`problem_id`
        ) AS up
        INNER JOIN
            `Users` AS `full_u` ON `full_u`.`user_id` = `up`.`user_id`
        INNER JOIN
            `Problems` AS `p`
        ON `p`.`problem_id` = up.`problem_id` AND `p`.visibility > 0
        INNER JOIN
            `Identities` AS `i`
                ON `i`.`identity_id` = `full_u`.`main_identity_id`
        LEFT JOIN
            `Identities_Schools` AS `full_isc`
        ON
            `full_isc`.`identity_school_id` = `i`.`current_identity_school_id`
        WHERE
            `full_u`.`is_private` = 0
            -- Exclude site-admins (SYSTEM_ACL / ADMIN_ROLE).
            AND `full_u`.`user_id` NOT IN (
                SELECT
                    `ur`.`user_id`
                FROM
                    `User_Roles` AS `ur`
                WHERE
                    `ur`.`acl_id` = %s AND
                    `ur`.`role_id` = %s
            )
            AND NOT EXISTS (
                SELECT
                    `pf`.`problem_id`, `pf`.`user_id`
                FROM
                    `Problems_Forfeited` AS `pf`
                WHERE
                    `pf`.`problem_id` = `p`.`problem_id` AND
                    `pf`.`user_id` = `full_u`.`user_id`
            )
            AND NOT EXISTS (
                SELECT
                    `a`.`acl_id`
                FROM
                    `ACLs` AS `a`
                WHERE
                    `a`.`acl_id` = `p`.`acl_id` AND
                    `a`.`owner_id` = `full_u`.`user_id`
            )
        GROUP BY
            `identity_id`
        ORDER BY
            `score` DESC;
    ''', (SYSTEM_ACL, ADMIN_ROLE))
    prev_score = None
    rank = 0
    # MySQL has no good way of obtaining percentiles, so we'll store the sorted
    # list of scores in order to calculate the cutoff scores later.
    scores: List[float] = []
    cur.execute('DELETE FROM `User_Rank`;')
    insert_user_rank_sql = '''
                    INSERT INTO
                        `User_Rank` (`user_id`, `ranking`,
                                     `problems_solved_count`, `score`,
                                     `username`, `name`, `country_id`,
                                     `state_id`, `school_id`)
                    VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s);'''
    user_rank_rows: List[UserRankRow] = []
    batch_size = 1000
    for index, row in enumerate(cur_readonly):
        if row['score'] != prev_score:
            rank = index + 1
        score = row.get('score', 0)
        scores.append(score)
        prev_score = score
        user_rank_rows.append(
            UserRankRow(
                user_id=row['user_id'],
                ranking=rank,
                problems_solved_count=row['problems_solved_count'],
                score=score,
                username=row['username'],
                name=row['name'],
                country_id=row['country_id'],
                state_id=row['state_id'],
                school_id=row['school_id'],
            ))
        if len(user_rank_rows) >= batch_size:
            cur.executemany(insert_user_rank_sql, user_rank_rows)
            user_rank_rows.clear()
    if user_rank_rows:
        cur.executemany(insert_user_rank_sql, user_rank_rows)
    return scores


def update_author_rank(
    cur: mysql.connector.cursor.MySQLCursorDict,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
) -> None:
    '''Updates the author's ranking'''
    logging.info('Updating authors ranking...')
    cur_readonly.execute('''
        SELECT
            `u`.`user_id`,
            `i`.`username`,
            `i`.`name`,
            `i`.`country_id`,
            `i`.`state_id`,
            `isc`.`school_id`,
            SUM(`full_p`.`quality`) AS `author_score`
        FROM
            `Problems` AS `full_p`
        INNER JOIN
            `ACLs` AS `a` ON `a`.`acl_id` = `full_p`.`acl_id`
        INNER JOIN
            `Users` AS `u` ON `u`.`user_id` = `a`.`owner_id`
        INNER JOIN
            `Identities` AS `i` ON `i`.`identity_id` = `u`.`main_identity_id`
        LEFT JOIN
            `Identities_Schools` AS `isc`
        ON
            `isc`.`identity_school_id` = `i`.`current_identity_school_id`
        WHERE
            `full_p`.`quality` IS NOT NULL
            -- Exclude site-admins (SYSTEM_ACL / ADMIN_ROLE).
            AND `u`.`user_id` NOT IN (
                SELECT
                    `ur`.`user_id`
                FROM
                    `User_Roles` AS `ur`
                WHERE
                    `ur`.`acl_id` = %s AND
                    `ur`.`role_id` = %s
            )
        GROUP BY
            `u`.`user_id`
        ORDER BY
            `author_score` DESC
    ''', (SYSTEM_ACL, ADMIN_ROLE))

    prev_score = None
    rank = 0
    insert_author_rank_sql = '''
                    INSERT INTO
                        `User_Rank` (`user_id`, `username`, `author_score`,
                                     `author_ranking`, `name`, `country_id`,
                                     `state_id`, `school_id`)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    ON DUPLICATE KEY
                        UPDATE
                            author_ranking = VALUES(author_ranking),
                            author_score = VALUES(author_score);'''
    author_rank_rows: List[AuthorRankRow] = []
    batch_size = 1000
    for index, row in enumerate(cur_readonly):
        if row['author_score'] != prev_score:
            rank = index + 1
        prev_score = row['author_score']
        author_rank_rows.append(
            AuthorRankRow(
                user_id=row['user_id'],
                username=row['username'],
                author_score=row['author_score'],
                author_ranking=rank,
                name=row['name'],
                country_id=row['country_id'],
                state_id=row['state_id'],
                school_id=row['school_id'],
            ))
        if len(author_rank_rows) >= batch_size:
            cur.executemany(insert_author_rank_sql, author_rank_rows)
            author_rank_rows.clear()
    if author_rank_rows:
        cur.executemany(insert_author_rank_sql, author_rank_rows)


def update_user_rank_cutoffs(cur: mysql.connector.cursor.MySQLCursorDict,
                             scores: Sequence[float]) -> None:
    '''Updates the user ranking cutoff table.'''

    cur.execute('DELETE FROM `User_Rank_Cutoffs`;')
    logging.info('Updating ranking cutoffs...')
    cutoffs = [
        Cutoff(.01, 'user-rank-international-master'),
        Cutoff(.09, 'user-rank-master'),
        Cutoff(.15, 'user-rank-expert'),
        Cutoff(.35, 'user-rank-specialist'),
        Cutoff(.40, 'user-rank-beginner'),
    ]
    if not scores:
        return
    for cutoff in cutoffs:
        # Scores are already in descending order. That will also bias the
        # cutoffs towards higher scores.
        cur.execute(
            '''
                    INSERT INTO
                        `User_Rank_Cutoffs` (`score`, `percentile`,
                                             `classname`)
                    VALUES(%s, %s, %s);''',
            (scores[int(len(scores) * cutoff.percentile)], cutoff.percentile,
             cutoff.classname))


def update_user_rank_classname(
        cur: mysql.connector.cursor.MySQLCursorDict) -> None:
    '''Updates the user ranking classname.

    This requires having updated both user scores and rank cutoffs.'''
    cur.execute('''
    UPDATE User_Rank ur
    SET classname = (
        SELECT
                IFNULL(
                    (
                        SELECT
                            urc.classname
                        FROM
                            User_Rank_Cutoffs urc
                        WHERE
                            urc.score <= ur.score
                        ORDER BY
                            urc.percentile ASC
                        LIMIT
                            1
                    ),
                    'user-rank-unranked'
                )
        );
    ''')


def update_school_rank(cur: mysql.connector.cursor.MySQLCursorDict) -> None:
    '''Updates the school rank'''

    logging.info('Updating school rank...')
    cur.execute('''
        SELECT
            `s`.`school_id`,
            SUM(ROUND(100 / LOG(2, `distinct_school_problems`.accepted+1), 0))
            AS `score`
        FROM
            `Schools` AS `s`
        INNER JOIN
            (
                SELECT
                    `su`.`school_id`,
                    `p`.accepted,
                    MIN(`su`.time)
                FROM
                    `Submissions` AS `su`
                INNER JOIN
                    `Problems` AS `p` ON `p`.`problem_id` = `su`.`problem_id`
                WHERE
                    `su`.verdict = "AC"
                    AND `p`.visibility >= 1
                    AND `su`.`school_id` IS NOT NULL
                GROUP BY
                    `su`.`school_id`,
                    `su`.`problem_id`
            ) AS `distinct_school_problems`
        ON
            `distinct_school_problems`.`school_id` = `s`.`school_id`
        GROUP BY
            `s`.`school_id`
        ORDER BY
            `score` DESC;
    ''')
    prev_score = None
    rank = 0

    for index, row in enumerate(cur.fetchall()):
        if row['score'] != prev_score:
            rank = index + 1
        prev_score = row['score']
        cur.execute(
            '''
                UPDATE
                    `Schools` AS `s`
                SET
                    `s`.`score` = %s,
                    `s`.`ranking` = %s
                WHERE
                    `s`.`school_id` = %s;
            ''', (row['score'], rank, row['school_id']))


def update_school_of_the_month_candidates(
    cur: mysql.connector.cursor.MySQLCursorDict,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    update_school_of_the_month: bool,
) -> None:
    '''Updates the list of candidates to school of the current month'''
    logging.info('Updating the candidates to school of the month...')
    first_day_of_next_month = get_first_day_of_next_month(
        first_day_of_current_month)
    exist_school = check_existing_school_of_the_next_month(
        cur_readonly, first_day_of_next_month)
    if exist_school:
        logging.info('Skipping because already exist selected schools.')
        return
    remove_school_of_the_month_candidates(cur, first_day_of_next_month)

    schools_sql = get_school_of_the_month_candidates(
        cur_readonly,
        first_day_of_next_month,
        first_day_of_current_month
    )

    if update_school_of_the_month:
        insert_school_of_the_month_candidates(
            cur, first_day_of_next_month, schools_sql)
    else:
        debug_school_of_the_month_candidates(
            first_day_of_next_month, schools_sql,
            use_json_format=True)


def debug_school_of_the_month_candidates(
    first_day_of_next_month: datetime.date,
    schools_sql: List[School],
    use_json_format: bool = True,
) -> None:
    '''Log school of the month candidates and their data'''

    if use_json_format:
        # JSON format for production (New Relic compatible)
        candidates_data = {
            "time": first_day_of_next_month.isoformat(),
            "count": len(schools_sql),
            "candidates": [
                {
                    "rank": i,
                    "school_id": s.school_id,
                    "name": s.name,
                    "score": round(s.score, 2)
                }
                for i, s in enumerate(schools_sql, 1)
            ],
        }
        logging.info(
            'School of the Month candidates: %s',
            json.dumps(candidates_data)
        )
    else:
        # Human-readable format for test environment
        logging.info('=' * 75)
        logging.info('SCHOOL OF THE MONTH - CANDIDATES')
        logging.info('=' * 75)
        logging.info('Date: %s', first_day_of_next_month.isoformat())
        logging.info('Total candidates: %d', len(schools_sql))
        logging.info('')
        logging.info(
            '%-6s %-12s %-40s %-8s',
            'Rank', 'SchoolId', 'Name', 'Score'
        )
        logging.info('-' * 75)
        for i, s in enumerate(schools_sql, 1):
            logging.info(
                '%-6d %-12d %-40s %-8.2f',
                i, s.school_id, s.name, s.score
            )
        logging.info('=' * 75)


def compute_points_for_school(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    first_day_of_next_month: datetime.date
) -> List[School]:
    '''Computes the points for each eligible school'''

    # Get the last 12 schools of the month winners
    last_12_schools = get_last_12_schools_of_the_month(
        cur_readonly,
        first_day_of_current_month
    )

    last_12_schools_ids = {school.school_id for school in last_12_schools}

    eligible_schools = get_candidate_schools_list(
        cur_readonly,
        first_day_of_current_month,
        first_day_of_next_month,
    )
    eligible_users = get_cotm_eligible_users(
        cur_readonly,
        first_day_of_current_month,
        first_day_of_next_month,
        'all',
        []
    )

    if last_12_schools_ids:
        eligible_schools = [
            s for s in eligible_schools
            if s.school_id not in last_12_schools_ids
        ]
        eligible_users = [
            u for u in eligible_users
            if u.school_id is None or u.school_id not in last_12_schools_ids
        ]

    # Debug
    if not eligible_schools:
        logging.info('No eligible schools founds.')
        return []

    eligible_problems = get_eligible_problems(cur_readonly)

    # Get the list of identity IDs for eligible users
    identity_ids = [user.identity_id for user in eligible_users]

    # Get the list of problem IDs for eligible problems
    problem_ids = list(eligible_problems.keys())

    if not identity_ids or not problem_ids:
        missing = 'users' if not identity_ids else 'problems'
        logging.info('No eligible %s founds.', missing)
        return []

    # Convert the list of identity IDs to a comma-separated string
    identity_ids_str = ', '.join(map(str, identity_ids))

    # Convert the list of problem IDs to a comma-separated string
    problem_ids_str = ', '.join(map(str, problem_ids))

    user_problems = get_user_problems(cur_readonly,
                                      identity_ids_str,
                                      problem_ids_str,
                                      eligible_users,
                                      first_day_of_current_month,
                                      )
    # Calculate the score for each school based on the problems solved by its
    # users
    for _, points in user_problems.items():
        # Iterate over each problem solved by the user to get the score and add
        # it to the total score
        for problem_id in points['solved']:
            points['score'] += eligible_problems[problem_id].score
    # Create a mapping from school_id to School object
    school_map: Dict[int, School] = {}
    for school in eligible_schools:
        school_map[school.school_id] = school
    # Group problems by the school the user belonged to when they solved them
    school_problems: Dict[int, Set[int]] = {}
    for user in eligible_users:
        for problem_id in user_problems[user.identity_id]['solved']:
            school_id = user_problems[user.identity_id][
                'school_at_solve'].get(problem_id)
            if school_id is not None and school_id in school_map:
                if school_id not in school_problems:
                    school_problems[school_id] = set()
                school_problems[school_id].add(problem_id)

    # Calculate score for each school based on unique problems solved
    for school_id, unique_problem_ids in school_problems.items():
        total_score = 0.0
        for problem_id in unique_problem_ids:
            total_score += eligible_problems[problem_id].score

        school = school_map[school_id]
        updated_school = school._replace(score=total_score)
        school_map[school_id] = updated_school

    # Create a list of updated schools with their scores
    eligible_schools = list(school_map.values())
    # Sort the updated schools by score in descending order
    eligible_schools_sorted = sorted(
        eligible_schools, key=lambda school: school.score, reverse=True)
    eligible_schools = eligible_schools_sorted

    return eligible_schools


def compute_points_for_user(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    first_day_of_current_month: datetime.date,
    first_day_of_next_month: datetime.date,
    category: str,
    coder_list_count: int,
) -> List[UserRank]:
    '''Computes the points for each eligible user'''

    last_12_coders = get_last_12_coders_of_the_month(
        cur_readonly,
        first_day_of_current_month,
        category
    )

    eligible_users = get_cotm_eligible_users(
        cur_readonly,
        first_day_of_current_month,
        first_day_of_next_month,
        category,
        last_12_coders
    )

    eligible_problems = get_eligible_problems(cur_readonly)

    # Get the list of identity IDs for eligible users
    identity_ids = [user.identity_id for user in eligible_users]

    # Get the list of problem IDs for eligible problems
    problem_ids = list(eligible_problems.keys())

    if not identity_ids:
        logging.info('No eligible users found in category [%s].', category)
        return []

    if not problem_ids:
        logging.info('No eligible problems found.')
        return []

    # Convert the list of identity IDs to a comma-separated string
    identity_ids_str = ', '.join(map(str, identity_ids))

    # Convert the list of problem IDs to a comma-separated string
    problem_ids_str = ', '.join(map(str, problem_ids))

    user_problems = get_user_problems(cur_readonly,
                                      identity_ids_str,
                                      problem_ids_str,
                                      eligible_users,
                                      first_day_of_current_month,
                                      )

    # Calculate the score for each user based on the problems they have solved
    for _, points in user_problems.items():
        # Iterate over each problem solved by the user to get the score and add
        # it to the total score
        for problem_id in points['solved']:
            points['score'] += eligible_problems[problem_id].score

    # Create a list of updated users with their scores and problems solved
    updated_users: List[UserRank] = []
    for user in eligible_users:
        updated_user = user._replace(
            score=user_problems[user.identity_id]['score'],
            problems_solved=len(
                user_problems[user.identity_id]['solved'])
        )
        updated_users.append(updated_user)
    # Sort the updated users by score in descending order
    updated_users_sorted = sorted(
        updated_users, key=lambda user: float(user.score), reverse=True)
    return updated_users_sorted[:coder_list_count]


def update_schools_solved_problems(
    cur: mysql.connector.cursor.MySQLCursorDict) -> None:
    '''Updates the solved problems count by each school the last 6 months'''

    logging.info('Updating schools solved problems...')
    delete_problems_solved_per_month(cur)
    problems = get_current_problems_solved_per_month(cur, 6)
    insert_updated_problems_solved_per_month(cur, problems)


def update_coder_of_the_month_candidates(
    cur: mysql.connector.cursor.MySQLCursorDict,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    category: str,
    args: argparse.Namespace,
) -> None:
    '''Updates the list of candidates to coder of the current month'''

    logging.info('Updating the candidates to coder of the month...')
    first_day_of_next_month = get_first_day_of_next_month(args.date)

    # First make sure there are not already selected coder of the month
    if check_existing_coder_of_the_month(cur_readonly,
                                         first_day_of_next_month,
                                         category):
        logging.info('Skipping because already exist selected coder')
        return

    remove_coder_of_the_month_candidates(cur, first_day_of_next_month,
                                         category)

    candidates = compute_points_for_user(cur_readonly,
                                         args.date,
                                         first_day_of_next_month,
                                         category,
                                         args.coders_list_count)

    for ranking, candidate in enumerate(candidates, start=1):
        insert_coder_of_the_month_candidates(cur, first_day_of_next_month,
                                             ranking, category, candidate)

    debug_coder_of_the_month_candidates(first_day_of_next_month, category,
                                        candidates)


def debug_coder_of_the_month_candidates(
    first_day_of_next_month: datetime.date,
    category: str,
    candidates: List[UserRank],
) -> None:
    '''Log coder of the month candidates'''

    log_entries = []
    for ranking, candidate in enumerate(candidates, start=1):
        log_entry = {
            "user_id": candidate.user_id,
            "username": candidate.username,
            "time": first_day_of_next_month.isoformat(),
            "ranking": ranking,
            "school_id": candidate.school_id,
            "category": category,
            "score": candidate.score,
            "problems_solved": candidate.problems_solved
        }
        log_entries.append(log_entry)

    log_message = json.dumps(log_entries, indent=4)
    logging.info(log_message)


def update_users_stats(
    cur: mysql.connector.cursor.MySQLCursorDict,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    dbconn: mysql.connector.MySQLConnection,
    args: argparse.Namespace,
) -> None:
    '''Updates all the information and ranks related to users'''
    logging.info('Updating users stats...')
    try:
        try:
            scores = update_user_rank(cur, cur_readonly)
            update_user_rank_cutoffs(cur, scores)
            update_user_rank_classname(cur)
        except:  # noqa: bare-except
            logging.exception('Failed to update user ranking')
            raise

        try:
            update_author_rank(cur, cur_readonly)
        except:  # noqa: bare-except
            logging.exception('Failed to update authors ranking')
            raise

        try:
            update_coder_of_the_month_candidates(cur, cur_readonly, 'all',
                                                 args)
        except:  # noqa: bare-except
            logging.exception(
                'Failed to update candidates to coder of the month')
            raise

        try:
            update_coder_of_the_month_candidates(cur, cur_readonly, 'female',
                                                 args)
        except:  # noqa: bare-except
            logging.exception(
                'Failed to update candidates to coder of the month female')
            raise

        # Commit all user stats and coder of the month updates atomically.
        dbconn.commit()
        logging.info('Users stats updated')
    except:  # noqa: bare-except
        logging.exception('Failed to update all users stats')
        dbconn.rollback()
        raise


def update_schools_stats(
    cur: mysql.connector.cursor.MySQLCursorDict,
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
    dbconn: mysql.connector.MySQLConnection,
    date: datetime.date,
    update_school_of_the_month: bool,
) -> None:
    '''Updates all the information and ranks related to schools'''
    logging.info('Updating schools stats...')
    try:
        try:
            update_schools_solved_problems(cur)
        except:  # noqa: bare-except
            logging.exception('Failed to update schools solved problems')
            raise

        try:
            update_school_rank(cur)
        except:  # noqa: bare-except
            logging.exception('Failed to update school ranking')
            raise

        try:
            update_school_of_the_month_candidates(cur, cur_readonly, date,
                                                  update_school_of_the_month)
        except:  # noqa: bare-except
            logging.exception(
                'Failed to update candidates to school of the month')
            raise

        # Commit all school stats updates automatically.
        dbconn.commit()
        logging.info('Schools stats updated')
    except:  # noqa: bare-except
        logging.exception('Failed to update all schools stats')
        dbconn.rollback()
        raise


def main() -> None:
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser(description=__doc__)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    parser.add_argument('--date',
                        type=_parse_date,
                        default=_default_date(),
                        help='The date the command should take as today')
    parser.add_argument('--coders-list-count',
                        type=int,
                        default=100,
                        help='The number of candidates to save in the DB')
    parser.add_argument('--update-school-of-the-month', action='store_true',
                        help='Update the School of the month')
    args: argparse.Namespace = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    start_time = time.monotonic()
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))
    dbconn_readonly = lib.db.connect_readonly(
        lib.db.DatabaseConnectionArguments.from_args_readonly(args)) or dbconn
    try:
        with dbconn.cursor(buffered=True,
                           dictionary=True) as cur, dbconn_readonly.cursor(
                               buffered=True, dictionary=True) as cur_readonly:
            phase_start = time.monotonic()
            update_problem_accepted_stats(cur, dbconn.conn)
            logging.info(
                'update_problem_accepted_stats completed in %.2fs',
                time.monotonic() - phase_start,
            )
            phase_start = time.monotonic()
            update_users_stats(cur, cur_readonly, dbconn.conn, args)
            logging.info(
                'update_users_stats completed in %.2fs',
                time.monotonic() - phase_start,
            )
            phase_start = time.monotonic()
            update_schools_stats(cur, cur_readonly, dbconn.conn, args.date,
                                 args.update_school_of_the_month)
            logging.info(
                'update_schools_stats completed in %.2fs',
                time.monotonic() - phase_start,
            )
    finally:
        dbconn.conn.close()
        logging.info(
            'Total execution time: %.2fs',
            time.monotonic() - start_time,
        )
        logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
