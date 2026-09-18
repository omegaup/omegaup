'''Runs the cron modules' real SQL against an in-memory SQLite database.

Only the dialect differences the cron queries actually hit are translated, so
the tests execute the production query text instead of asserting on it.
'''
import datetime
import re
import sqlite3
from typing import Any, Dict, Iterable, Iterator, List, Optional, Tuple

# SQLite requires `AS` before an UPDATE alias, MySQL does not.
_UPDATE_ALIAS_RE = re.compile(
    r'\bUPDATE\s+(`?\w+`?)\s+(?!AS\b|SET\b)(\w+)\b', re.IGNORECASE)
_TIMESTAMP_FORMAT = '%Y-%m-%d %H:%M:%S'


def _translate(sql: str) -> str:
    '''Rewrite the MySQL-only constructs the cron queries use.'''
    sql = sql.replace('%s', '?').replace('UNION DISTINCT', 'UNION')
    return _UPDATE_ALIAS_RE.sub(r'UPDATE \1 AS \2', sql)


def _convert(value: Any) -> Any:
    '''SQLite has no DATETIME, so timestamps arrive as strings.'''
    if not isinstance(value, str):
        return value
    try:
        return datetime.datetime.strptime(value, _TIMESTAMP_FORMAT)
    except ValueError:
        return value


def connect(*statements: str) -> sqlite3.Connection:
    '''Open an in-memory database and run the given statements.'''
    conn = sqlite3.connect(':memory:')
    for statement in statements:
        conn.execute(statement)
    return conn


class SqliteCursor:
    '''Dict cursor that executes translated statements on SQLite.'''

    def __init__(self, conn: sqlite3.Connection) -> None:
        self._cur = conn.cursor()
        self.calls: List[Tuple[str, Any]] = []

    def execute(self, sql: str, params: Any = None) -> None:
        '''Record the call and run the translated statement.'''
        self.calls.append((sql, params))
        self._cur.execute(_translate(sql), tuple(params or ()))

    def executemany(self, sql: str, seq_of_params: Iterable[Any]) -> None:
        '''Forward each parameter row through `execute`.'''
        for params in seq_of_params:
            self.execute(sql, params)

    def _row(self, raw: Tuple[Any, ...]) -> Dict[str, Any]:
        names = [column[0] for column in self._cur.description]
        return dict(zip(names, (_convert(value) for value in raw)))

    def fetchall(self) -> List[Dict[str, Any]]:
        '''Return the remaining rows of the last statement.'''
        return [self._row(raw) for raw in self._cur.fetchall()]

    def fetchone(self) -> Optional[Dict[str, Any]]:
        '''Return the next row of the last statement, or None.'''
        raw = self._cur.fetchone()
        return None if raw is None else self._row(raw)

    def __iter__(self) -> Iterator[Dict[str, Any]]:
        for raw in self._cur:
            yield self._row(raw)

    def __enter__(self) -> 'SqliteCursor':
        return self

    def __exit__(self, exc_type: Any, exc_val: Any, exc_tb: Any) -> None:
        del exc_type, exc_val, exc_tb
