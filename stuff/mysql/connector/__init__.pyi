from typing import overload, Literal, Text

from . import cursor

def connect(
    user: Text,
    password: Text,
    database: Text,
    host: Text,
    port: int = 13306,
) -> MySQLConnection:
    ...


class MySQLConnection:
    get_warnings: bool

    def close(self) -> None:
        ...

    @overload
    def cursor(self, *, buffered: Literal[True],
               dictionary: Literal[True]) -> cursor.MySQLCursorBufferedDict:
        ...

    @overload
    def cursor(self,
               *,
               buffered: Literal[False] = ...,
               dictionary: Literal[True]) -> cursor.MySQLCursorDict:
        ...

    @overload
    def cursor(self,
               *,
               buffered: Literal[True],
               dictionary: Literal[False] = ...) -> cursor.MySQLCursorBuffered:
        ...

    @overload
    def cursor(self,
               *,
               buffered: Literal[False] = ...,
               dictionary: Literal[False] = ...) -> cursor.MySQLCursor:
        ...

    @overload
    def cursor(self,
               *,
               buffered: bool = False,
               dictionary: bool = False) -> cursor.BaseCursor:
        ...

    def commit(self) -> None:
        ...

    def rollback(self) -> None:
        ...
