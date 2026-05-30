'''Unit tests for `utils.py`.'''
# pylint: disable=wrong-import-position
import datetime
import os
import sys
import unittest

THIS_DIR = os.path.dirname(os.path.realpath(__file__))
sys.path.insert(0, os.path.dirname(THIS_DIR))

import utils


class GetFirstDayOfNextMonthTest(unittest.TestCase):
    '''Tests for `utils.get_first_day_of_next_month`.'''

    def _assert_next(
        self,
        current: datetime.date,
        expected: datetime.date,
    ) -> None:
        '''Assert the helper returns `expected` for `current`.'''
        self.assertEqual(
            utils.get_first_day_of_next_month(current),
            expected,
        )

    def test_january(self) -> None:
        '''January 1st rolls forward to February 1st.'''
        self._assert_next(
            datetime.date(2026, 1, 1),
            datetime.date(2026, 2, 1),
        )

    def test_february_non_leap_year(self) -> None:
        '''February 1st rolls forward to March 1st in a non-leap year.'''
        self._assert_next(
            datetime.date(2026, 2, 1),
            datetime.date(2026, 3, 1),
        )

    def test_february_leap_year(self) -> None:
        '''Leap years still return March 1st; only month/year matter.'''
        self._assert_next(
            datetime.date(2024, 2, 1),
            datetime.date(2024, 3, 1),
        )

    def test_march(self) -> None:
        '''March rolls forward to April.'''
        self._assert_next(
            datetime.date(2026, 3, 1),
            datetime.date(2026, 4, 1),
        )

    def test_april(self) -> None:
        '''April rolls forward to May.'''
        self._assert_next(
            datetime.date(2026, 4, 1),
            datetime.date(2026, 5, 1),
        )

    def test_may(self) -> None:
        '''May rolls forward to June.'''
        self._assert_next(
            datetime.date(2026, 5, 1),
            datetime.date(2026, 6, 1),
        )

    def test_june(self) -> None:
        '''June rolls forward to July.'''
        self._assert_next(
            datetime.date(2026, 6, 1),
            datetime.date(2026, 7, 1),
        )

    def test_july(self) -> None:
        '''July rolls forward to August.'''
        self._assert_next(
            datetime.date(2026, 7, 1),
            datetime.date(2026, 8, 1),
        )

    def test_august(self) -> None:
        '''August rolls forward to September.'''
        self._assert_next(
            datetime.date(2026, 8, 1),
            datetime.date(2026, 9, 1),
        )

    def test_september(self) -> None:
        '''September rolls forward to October.'''
        self._assert_next(
            datetime.date(2026, 9, 1),
            datetime.date(2026, 10, 1),
        )

    def test_october(self) -> None:
        '''October rolls forward to November.'''
        self._assert_next(
            datetime.date(2026, 10, 1),
            datetime.date(2026, 11, 1),
        )

    def test_november(self) -> None:
        '''November rolls forward to December.'''
        self._assert_next(
            datetime.date(2026, 11, 1),
            datetime.date(2026, 12, 1),
        )

    def test_december_rolls_year(self) -> None:
        '''December crosses the year boundary into the next January.'''
        self._assert_next(
            datetime.date(2026, 12, 1),
            datetime.date(2027, 1, 1),
        )


if __name__ == '__main__':
    unittest.main()
