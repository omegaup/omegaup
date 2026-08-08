'''Unit tests for the badge-assignment logic in `assign_badges.py`.'''
import datetime
import json
import unittest
from typing import Any, cast
from unittest import mock

import mysql.connector.cursor

from cron import assign_badges
from cron.tests.fixtures.mock_cursor import MockConnection, MockCursor
import lib.db


def _as_cursor(cur: MockCursor) -> mysql.connector.cursor.MySQLCursorDict:
    '''Type a MockCursor as the dict cursor the cron code expects.'''
    return cast(mysql.connector.cursor.MySQLCursorDict, cur)


def _as_conn(conn: MockConnection) -> lib.db.Connection:
    '''Type a MockConnection as the lib.db connection the cron code expects.'''
    return cast(lib.db.Connection, conn)


class GetAllOwnersTest(unittest.TestCase):
    '''Tests for `assign_badges.get_all_owners`.'''

    _QUERY = 'SELECT user_id FROM Users WHERE created < NOW();'

    def test_returns_the_user_ids_from_the_query(self) -> None:
        '''The owner ids are read out of the result rows.'''
        cur = MockCursor(
            script=[('select user_id', [{'user_id': 1}, {'user_id': 2}])])

        with mock.patch(
                'builtins.open', mock.mock_open(read_data=self._QUERY)):
            result = assign_badges.get_all_owners(
                'contestManager', None, _as_cursor(cur))

        self.assertEqual(result, {1, 2})

    def test_replaces_now_with_the_given_timestamp(self) -> None:
        '''A timestamp is substituted for the literal NOW().'''
        cur = MockCursor(script=[('select user_id', [])])
        timestamp = datetime.datetime(2026, 6, 1, 3, 0, 0)

        with mock.patch(
                'builtins.open', mock.mock_open(read_data=self._QUERY)):
            assign_badges.get_all_owners(
                'coderOfTheMonth', timestamp, _as_cursor(cur))

        executed_sql = cur.calls[-1][0]
        self.assertIn('2026-06-01 03:00:00', executed_sql)
        self.assertNotIn('NOW()', executed_sql)

    def test_keeps_now_when_no_timestamp_is_given(self) -> None:
        '''Without a timestamp the query is executed unchanged.'''
        cur = MockCursor(script=[('select user_id', [])])

        with mock.patch(
                'builtins.open', mock.mock_open(read_data=self._QUERY)):
            assign_badges.get_all_owners(
                'coderOfTheMonth', None, _as_cursor(cur))

        self.assertIn('NOW()', cur.calls[-1][0])


class GetCurrentOwnersTest(unittest.TestCase):
    '''Tests for `assign_badges.get_current_owners`.'''

    def test_returns_current_owner_ids(self) -> None:
        '''The current owners are read out of Users_Badges.'''
        cur = MockCursor(
            script=[('users_badges', [{'user_id': 5}, {'user_id': 7}])])

        result = assign_badges.get_current_owners(
            'contestManager', _as_cursor(cur))

        self.assertEqual(result, {5, 7})

    def test_filters_by_the_badge_alias(self) -> None:
        '''The badge alias is passed as a query parameter.'''
        cur = MockCursor(script=[('users_badges', [])])

        assign_badges.get_current_owners('contestManager', _as_cursor(cur))

        self.assertEqual(cur.calls[0][1], ('contestManager',))


class SaveNewOwnersTest(unittest.TestCase):
    '''Tests for `assign_badges.save_new_owners`.'''

    def test_inserts_badges_and_notifications(self) -> None:
        '''Each new owner gets a badge row and a notification row.'''
        cur = MockCursor()

        assign_badges.save_new_owners(
            'contestManager', {1, 2}, _as_cursor(cur))

        badge_rows = {
            params for sql, params in cur.calls
            if 'users_badges' in sql.lower()
        }
        self.assertEqual(
            badge_rows,
            {(1, 'contestManager'), (2, 'contestManager')})

        notifications = {
            params[0]: json.loads(params[1])
            for sql, params in cur.calls
            if 'notifications' in sql.lower()
        }
        self.assertEqual(
            notifications,
            {
                1: {'type': 'badge', 'badge': 'contestManager'},
                2: {'type': 'badge', 'badge': 'contestManager'},
            })

    def test_no_users_inserts_nothing(self) -> None:
        '''With no new owners the cursor records no inserts.'''
        cur = MockCursor()

        assign_badges.save_new_owners('contestManager', set(), _as_cursor(cur))

        self.assertEqual(cur.calls, [])


class ProcessBadgesTest(unittest.TestCase):
    '''Tests for `assign_badges.process_badges`.'''

    def _patch(self, name: str, **kwargs: Any) -> mock.Mock:
        patcher = mock.patch.object(assign_badges, name, **kwargs)
        started = patcher.start()
        self.addCleanup(patcher.stop)
        return cast(mock.Mock, started)

    def _patch_badge_dirs(self, *names: str) -> None:
        entries = []
        for name in names:
            entry = mock.Mock()
            entry.name = name
            entry.is_dir.return_value = True
            entries.append(entry)
        patcher = mock.patch(
            'cron.assign_badges.os.scandir', return_value=entries)
        patcher.start()
        self.addCleanup(patcher.stop)

    def test_saves_only_the_new_owners(self) -> None:
        '''Only owners that are not already recorded are saved.'''
        self._patch('get_all_owners', return_value={1, 2, 3})
        self._patch('get_current_owners', return_value={2})
        save = self._patch('save_new_owners')
        self._patch_badge_dirs('contestManager')
        dbconn = MockConnection(MockCursor())

        has_failures = assign_badges.process_badges(
            None, _as_conn(dbconn),
            _as_cursor(MockCursor()), _as_cursor(MockCursor()))

        self.assertFalse(has_failures)
        save.assert_called_once()
        self.assertEqual(save.call_args[0][0], 'contestManager')
        self.assertEqual(save.call_args[0][1], {1, 3})
        self.assertGreaterEqual(dbconn.commits, 1)

    def test_skips_save_when_there_are_no_new_owners(self) -> None:
        '''A badge with no new owners is not written.'''
        self._patch('get_all_owners', return_value={2})
        self._patch('get_current_owners', return_value={2})
        save = self._patch('save_new_owners')
        self._patch_badge_dirs('contestManager')
        dbconn = MockConnection(MockCursor())

        has_failures = assign_badges.process_badges(
            None, _as_conn(dbconn),
            _as_cursor(MockCursor()), _as_cursor(MockCursor()))

        self.assertFalse(has_failures)
        save.assert_not_called()

    def test_one_failing_badge_rolls_back_and_continues(self) -> None:
        '''A failure on one badge is isolated; the next badge still runs.'''
        self._patch(
            'get_all_owners', side_effect=[RuntimeError('boom'), {1}])
        self._patch('get_current_owners', return_value=set())
        save = self._patch('save_new_owners')
        self._patch_badge_dirs('brokenBadge', 'goodBadge')
        dbconn = MockConnection(MockCursor())

        has_failures = assign_badges.process_badges(
            None, _as_conn(dbconn),
            _as_cursor(MockCursor()), _as_cursor(MockCursor()))

        self.assertTrue(has_failures)
        self.assertGreaterEqual(dbconn.rollbacks, 1)
        save.assert_called_once()
        self.assertEqual(save.call_args[0][0], 'goodBadge')
        self.assertEqual(save.call_args[0][1], {1})


if __name__ == '__main__':
    unittest.main()
