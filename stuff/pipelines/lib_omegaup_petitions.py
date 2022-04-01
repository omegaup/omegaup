#!/usr/bin/python3

'''Module to get omegaUp information via API'''

import dataclasses

from typing import List
import omegaup.api


@dataclasses.dataclass
class Ranking:
    '''A dataclass for contest ranking.'''
    place: int
    username: str


@dataclasses.dataclass
class Scoreboard:
    '''A dataclass for contest scoreboard.'''
    ranking: List[Ranking]


def get_contest_scoreboard(*,
                           api_token: str,
                           url: str,
                           alias: str,
                           scoreboard_url: str) -> List[Ranking]:
    '''Get scoreboard from a contest'''
    client = omegaup.api.Client(api_token=api_token, url=url)

    scoreboard: Scoreboard = client.contest.scoreboard(
        contest_alias=alias,
        token=scoreboard_url)
    return scoreboard['ranking']
