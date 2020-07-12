from typing import Iterable, Optional, Tuple, Type

from . import cursors
from . import _exceptions


class Connection(object):
    Warning: Type[_exceptions.Warning]

    def close(self) -> None: ...
    def cursor(
      self,
      cursorclass: Optional[Type[cursors.BaseCursor]] = None
    ) -> cursors.BaseCursor: ...
    def commit(self) -> None: ...
    def rollback(self) -> None: ...
    def show_warnings(self) -> Iterable[Tuple[str, int, str]]: ...
