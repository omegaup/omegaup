#!/usr/bin/env python3

"""MySQL helpers for user and author ranking queries."""

from typing import Any, Dict, Iterator, Sequence, cast

import mysql.connector
import mysql.connector.cursor


_USER_RANK_SELECT_QUERY = '''
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
            -- Exclude site-admins (acl_id = 1 is SYSTEM_ACL,
            -- role_id = 1 is ADMIN_ROLE)
            -- TODO: Replace magic numbers with constants
            AND `full_u`.`user_id` NOT IN (
                SELECT
                    `ur`.`user_id`
                FROM
                    `User_Roles` AS `ur`
                WHERE
                    `ur`.`acl_id` = 1 AND
                    `ur`.`role_id` = 1
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
    '''

_USER_RANK_DELETE_QUERY = 'DELETE FROM `User_Rank`;'

_USER_RANK_INSERT_QUERY = '''
                    INSERT INTO
                        `User_Rank` (`user_id`, `ranking`,
                                     `problems_solved_count`, `score`,
                                     `username`, `name`, `country_id`,
                                     `state_id`, `school_id`)
                    VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s);'''

_AUTHOR_RANK_SELECT_QUERY = '''
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
            -- Exclude site-admins (acl_id = 1 is SYSTEM_ACL,
            -- role_id = 1 is ADMIN_ROLE)
            -- TODO: Replace magic numbers with constants
            AND `u`.`user_id` NOT IN (
                SELECT
                    `ur`.`user_id`
                FROM
                    `User_Roles` AS `ur`
                WHERE
                    `ur`.`acl_id` = 1 AND
                    `ur`.`role_id` = 1
            )
        GROUP BY
            `u`.`user_id`
        ORDER BY
            `author_score` DESC
    '''

_AUTHOR_RANK_INSERT_QUERY = '''
                    INSERT INTO
                        `User_Rank` (`user_id`, `username`, `author_score`,
                                     `author_ranking`, `name`, `country_id`,
                                     `state_id`, `school_id`)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    ON DUPLICATE KEY
                        UPDATE
                            author_ranking = VALUES(author_ranking),
                            author_score = VALUES(author_score);'''


def fetch_user_rank_rows(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
) -> Iterator[Dict[str, Any]]:
    '''Yield rows for the user rank calculation.'''

    cur_readonly.execute(_USER_RANK_SELECT_QUERY)
    for row in cur_readonly:
        yield cast(Dict[str, Any], row)


def clear_user_rank(
    cur: mysql.connector.cursor.MySQLCursorDict,
) -> None:
    '''Remove all rows from User_Rank.'''

    cur.execute(_USER_RANK_DELETE_QUERY)


def insert_user_rank_rows(
    cur: mysql.connector.cursor.MySQLCursorDict,
    rows: Sequence[Sequence[Any]],
) -> None:
    '''Insert user rank rows into User_Rank.'''

    cur.executemany(_USER_RANK_INSERT_QUERY, rows)


def fetch_author_rank_rows(
    cur_readonly: mysql.connector.cursor.MySQLCursorDict,
) -> Iterator[Dict[str, Any]]:
    '''Yield rows for the author rank calculation.'''

    cur_readonly.execute(_AUTHOR_RANK_SELECT_QUERY)
    for row in cur_readonly:
        yield cast(Dict[str, Any], row)


def upsert_author_rank_rows(
    cur: mysql.connector.cursor.MySQLCursorDict,
    rows: Sequence[Sequence[Any]],
) -> None:
    '''Upsert author rank rows into User_Rank.'''

    cur.executemany(_AUTHOR_RANK_INSERT_QUERY, rows)
