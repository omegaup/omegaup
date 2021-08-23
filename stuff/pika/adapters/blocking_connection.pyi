from __future__ import annotations

import types
from typing import Any, Callable, List, Mapping, Optional, Tuple, Type

from .. import channel, connection, frame, spec


class BlockingConnection:

    def __init__(
        self,
        parameters: Optional[connection.Parameters] = ...,
        _impl_class: Optional[Any] = ...,
    ) -> None: ...

    def __enter__(self) -> BlockingConnection: ...

    def __exit__(
        self,
        exc_type: Optional[Type[BaseException]],
        value: Optional[BaseException],
        traceback: Optional[types.TracebackType],
    ) -> None: ...

    def add_on_connection_blocked_callback(
        self,
        callback: Callable[[spec.Connection.Blocked], None],
    ) -> None: ...

    def add_on_connection_unblocked_callback(
        self,
        callback: Callable[[spec.Connection.Unblocked], None],
    ) -> None: ...

    def call_later(
        self,
        delay: float,
        callback: Callable[[], None],
    ) -> object: ...

    def add_callback_threadsafe(self, callback: Callable[[], None]) -> None: ...
    def remove_timeout(self, timeout_id: object) -> None: ...
    def close(self, reply_code: int = ..., reply_text: str = ...) -> None: ...
    def process_data_events(self, time_limit: int = ...) -> None: ...
    def sleep(self, duration: float) -> None: ...
    def channel(self, channel_number: Optional[int] = ...) -> BlockingChannel: ...

    @property
    def is_closed(self) -> bool: ...
    @property
    def is_open(self) -> bool: ...
    @property
    def basic_nack_supported(self) -> bool: ...
    @property
    def consumer_cancel_notify_supported(self) -> bool: ...
    @property
    def exchange_exchange_bindings_supported(self) -> bool: ...
    @property
    def publisher_confirms_supported(self) -> bool: ...

    # Legacy property names for backward compatibility
    @property
    def basic_nack(self) -> bool: ...
    @property
    def consumer_cancel_notify(self) -> bool: ...
    @property
    def exchange_exchange_bindings(self) -> bool: ...
    @property
    def publisher_confirms(self) -> bool: ...


class ReturnedMessage:

    method: spec.Basic.Return = ...
    properties: spec.BasicProperties = ...
    body: bytes = ...

    def __init__(
        self,
        method: spec.Basic.Return,
        properties: spec.BasicProperties,
        body: bytes,
    ) -> None: ...


class BlockingChannel:

    def __init__(self, channel_impl: channel.Channel, connection: BlockingConnection) -> None: ...

    def __int__(self) -> int: ...

    def __enter__(self) -> BlockingChannel: ...

    def __exit__(
        self,
        exc_type: Optional[Type[BaseException]],
        value: Optional[BaseException],
        traceback: Optional[types.TracebackType],
    ) -> None: ...

    @property
    def channel_number(self) -> int: ...
    @property
    def connection(self) -> BlockingConnection: ...
    @property
    def is_closed(self) -> bool: ...
    @property
    def is_open(self) -> bool: ...
    @property
    def consumer_tags(self) -> List[str]: ...

    def close(self, reply_code: int = ..., reply_text: str = ...) -> None: ...
    def flow(self, active: bool) -> bool: ...

    def add_on_cancel_callback(self, callback: Callable[[spec.Basic.Cancel], None]) -> None: ...
    def add_on_return_callback(self, callback: Callable[[spec.Basic.Return], None]) -> None: ...

    def basic_consume(
        self,
        queue: Any,
        on_message_callback: Callable[
            [
                BlockingChannel,
                spec.Basic.Deliver,
                spec.BasicProperties,
                bytes,
            ],
            None,
        ],
        auto_ack: bool = ...,
        exclusive: bool = ...,
        consumer_tag: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> str: ...

    def basic_cancel(self, consumer_tag: str) -> List[
            Tuple[spec.Basic.Deliver, spec.BasicProperties, bytes],
    ]: ...
    def start_consuming(self) -> None: ...
    def stop_consuming(self, consumer_tag: Optional[str] = ...) -> None: ...

    def consume(
        self,
        queue: str,
        auto_ack: bool = ...,
        exclusive: bool = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        inactivity_timeout: Optional[float] = ...,
    ) -> None: ...

    def get_waiting_message_count(self) -> int: ...

    def cancel(self) -> int: ...

    def basic_ack(self, delivery_tag: int = ..., multiple: bool = ...) -> None: ...

    def basic_nack(
        self,
        delivery_tag: Optional[int] = ...,
        multiple: bool = ...,
        requeue: bool = ...,
    ) -> None: ...

    def basic_get(self, queue: str, auto_ack: bool = ...) -> Tuple[
        Optional[spec.Basic.GetOk],
        Optional[spec.BasicProperties],
        Optional[str],
    ]: ...

    def basic_publish(
        self,
        exchange: str,
        routing_key: str,
        body: bytes,
        properties: Optional[spec.BasicProperties] = ...,
        mandatory: bool = ...,
    ) -> None: ...

    def basic_qos(
        self,
        prefetch_size: int = ...,
        prefetch_count: int = ...,
        global_qos: bool = ...,
    ) -> None: ...

    def basic_recover(self, requeue: bool = ...) -> None: ...
    def basic_reject(self, delivery_tag: Optional[int] = ..., requeue: bool = ...) -> None: ...

    def confirm_delivery(self) -> None: ...

    def exchange_declare(
        self,
        exchange: str,
        exchange_type: str = ...,
        passive: bool = ...,
        durable: bool = ...,
        auto_delete: bool = ...,
        internal: bool = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> frame.Method[spec.Exchange.DeclareOk]: ...

    def exchange_delete(
        self,
        exchange: Optional[str] = ...,
        if_unused: bool = ...,
    ) -> frame.Method[spec.Exchange.DeleteOk]: ...

    def exchange_bind(
        self,
        destination: str,
        source: str,
        routing_key: str = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> frame.Method[spec.Exchange.BindOk]: ...

    def exchange_unbind(
        self,
        destination: Optional[str] = ...,
        source: Optional[str] = ...,
        routing_key: str = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> frame.Method[spec.Exchange.UnbindOk]: ...

    def queue_declare(
        self,
        queue: str,
        passive: bool = ...,
        durable: bool = ...,
        exclusive: bool = ...,
        auto_delete: bool = ...,
        arguments: Optional[spec.Queue.DeclareOk] = ...,
    ) -> frame.Method[spec.Queue.DeclareOk]: ...

    def queue_delete(
        self,
        queue: str,
        if_unused: bool = ...,
        if_empty: bool = ...,
    ) -> frame.Method[spec.Queue.DeleteOk]: ...

    def queue_purge(self, queue: str) -> frame.Method[spec.Queue.PurgeOk]: ...

    def queue_bind(
        self,
        queue: str,
        exchange: str,
        routing_key: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> frame.Method[spec.Queue.BindOk]: ...

    def queue_unbind(
        self,
        queue: Any,
        exchange: Optional[str] = ...,
        routing_key: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
    ) -> frame.Method[spec.Queue.UnbindOk]: ...

    def tx_select(self) -> frame.Method[spec.Tx.SelectOk]: ...
    def tx_commit(self) -> frame.Method[spec.Tx.CommitOk]: ...
    def tx_rollback(self) -> frame.Method[spec.Tx.CommitOk]: ...
