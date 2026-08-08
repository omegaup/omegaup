'''Reusable mock cursor and connection for cron unit tests.

Match resolution uses substring search on whitespace-collapsed SQL.
'''
from typing import (Any, Dict, Iterable, Iterator, List, Optional, Sequence,
                    Tuple, Union)

Row = Union[Tuple[Any, ...], Dict[str, Any]]
Script = Sequence[Tuple[str, Sequence[Row]]]


def _normalize(sql: str) -> str:
    '''Lowercase and collapse whitespace.'''
    return ' '.join(sql.lower().split())


class MockCursor:
    '''Programmable stand-in for `mysql.connector.cursor.MySQLCursor`.

    The result-set shape is decided by the scripted rows: provide tuples to
    mimic a plain cursor or dicts to mimic a ``dictionary=True`` cursor. The
    ``dictionary`` flag is accepted only for signature parity.
    '''

    def __init__(
        self,
        script: Optional[Script] = None,
        dictionary: bool = False,
    ) -> None:
        self._script: List[Tuple[str, Sequence[Row]]] = list(script or [])
        self._dictionary = dictionary
        self._current: Sequence[Row] = []
        self._position = 0
        self.calls: List[Tuple[str, Any]] = []

    def execute(self, sql: str, params: Any = None) -> None:
        '''Record the call and resolve a result set from the script.'''
        self.calls.append((sql, params))
        normalized = _normalize(sql)
        for substring, rows in self._script:
            if _normalize(substring) in normalized:
                self._current = rows
                self._position = 0
                return
        self._current = []
        self._position = 0

    def executemany(self, sql: str, seq_of_params: Iterable[Any]) -> None:
        '''Forward each parameter row through `execute`.'''
        for params in seq_of_params:
            self.execute(sql, params)

    def fetchall(self) -> List[Row]:
        '''Return the rows not yet consumed and exhaust the cursor.'''
        remaining = list(self._current[self._position:])
        self._position = len(self._current)
        return remaining

    def fetchone(self) -> Optional[Row]:
        '''Return the next scripted row and advance, or None when drained.'''
        if self._position >= len(self._current):
            return None
        row = self._current[self._position]
        self._position += 1
        return row

    def __iter__(self) -> Iterator[Row]:
        while self._position < len(self._current):
            row = self._current[self._position]
            self._position += 1
            yield row

    def __enter__(self) -> 'MockCursor':
        return self

    def __exit__(
            self,
            exc_type: Any,
            exc_val: Any,
            exc_tb: Any) -> None:
        del exc_type, exc_val, exc_tb


class MockConnection:
    '''Stand-in for `lib.db.Connection` with scriptable cursors.'''

    def __init__(
        self,
        cur: MockCursor,
        cur_readonly: Optional[MockCursor] = None,
    ) -> None:
        self._cur = cur
        self._cur_ro = cur_readonly or cur
        self.commits = 0
        self.rollbacks = 0
        self.get_warnings: bool = False
        self.conn = self

    def cursor(
        self,
        buffered: bool = False,
        dictionary: bool = False,
    ) -> MockCursor:
        '''Return the scripted read-write cursor.

        Row shape is controlled by the script, so `buffered`/`dictionary` are
        accepted only for signature parity with `lib.db.Connection.cursor`.
        '''
        del buffered, dictionary
        return self._cur

    def readonly_cursor(self) -> MockCursor:
        '''Return the scripted read-only cursor.'''
        return self._cur_ro

    def commit(self) -> None:
        '''Record that a commit was requested.'''
        self.commits += 1

    def rollback(self) -> None:
        '''Record that a rollback was requested.'''
        self.rollbacks += 1

    def close(self) -> None:
        '''No-op.'''
