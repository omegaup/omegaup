from typing import Any, Iterator, Iterable, Mapping, Optional, Sequence, Text, Tuple


class BaseCursor:
    def close(self) -> None:
        ...

    def execute(self,
                operation: Text,
                parameters: Optional[Any] = None) -> None:
        ...

    def executemany(self, operation: Text, parameters: Sequence[Any]) -> None:
        ...

    def fetchwarnings(self) -> Sequence[Tuple[Text, int, Text]]:
        ...


class MySQLCursor(BaseCursor):
    def fetchone(self) -> Tuple[Any, ...]:
        ...

    def fetchall(self) -> Iterable[Tuple[Any, ...]]:
        ...


class MySQLCursorBuffered(MySQLCursor):
    ...


class MySQLCursorDict(BaseCursor):
    def __iter__(self) -> Iterator[Mapping[str, Any]]:
        ...

    def fetchone(self) -> Mapping[str, Any]:
        ...

    def fetchall(self) -> Iterable[Mapping[str, Any]]:
        ...


class MySQLCursorBufferedDict(MySQLCursorDict):
    ...
