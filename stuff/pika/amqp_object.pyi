from __future__ import annotations

from typing import Optional


class AMQPObject:

    NAME: str = ...
    INDEX: Optional[int] = ...


class Class(AMQPObject):

    NAME: str = ...


class Method(AMQPObject):

    NAME: str = ...

    @property
    def synchronous(self) -> bool: ...

    def get_properties(self) -> Properties: ...
    def get_body(self) -> bytes: ...


class Properties(AMQPObject):

    NAME: str = ...
