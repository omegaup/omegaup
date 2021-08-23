from __future__ import annotations

from typing import Any, Callable, List, Mapping, NamedTuple, Optional, Sequence, Union

import twisted.internet.base
import twisted.internet.defer
import twisted.internet.interfaces
import twisted.internet.protocol
import twisted.python.failure

from .. import channel as channel_, connection as connection_, spec


class ClosableDeferredQueue(twisted.internet.defer.DeferredQueue):  # type: ignore

    closed: Any = ...  # TODO

    def __init__(
        self,
        size: Optional[int] = ...,
        backlog: Optional[int] = ...,
    ) -> None: ...

    def put(self, obj: Any) -> None: ...
    def get(self) -> twisted.internet.defer.Deferred: ...
    def close(self, reason: Union[twisted.python.failure.Failure, Exception]) -> None: ...


class ReceivedMessage(NamedTuple):

    channel: TwistedChannel
    method: spec.Basic.Return
    properties: spec.BasicProperties
    body: bytes


class TwistedChannel:

    def __init__(self, channel: channel_.Channel) -> None: ...

    @property
    def channel_number(self) -> int: ...
    @property
    def connection(self) -> connection_.Connection: ...

    @property
    def is_closed(self) -> bool: ...
    @property
    def is_closing(self) -> bool: ...
    @property
    def is_open(self) -> bool: ...

    @property
    def flow_active(self) -> bool: ...
    @property
    def consumer_tags(self) -> List[str]: ...

    def callback_deferred(
        self,
        deferred: twisted.internet.defer.Deferred,
        replies: Sequence[Any],
    ) -> None: ...

    def add_on_return_callback(self, callback: Callable[[ReceivedMessage], None]) -> None: ...
    def basic_ack(self, delivery_tag: int = ..., multiple: bool = ...) -> None: ...
    def basic_cancel(self, consumer_tag: str = ...) -> None: ...

    def basic_consume(
        self,
        queue: str,
        auto_ack: bool = ...,
        exclusive: bool = ...,
        consumer_tag: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def basic_get(
        self,
        queue: str,
        auto_ack: bool = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def basic_nack(
        self,
        delivery_tag: Optional[int] = ...,
        multiple: bool = ...,
        requeue: bool = ...,
    ) -> None: ...

    def basic_publish(
        self,
        exchange: str,
        routing_key: str,
        body: bytes,
        properties: Optional[spec.BasicProperties] = ...,
        mandatory: bool = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def basic_qos(
        self,
        prefetch_size: int = ...,
        prefetch_count: int = ...,
        global_qos: bool = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def basic_reject(self, delivery_tag: int, requeue: bool = ...) -> None: ...
    def basic_recover(self, requeue: bool = ...) -> twisted.internet.defer.Deferred: ...

    def close(self, reply_code: int = ..., reply_text: str = ...) -> None: ...
    def confirm_delivery(self) -> twisted.internet.defer.Deferred: ...

    def exchange_bind(
        self,
        destination: str,
        source: str,
        routing_key: str = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def exchange_declare(
        self,
        exchange: str,
        exchange_type: str = ...,
        passive: bool = ...,
        durable: bool = ...,
        auto_delete: bool = ...,
        internal: bool = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def exchange_delete(
        self,
        exchange: Optional[str] = ...,
        if_unused: bool = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def exchange_unbind(
        self,
        destination: Optional[str] = ...,
        source: Optional[str] = ...,
        routing_key: str = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def flow(self, active: bool) -> twisted.internet.defer.Deferred: ...
    def open(self) -> None: ...

    def queue_bind(
        self,
        queue: str,
        exchange: str,
        routing_key: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def queue_declare(
        self,
        queue: str,
        passive: bool = ...,
        durable: bool = ...,
        exclusive: bool = ...,
        auto_delete: bool = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def queue_delete(
        self,
        queue: str,
        if_unused: bool = ...,
        if_empty: bool = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def queue_purge(self, queue: str) -> twisted.internet.defer.Deferred: ...

    def queue_unbind(
        self,
        queue: str,
        exchange: Optional[str] = ...,
        routing_key: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> twisted.internet.defer.Deferred: ...

    def tx_commit(self) -> twisted.internet.defer.Deferred: ...
    def tx_rollback(self) -> twisted.internet.defer.Deferred: ...
    def tx_select(self) -> twisted.internet.defer.Deferred: ...


class TwistedProtocolConnection(twisted.internet.protocol.Protocol):  # type: ignore

    ready: twisted.internet.defer.Deferred = ...
    closed: Optional[twisted.internet.defer.Deferred] = ...

    def __init__(
        self,
        parameters: Optional[connection_.Parameters] = ...,
        custom_reactor: Optional[twisted.internet.base.ReactorBase] = ...,
    ) -> None: ...

    def channel(self, channel_number: Optional[int] = ...) -> twisted.internet.defer.Deferred: ...

    @property
    def is_closed(self) -> bool: ...

    def close(
        self,
        reply_code: int = ...,
        reply_text: str = ...,
    ) -> twisted.internet.defer.Deferred: ...

    # IProtocol methods

    def dataReceived(self, data: bytes) -> None: ...
    def connectionLost(self, reason: twisted.python.failure.Failure = ...) -> None: ...
    def makeConnection(self, transport: twisted.internet.interfaces.ITransport) -> None: ...

    # Our own methods

    def connectionReady(self) -> None: ...
