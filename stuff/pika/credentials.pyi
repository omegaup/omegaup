from typing import Optional, Tuple, Union

from . import spec


class PlainCredentials:

    TYPE: str = ...

    username: str = ...
    password: str = ...
    erase_on_connect: bool = ...

    def __init__(self, username: str, password: str, erase_on_connect: bool = ...) -> None: ...

    def response_for(
        self,
        start: spec.Connection.Start,
    ) -> Tuple[Optional[str], Optional[str]]: ...

    def erase_credentials(self) -> None: ...


class ExternalCredentials:

    TYPE: str = ...

    erase_on_connect: bool = ...

    def response_for(
        self,
        start: spec.Connection.Start,
    ) -> Tuple[Optional[str], Optional[str]]: ...

    def erase_credentials(self) -> None: ...


VALID_TYPES = Union[PlainCredentials, ExternalCredentials]
