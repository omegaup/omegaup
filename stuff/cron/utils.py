'''Util functions for cron jobs'''
from typing import NamedTuple, List, Optional, TypedDict
import datetime


class UserProblems(TypedDict):
    '''Problems solved by a user and their calculated score'''
    solved: List[int]
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
