import abc
from socket import AddressFamily, SocketKind
from typing import (
    Any,
    AnyStr,
    Callable,
    Generic,
    IO,
    List,
    Optional,
    Text,
    Tuple,
    TypeVar,
    Union,
)

from . import io_services_utils, nbio_interface

_Timeout = TypeVar('_Timeout', bound=object)


class AbstractSelectorIOLoop(Generic[_Timeout], metaclass=abc.ABCMeta):

    @property
    @abc.abstractmethod
    def READ(self) -> int: ...
    @property
    @abc.abstractmethod
    def WRITE(self) -> int: ...
    @property
    @abc.abstractmethod
    def ERROR(self) -> int: ...

    @abc.abstractmethod
    def close(self) -> None: ...
    @abc.abstractmethod
    def start(self) -> None: ...
    @abc.abstractmethod
    def stop(self) -> None: ...
    @abc.abstractmethod
    def call_later(self, delay: float, callback: Callable[[], None]) -> _Timeout: ...
    @abc.abstractmethod
    def remove_timeout(self, timeout_handle: _Timeout) -> None: ...
    @abc.abstractmethod
    def add_callback(self, callback: Callable[[], None]) -> None: ...

    @abc.abstractmethod
    def add_handler(
        self,
        fd: IO[AnyStr],
        handler: Callable[[IO[AnyStr], int], None],
        events: int,
    ) -> None: ...

    @abc.abstractmethod
    def update_handler(self, fd: IO[AnyStr], events: int) -> None: ...
    @abc.abstractmethod
    def remove_handler(self, fd: IO[AnyStr]) -> None: ...


class SelectorIOServicesAdapter(
    Generic[_Timeout],
    io_services_utils.SocketConnectionMixin,
    io_services_utils.StreamingConnectionMixin,
    nbio_interface.AbstractIOServices,
    nbio_interface.AbstractFileDescriptorServices,
):

    def __init__(self, native_loop: AbstractSelectorIOLoop[_Timeout]) -> None: ...
    def get_native_ioloop(self) -> AbstractSelectorIOLoop[_Timeout]: ...
    def close(self) -> None: ...
    def run(self) -> None: ...
    def stop(self) -> None: ...
    def add_callback_threadsafe(self, callback: Callable[[], None]) -> None: ...

    def call_later(
        self,
        delay: float,
        callback: Callable[[], None],
    ) -> nbio_interface.AbstractTimerReference: ...

    def getaddrinfo(
        self,
        host: Optional[Union[bytearray, bytes, Text]],
        port: Union[str, int, None],
        on_done: Callable[
            [
                Union[
                    BaseException,
                    List[Tuple[AddressFamily, SocketKind, int, str, Tuple[Any, ...]]],
                ]
            ],
            None,
        ],
        family: int,
        socktype: int,
        proto: int,
        flags: int,
    ) -> nbio_interface.AbstractIOReference: ...

    def set_reader(self, fd: IO[AnyStr], on_readable: Callable[[], None]) -> None: ...
    def remove_reader(self, fd: IO[AnyStr]) -> bool: ...
    def set_writer(self, fd: IO[AnyStr], on_writable: Callable[[], None]) -> None: ...
    def remove_writer(self, fd: IO[AnyStr]) -> bool: ...
