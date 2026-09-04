#!/usr/bin/env python3
'''Detects problems that silently stopped working.

Findings are recorded in `Problem_Health_Checks`, upserted so each one keeps
the date it was first detected, and resolved once it stops being detected.
'''

import argparse
import datetime
import logging
import os
import sys

import enum
from typing import List, NamedTuple, Sequence, cast

import mysql.connector.cursor

sys.path.insert(0,
                os.path.dirname(os.path.dirname(os.path.realpath(__file__))))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position
import lib.runner  # pylint: disable=wrong-import-position
from cron.constants import (  # pylint: disable=wrong-import-position
    PROBLEM_VISIBILITY_PUBLIC,
)

_JUDGE_ERROR_WINDOW_HOURS = 24
_MIN_JUDGE_ERRORS = 3
_MIN_JUDGE_ERROR_RATIO = 0.3
_MIN_SUBMISSIONS_NEVER_SOLVED = 20
_MIN_AGE_DAYS_NEVER_SOLVED = 7


class CheckType(enum.Enum):
    '''The checks that can flag a problem, as stored in `check_type`.'''
    JUDGE_ERRORS = 'judge_errors'
    NO_LANGUAGES = 'no_languages'
    NEVER_SOLVED = 'never_solved'
    DEPRECATED_PUBLIC = 'deprecated_public'


class Severity(enum.Enum):
    '''How bad a finding is, as stored in `severity`.'''
    WARNING = 'warning'
    ERROR = 'error'


class Finding(NamedTuple):
    '''One detected problem.'''
    problem_id: int
    check_type: CheckType
    severity: Severity
    detail: str


def find_judge_errors(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict,
        run_timestamp: datetime.datetime,
        window_hours: int = _JUDGE_ERROR_WINDOW_HOURS,
        min_errors: int = _MIN_JUDGE_ERRORS,
        min_ratio: float = _MIN_JUDGE_ERROR_RATIO,
) -> List[Finding]:
    '''Finds problems that are failing to judge the submissions they get.

    Only judged submissions count, on both sides of the ratio: one still
    waiting for a runner carries a verdict it was never given. Only `normal`
    ones count either, so a setter testing a broken validator against their
    own problem does not report it as broken for everybody else.

    Both thresholds must hold: enough failures to rule out a one-off, and
    enough of the problem's own traffic to rule out a flaky submission.
    '''
    cur.execute(
        '''
        SELECT
            `c`.`problem_id`,
            `c`.`error_count`,
            `c`.`submission_count`
        FROM (
            SELECT
                `s`.`problem_id`,
                SUM(`s`.`verdict` IN ('JE', 'VE')) AS `error_count`,
                COUNT(*) AS `submission_count`
            FROM `Submissions` `s`
            WHERE
                `s`.`time` >= %s AND
                `s`.`status` = 'ready' AND
                `s`.`type` = 'normal'
            GROUP BY `s`.`problem_id`
            HAVING
                `error_count` >= %s AND
                `error_count` >= %s * `submission_count`
        ) AS `c`
        INNER JOIN `Problems` `p` ON `p`.`problem_id` = `c`.`problem_id`
        WHERE
            `p`.`deprecated` = 0 AND
            `p`.`visibility` >= %s;''',
        (run_timestamp - datetime.timedelta(hours=window_hours), min_errors,
         min_ratio, PROBLEM_VISIBILITY_PUBLIC))
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type=CheckType.JUDGE_ERRORS,
            severity=Severity.ERROR,
            detail=(f'{int(row["error_count"])} of '
                    f'{int(row["submission_count"])} submissions in the last '
                    f'{window_hours}h ended in a judge or validator error'),
        ) for row in cur.fetchall()
    ]


def find_problems_without_languages(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict) -> List[Finding]:
    '''Finds public problems that have no enabled language.'''
    cur.execute(
        '''
        SELECT
            `p`.`problem_id`
        FROM `Problems` `p`
        WHERE
            `p`.`visibility` >= %s AND
            `p`.`deprecated` = 0 AND
            `p`.`languages` = '';''',
        (PROBLEM_VISIBILITY_PUBLIC,))
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type=CheckType.NO_LANGUAGES,
            severity=Severity.ERROR,
            detail='the problem is public but has no enabled language',
        ) for row in cur.fetchall()
    ]


def find_never_solved(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict,
        created_before: datetime.datetime,
        min_submissions: int = _MIN_SUBMISSIONS_NEVER_SOLVED,
) -> List[Finding]:
    '''Finds public problems many people tried and nobody ever solved.

    Problems younger than the cutoff are skipped: a hard problem published
    this week has simply not had the time to be solved yet.

    `Problems`.`submissions` counts every submission a problem ever took, of
    every type, so the attempts are counted again over `normal` ones only.
    The counter still narrows the candidates first, which it can do safely
    because it never undercounts them.
    '''
    cur.execute(
        '''
        SELECT
            `p`.`problem_id`,
            COUNT(*) AS `submission_count`
        FROM `Problems` `p`
        INNER JOIN `Submissions` `s` ON `s`.`problem_id` = `p`.`problem_id`
        WHERE
            `p`.`visibility` >= %s AND
            `p`.`deprecated` = 0 AND
            `p`.`languages` <> '' AND
            `p`.`accepted` = 0 AND
            `p`.`submissions` >= %s AND
            `p`.`creation_date` <= %s AND
            `s`.`type` = 'normal'
        GROUP BY `p`.`problem_id`
        HAVING `submission_count` >= %s;''',
        (PROBLEM_VISIBILITY_PUBLIC, min_submissions, created_before,
         min_submissions))
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type=CheckType.NEVER_SOLVED,
            severity=Severity.WARNING,
            detail=(f'{int(row["submission_count"])} submissions and no '
                    f'accepted solution yet'),
        ) for row in cur.fetchall()
    ]


def find_deprecated_but_public(
        cur: mysql.connector.cursor.MySQLCursorBufferedDict) -> List[Finding]:
    '''Finds problems marked deprecated that are still offered to users.'''
    cur.execute(
        '''
        SELECT
            `p`.`problem_id`
        FROM `Problems` `p`
        WHERE
            `p`.`deprecated` = 1 AND
            `p`.`visibility` >= %s;''',
        (PROBLEM_VISIBILITY_PUBLIC,))
    return [
        Finding(
            problem_id=int(row['problem_id']),
            check_type=CheckType.DEPRECATED_PUBLIC,
            severity=Severity.WARNING,
            detail='the problem is deprecated but still public',
        ) for row in cur.fetchall()
    ]


def record_findings(
        dbconn: lib.db.Connection,
        findings: Sequence[Finding],
        run_timestamp: datetime.datetime,
) -> None:
    '''Stores the findings, keeping the date each one was first detected.'''
    if not findings:
        return
    with dbconn.cursor() as cur:
        cur.executemany(
            '''
            INSERT INTO `Problem_Health_Checks`
                (`problem_id`, `check_type`, `severity`, `detail`,
                 `first_detected_at`, `last_seen_at`)
            VALUES (%s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
                -- Assigned before `resolved_at` is cleared, so it still
                -- sees whether the finding had been resolved.
                `first_detected_at` = CASE
                    WHEN `resolved_at` IS NULL THEN `first_detected_at`
                    ELSE VALUES(`last_seen_at`)
                END,
                `severity` = VALUES(`severity`),
                `detail` = VALUES(`detail`),
                `last_seen_at` = VALUES(`last_seen_at`),
                `resolved_at` = NULL;''',
            [(finding.problem_id, finding.check_type.value,
              finding.severity.value, finding.detail, run_timestamp,
              run_timestamp)
             for finding in findings])


def resolve_missing_findings(
        dbconn: lib.db.Connection,
        run_timestamp: datetime.datetime,
) -> int:
    '''Marks as resolved the findings that were not detected in this run.'''
    with dbconn.cursor() as cur:
        cur.execute(
            '''
            UPDATE `Problem_Health_Checks`
            SET `resolved_at` = %s
            WHERE `resolved_at` IS NULL AND `last_seen_at` < %s;''',
            (run_timestamp, run_timestamp))
        resolved = cur.rowcount
    return resolved


def current_timestamp(dbconn: lib.db.Connection) -> datetime.datetime:
    '''Returns the database clock, shared by every row of one run.'''
    with dbconn.cursor(buffered=True, dictionary=True) as cur:
        cur.execute('SELECT NOW() AS `run_timestamp`;')
        row = cur.fetchone()
    if not row:
        raise RuntimeError('The database did not return its clock')
    return cast(datetime.datetime, row['run_timestamp'])


def apply_findings(
        dbconn: lib.db.Connection,
        findings: Sequence[Finding],
        run_timestamp: datetime.datetime,
) -> int:
    '''Records this run's findings and closes the ones no longer detected.

    Both writes land together: on its own neither half describes the state of
    the site at `run_timestamp`.
    '''
    try:
        record_findings(dbconn, findings, run_timestamp)
        resolved = resolve_missing_findings(dbconn, run_timestamp)
        dbconn.conn.commit()
    except Exception:  # pylint: disable=broad-except
        logging.exception('Failed to store the findings')
        dbconn.conn.rollback()
        raise
    return resolved


def _positive_int(value: str) -> int:
    '''Parses a threshold that stops meaning anything below one.'''
    parsed = int(value)
    if parsed < 1:
        raise argparse.ArgumentTypeError(
            f'expected an integer greater than 0, got {parsed}')
    return parsed


def _non_negative_int(value: str) -> int:
    '''Parses a grace period, where zero is none rather than nonsense.'''
    parsed = int(value)
    if parsed < 0:
        raise argparse.ArgumentTypeError(
            f'expected an integer of 0 or more, got {parsed}')
    return parsed


def _ratio(value: str) -> float:
    '''Parses a fraction of a problem's submissions, in (0, 1].

    A whole one is the strictest a share can be asked to get and a problem
    can still reach it, but nothing above one is ever reachable, and zero
    asks for no share at all.
    '''
    parsed = float(value)
    if not 0 < parsed <= 1:
        raise argparse.ArgumentTypeError(
            f'expected a ratio greater than 0 and at most 1, got {parsed}')
    return parsed


def build_parser() -> argparse.ArgumentParser:
    '''Returns an argparse.ArgumentParser for this tool.'''
    parser = argparse.ArgumentParser(
        description='Detects problems that silently stopped working.')
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    lib.runner.configure_parser(parser)

    thresholds = parser.add_argument_group('Thresholds')
    thresholds.add_argument('--judge-error-window-hours',
                            type=_positive_int,
                            default=_JUDGE_ERROR_WINDOW_HOURS,
                            help='How far back to look for judge or '
                            'validator errors.')
    thresholds.add_argument('--min-judge-errors',
                            type=_positive_int,
                            default=_MIN_JUDGE_ERRORS,
                            help='How many judge or validator errors a '
                            'problem needs in the window to count as a '
                            'finding.')
    thresholds.add_argument('--min-judge-error-ratio',
                            type=_ratio,
                            default=_MIN_JUDGE_ERROR_RATIO,
                            help='Which fraction of a problem\'s submissions '
                            'in the window must have failed to be judged.')
    thresholds.add_argument('--min-submissions-never-solved',
                            type=_positive_int,
                            default=_MIN_SUBMISSIONS_NEVER_SOLVED,
                            help='How many submissions a problem needs before '
                            'never having been solved counts as a finding.')
    thresholds.add_argument('--min-age-days-never-solved',
                            type=_non_negative_int,
                            default=_MIN_AGE_DAYS_NEVER_SOLVED,
                            help='How old a problem must be before never '
                            'having been solved counts as a finding.')
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
            # One clock for the whole run, read before any write, so a finding
            # stored now is never mistaken for one that went missing.
            run_timestamp = current_timestamp(dbconn)
            created_before = run_timestamp - datetime.timedelta(
                days=args.min_age_days_never_solved)

            # A check that raises must fail the run: skipping it would
            # leave its findings unseen and resolve them as if they were fixed.
            findings: List[Finding] = []
            with dbconn.cursor(buffered=True, dictionary=True) as cur:
                with cron_run.phase(CheckType.JUDGE_ERRORS.value):
                    findings.extend(
                        find_judge_errors(cur, run_timestamp,
                                          args.judge_error_window_hours,
                                          args.min_judge_errors,
                                          args.min_judge_error_ratio))
                with cron_run.phase(CheckType.NO_LANGUAGES.value):
                    findings.extend(find_problems_without_languages(cur))
                with cron_run.phase(CheckType.NEVER_SOLVED.value):
                    findings.extend(
                        find_never_solved(cur, created_before,
                                          args.min_submissions_never_solved))
                with cron_run.phase(CheckType.DEPRECATED_PUBLIC.value):
                    findings.extend(find_deprecated_but_public(cur))

            with cron_run.phase('apply_findings'):
                resolved = apply_findings(dbconn, findings, run_timestamp)

            cron_run.set_rows_affected(len(findings) + resolved)
            logging.info('Found %d problems needing attention, resolved %d',
                         len(findings), resolved)
        finally:
            dbconn.conn.close()
            logging.info('Done')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
