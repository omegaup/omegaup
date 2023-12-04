#!/usr/bin/env python3

'''Mysql queries to generate messages for contests'''

import datetime
from typing import Dict, List, NamedTuple

import mysql.connector
import mysql.connector.cursor
import omegaup.api


class Ranking(NamedTuple):
    '''Relevant information for ranking users.'''
    username: str
    place: str


class ContestCertificate(NamedTuple):
    '''Relevant information for contests.'''
    certificate_cutoff: int
    alias: str
    scoreboard_url: str
    contest_id: int
    ranking: List[Dict[str, str]]


class UserContestCertificate(NamedTuple):
    '''Relevant information for user contest certificates.'''
    username: str
    contest_id: str


def get_all_certificates_for_contests(
    *,
    cur: mysql.connector.cursor.MySQLCursorDict
) -> List[UserContestCertificate]:
    '''
    Get all certificates previously registered in order to avoid duplicated
    entries for a user contest
    '''

    cur.execute(
        '''
        SELECT
            i.username,
            c.contest_id
        FROM
            Certificates ce
        INNER JOIN
            Contests c
        ON
            c.contest_id = ce.contest_id
        INNER JOIN
            Identities i
        ON
            i.identity_id = ce.identity_id;
        ''')

    data: List[UserContestCertificate] = []
    for row in cur:
        user_contest = UserContestCertificate(
            username=row['username'],
            contest_id=row['contest_id'],
        )
        data.append(user_contest)
    return data


def get_contests(
        *,
        cur: mysql.connector.cursor.MySQLCursorDict,
        date_lower_limit: datetime.datetime,
        date_upper_limit: datetime.datetime,
        certificates: List[UserContestCertificate],
        client: omegaup.api.Client,
) -> List[ContestCertificate]:
    '''Get contests information'''

    cur.execute(
        '''
        SELECT
            certificate_cutoff,
            c.contest_id,
            alias,
            scoreboard_url
        FROM
            Contests c
        INNER JOIN
            Problemsets p
        ON
            c.problemset_id = p.problemset_id
        WHERE
            finish_time >= %s
            AND finish_time <= %s
            AND certificates_status <> 'generated';
        ''', (date_lower_limit,
              date_upper_limit.replace(hour=23, minute=59, second=59))
    )
    data: List[ContestCertificate] = []
    ranking: List[Dict[str, str]] = []
    for row in cur:
        scoreboard = client.contest.scoreboard(
            contest_alias=row['alias'],
            token=row['scoreboard_url'])
        users_contest: List[UserContestCertificate] = [
            certificate for certificate in certificates
            if certificate.contest_id == row['contest_id']
        ]
        for position in scoreboard.ranking:
            existing_certificates = [
                record for record in users_contest
                if record.username == position.username
            ]
            if len(existing_certificates) > 0:
                continue
            ranking.append(Ranking(
                username=position.username,
                place=f'{position.place}')._asdict())
        contest = ContestCertificate(
            certificate_cutoff=row['certificate_cutoff'],
            alias=row['alias'],
            scoreboard_url=row['scoreboard_url'],
            contest_id=row['contest_id'],
            ranking=ranking
        )
        ranking = []
        data.append(contest)
    return data
