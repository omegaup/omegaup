#!/usr/bin/env python3
'''Detects problems that silently stopped working.

Findings are recorded in `Problem_Health_Checks`, upserted so each one keeps
the date it was first detected, and resolved once it stops being detected.
'''

import argparse
import logging
import os
import sys

from typing import List, NamedTuple, Sequence

import mysql.connector.cursor

sys.path.insert(0,
                os.path.dirname(os.path.dirname(os.path.realpath(__file__))))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position
import lib.runner  # pylint: disable=wrong-import-position

_MIN_SUBMISSIONS_NEVER_SOLVED = 20
_MIN_JUDGE_ERRORS = 5
_JUDGE_ERROR_SAMPLE = 50


class Finding(NamedTuple):
    '''One detected problem.'''
    problem_id: int
    check_type: str
    severity: str
    detail: str


def find_judge_errors(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict,
        min_errors: int = _MIN_JUDGE_ERRORS,
        sample: int = _JUDGE_ERROR_SAMPLE,
) -> List[Finding]:
    '''Finds problems whose recent runs keep failing to be judged.'''
    cur.execute(
        '''
        SELECT
            s.`problem_id`,
            COUNT(*) AS `error_count`
        FROM (
            SELECT
                s.`problem_id`,
                r.`verdict`
            FROM `Submissions` s
            INNER JOIN `Runs` r ON r.`run_id` = s.`current_run_id`
            ORDER BY s.`submission_id` DESC
            LIMIT %s
        ) AS s
        WHERE s.`verdict` IN ('JE', 'VE')
        GROUP BY s.`problem_id`
        HAVING `error_count` >= %s;''',
        (sample, min_errors))
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type='judge_errors',
            severity='error',
            detail=(f'{int(row["error_count"])} of the last {sample} runs '
                    f'failed to be judged'),
        ) for row in cur.fetchall()
    ]


def find_problems_without_languages(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict) -> List[Finding]:
    '''Finds public problems that have no enabled language.'''
    cur.execute(
        '''
        SELECT
            p.`problem_id`
        FROM `Problems` p
        WHERE
            p.`visibility` >= 1 AND
            p.`deprecated` = 0 AND
            (p.`languages` = '' OR NOT EXISTS (
                SELECT 1
                FROM `Problems_Languages` pl
                WHERE pl.`problem_id` = p.`problem_id`
            ));''')
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type='no_languages',
            severity='error',
            detail='the problem is public but has no enabled language',
        ) for row in cur.fetchall()
    ]


def find_never_solved(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict,
        min_submissions: int = _MIN_SUBMISSIONS_NEVER_SOLVED,
) -> List[Finding]:
    '''Finds public problems many people tried and nobody ever solved.'''
    cur.execute(
        '''
        SELECT
            p.`problem_id`,
            p.`submissions`
        FROM `Problems` p
        WHERE
            p.`visibility` >= 1 AND
            p.`deprecated` = 0 AND
            p.`accepted` = 0 AND
            p.`submissions` >= %s;''',
        (min_submissions,))
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type='never_solved',
            severity='warning',
            detail=(f'{int(row["submissions"])} submissions and no accepted '
                    f'solution yet'),
        ) for row in cur.fetchall()
    ]


def find_deprecated_but_public(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict) -> List[Finding]:
    '''Finds problems marked deprecated that are still offered to users.'''
    cur.execute(
        '''
        SELECT
            p.`problem_id`
        FROM `Problems` p
        WHERE
            p.`deprecated` = 1 AND
            p.`visibility` >= 1;''')
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type='deprecated_public',
            severity='warning',
            detail='the problem is deprecated but still public',
        ) for row in cur.fetchall()
    ]


def record_findings(
        dbconn: lib.db.Connection,
        findings: Sequence[Finding],
        run_timestamp: str,
) -> None:
    '''Stores the findings, keeping the date each one was first detected.'''
    with dbconn.cursor() as cur:
        for finding in findings:
            cur.execute(
                '''
                INSERT INTO `Problem_Health_Checks`
                    (`problem_id`, `check_type`, `severity`, `detail`,
                     `last_seen_at`)
                VALUES (%s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    `severity` = VALUES(`severity`),
                    `detail` = VALUES(`detail`),
                    `last_seen_at` = VALUES(`last_seen_at`),
                    `resolved_at` = NULL;''',
                (finding.problem_id, finding.check_type, finding.severity,
                 finding.detail, run_timestamp))
    dbconn.conn.commit()


def resolve_missing_findings(
        dbconn: lib.db.Connection,
        run_timestamp: str,
) -> int:
    '''Marks as resolved the findings that were not detected in this run.'''
    with dbconn.cursor(buffered=True, dictionary=True) as cur:
        cur.execute(
            '''
            UPDATE `Problem_Health_Checks`
            SET `resolved_at` = %s
            WHERE `resolved_at` IS NULL AND `last_seen_at` < %s;''',
            (run_timestamp, run_timestamp))
        cur.execute('SELECT ROW_COUNT() AS `resolved`;')
        row = cur.fetchone()
        resolved = int(row['resolved']) if row else 0
    dbconn.conn.commit()
    return resolved


def current_timestamp(dbconn: lib.db.Connection) -> str:
    '''Returns the database clock, shared by every row of one run.'''
    with dbconn.cursor() as cur:
        cur.execute('SELECT NOW();')
        row = cur.fetchone()
    return str(row[0]) if row else ''


def build_parser() -> argparse.ArgumentParser:
    '''Returns an argparse.ArgumentParser for this tool.'''
    parser = argparse.ArgumentParser(
        description='Detects problems that silently stopped working.')
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    lib.runner.configure_parser(parser)

    thresholds = parser.add_argument_group('Thresholds')
    thresholds.add_argument('--min-submissions-never-solved',
                            type=int,
                            default=_MIN_SUBMISSIONS_NEVER_SOLVED,
                            help='How many submissions a problem needs before '
                            'never having been solved counts as a finding.')
    thresholds.add_argument('--min-judge-errors',
                            type=int,
                            default=_MIN_JUDGE_ERRORS,
                            help='How many judge or validator errors in the '
                            'recent runs count as a finding.')
    thresholds.add_argument('--judge-error-sample',
                            type=int,
                            default=_JUDGE_ERROR_SAMPLE,
                            help='How many recent runs to look at when '
                            'counting judge errors.')
    return parser


def main() -> None:
    '''Main entrypoint.'''
    parser = build_parser()
    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    with lib.runner.run(parser.prog, args) as cron_run:
        dbconn = lib.db.connect(
            lib.db.DatabaseConnectionArguments.from_args(args))
        try:
            findings: List[Finding] = []
            with dbconn.cursor(buffered=True, dictionary=True) as cur:
                with cron_run.phase('judge_errors'):
                    findings.extend(
                        find_judge_errors(cur, args.min_judge_errors,
                                          args.judge_error_sample))
                with cron_run.phase('no_languages'):
                    findings.extend(find_problems_without_languages(cur))
                with cron_run.phase('never_solved'):
                    findings.extend(
                        find_never_solved(cur,
                                          args.min_submissions_never_solved))
                with cron_run.phase('deprecated_public'):
                    findings.extend(find_deprecated_but_public(cur))

            with cron_run.phase('record_findings'):
                run_timestamp = current_timestamp(dbconn)
                record_findings(dbconn, findings, run_timestamp)
                resolved = resolve_missing_findings(dbconn, run_timestamp)

            cron_run.set_rows_affected(len(findings))
            logging.info('Found %d problems needing attention, resolved %d',
                         len(findings), resolved)
        finally:
            dbconn.conn.close()
            logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
