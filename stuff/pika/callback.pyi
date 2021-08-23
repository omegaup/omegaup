from typing import Any, Callable, Mapping, Optional, Tuple, Union

from . import amqp_object

_Prefix = Union[int, str]
_Key = Any
_Caller = object
_Callback = Callable[..., Any]


def name_or_value(value: amqp_object.AMQPObject) -> str: ...
def sanitize_prefix(function: _Callback) -> _Callback: ...
def check_for_prefix_and_key(function: _Callback) -> _Callback: ...


class CallbackManager:

    CALLS: str = ...
    ARGUMENTS: str = ...
    DUPLICATE_WARNING: str = ...
    CALLBACK: str = ...
    ONE_SHOT: str = ...
    ONLY_CALLER: str = ...

    def add(
        self,
        prefix: _Prefix,
        key: _Key,
        callback: _Callback,
        one_shot: bool = ...,
        only_caller: Optional[_Caller] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> Tuple[_Prefix, Any]: ...

    def clear(self) -> None: ...
    def cleanup(self, prefix: _Prefix) -> bool: ...

    def pending(self, prefix: _Prefix, key: _Key) -> Optional[int]: ...

    def process(
        self,
        prefix: _Prefix,
        key: _Key,
        caller: _Caller,
        *args: Any,
        **keywords: Any,
    ) -> bool: ...

    def remove(
        self,
        prefix: _Prefix,
        key: _Key,
        callback_value: Optional[_Callback] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> bool: ...
    def remove_all(self, prefix: _Prefix, key: _Key) -> None: ...
