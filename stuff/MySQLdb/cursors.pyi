from typing import Any, Iterator, List, Optional, Sequence

from . import connections


class BaseCursor(object):
    rowcount: int
    def __init__(self, connection: connections.Connection) -> None:
      self.messages: List[str] = []

    def __enter__(self) -> BaseCursor: ...
    def __exit__(self, *exc_info: Any) -> None: ...
    def __iter__(self) -> Iterator[Any]: ...
    def execute(self, query: str, args: Optional[Any] = None) -> int: ...
    def executemany(self, query: str, args: Sequence[Any]) -> int: ...
    def fetchone(self) -> Sequence[Any]: ...


class DictCursor(BaseCursor):
    pass
