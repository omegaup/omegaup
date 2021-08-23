from __future__ import annotations

from typing import Any, Callable, List, Mapping, Optional, Sequence, Tuple, Union

from . import (
    callback,
    connection as connection_,
    frame,
    spec,
)

MAX_CHANNELS: int

_OnAckNackCallback = Callable[[frame.Method[Union[spec.Basic.Ack, spec.Basic.Nack]]], None]
_OnConfirmDeliveryCallback = Callable[[frame.Method[spec.Confirm.SelectOk]], None]
_OnBasicConsumeCallback = Callable[[frame.Method[spec.Basic.ConsumeOk]], None]
_OnBasicGetCallback = Callable[['Channel', spec.Basic.GetOk, spec.BasicProperties, bytes], None]
_OnBasicRecoverCallback = Callable[[frame.Method[spec.Basic.RecoverOk]], None]
_OnBasicQosCallback = Callable[[frame.Method[spec.Basic.QosOk]], None]
_OnBasicCancelCallback = Callable[[frame.Method[spec.Basic.CancelOk]], None]
_OnCloseCallback = Callable[['Channel', Exception], None]
_OnExchangeBindCallback = Callable[[frame.Method[spec.Exchange.BindOk]], None]
_OnExchangeDeclareCallback = Callable[[frame.Method[spec.Exchange.DeclareOk]], None]
_OnExchangeDeleteCallback = Callable[[frame.Method[spec.Exchange.DeleteOk]], None]
_OnExchangeUnbindCallback = Callable[[frame.Method[spec.Exchange.UnbindOk]], None]
_OnFlowCallback = Callable[[bool], None]
_OnMessageCallback = Callable[['Channel', spec.Basic.Deliver, spec.BasicProperties, bytes], None]
_OnOpenCallback = Callable[['Channel'], None]
_OnQueueBindCallback = Callable[[frame.Method[spec.Queue.BindOk]], None]
_OnQueueDeclareCallback = Callable[[frame.Method[spec.Queue.DeclareOk]], None]
_OnQueueDeleteCallback = Callable[[frame.Method[spec.Queue.DeleteOk]], None]
_OnQueuePurgeCallback = Callable[[frame.Method[spec.Queue.PurgeOk]], None]
_OnQueueUnbindCallback = Callable[[frame.Method[spec.Queue.UnbindOk]], None]
_OnReturnCallback = Callable[['Channel', spec.Basic.Return, spec.BasicProperties, bytes], None]
_OnTxCommitCallback = Callable[[spec.Tx.CommitOk], None]
_OnTxRollbackCallback = Callable[[spec.Tx.RollbackOk], None]
_OnTxSelectCallback = Callable[[spec.Tx.SelectOk], None]


class Channel:

    CLOSED: int = ...
    OPENING: int = ...
    OPEN: int = ...
    CLOSING: int = ...

    channel_number: int = ...
    callbacks: callback.CallbackManager = ...
    connection: connection_.Connection = ...
    flow_active: bool = ...

    def __init__(
        self,
        connection: connection_.Connection,
        channel_number: int,
        on_open_callback: _OnOpenCallback,
    ) -> None: ...

    def __int__(self) -> int: ...

    def add_callback(
        self,
        callback: Callable[..., Any],
        replies: Sequence[Any],
        one_shot: bool = ...,
    ) -> None: ...

    def add_on_cancel_callback(self, callback: _OnBasicCancelCallback) -> None: ...
    def add_on_close_callback(self, callback: _OnCloseCallback) -> None: ...
    def add_on_flow_callback(self, callback: _OnFlowCallback) -> None: ...
    def add_on_return_callback(self, callback: _OnReturnCallback) -> None: ...

    def basic_ack(
        self,
        delivery_tag: int = ...,
        multiple: bool = ...,
    ) -> None: ...

    def basic_cancel(
        self,
        consumer_tag: str = ...,
        callback: Optional[_OnBasicCancelCallback] = ...,
    ) -> None: ...

    def basic_consume(
        self,
        queue: str,
        on_message_callback: _OnMessageCallback,
        auto_ack: bool = ...,
        exclusive: bool = ...,
        consumer_tag: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        callback: Optional[_OnBasicConsumeCallback] = ...,
    ) -> str: ...

    def basic_get(
        self,
        queue: str,
        callback: _OnBasicGetCallback,
        auto_ack: bool = ...,
    ) -> None: ...

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
    ) -> None: ...

    def basic_qos(
        self,
        prefetch_size: int = ...,
        prefetch_count: int = ...,
        global_qos: bool = ...,
        callback: Optional[_OnBasicQosCallback] = ...,
    ) -> None: ...

    def basic_reject(self, delivery_tag: int, requeue: bool = ...) -> None: ...

    def basic_recover(
        self,
        requeue: bool = ...,
        callback: Optional[_OnBasicRecoverCallback] = ...,
    ) -> None: ...

    def close(self, reply_code: int = ..., reply_text: str = ...) -> None: ...

    def confirm_delivery(
        self,
        ack_nack_callback: _OnAckNackCallback,
        callback: Optional[_OnConfirmDeliveryCallback] = ...,
    ) -> None: ...

    @property
    def consumer_tags(self) -> List[str]: ...

    def exchange_bind(
        self,
        destination: str,
        source: str,
        routing_key: str = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        callback: Optional[_OnExchangeBindCallback] = ...,
    ) -> None: ...

    def exchange_declare(
        self,
        exchange: str,
        exchange_type: str = ...,
        passive: bool = ...,
        durable: bool = ...,
        auto_delete: bool = ...,
        internal: bool = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        callback: Optional[_OnExchangeDeclareCallback] = ...,
    ) -> None: ...

    def exchange_delete(
        self,
        exchange: Optional[str] = ...,
        if_unused: bool = ...,
        callback: Optional[_OnExchangeDeleteCallback] = ...,
    ) -> None: ...

    def exchange_unbind(
        self,
        destination: Optional[str] = ...,
        source: Optional[str] = ...,
        routing_key: str = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        callback: Optional[_OnExchangeUnbindCallback] = ...,
    ) -> None: ...

    def flow(self, active: bool, callback: Optional[_OnFlowCallback] = ...) -> None: ...

    @property
    def is_closed(self) -> bool: ...
    @property
    def is_closing(self) -> bool: ...
    @property
    def is_open(self) -> bool: ...

    def open(self) -> None: ...

    def queue_bind(
        self,
        queue: str,
        exchange: str,
        routing_key: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        callback: Optional[_OnQueueBindCallback] = ...,
    ) -> None: ...

    def queue_declare(
        self,
        queue: str,
        passive: bool = ...,
        durable: bool = ...,
        exclusive: bool = ...,
        auto_delete: bool = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        callback: Optional[_OnQueueDeclareCallback] = ...,
    ) -> None: ...

    def queue_delete(
        self,
        queue: str,
        if_unused: bool = ...,
        if_empty: bool = ...,
        callback: Optional[_OnQueueDeleteCallback] = ...,
    ) -> None: ...

    def queue_purge(
        self,
        queue: str,
        callback: Optional[_OnQueuePurgeCallback] = ...,
    ) -> None: ...

    def queue_unbind(
        self,
        queue: str,
        exchange: Optional[str] = ...,
        routing_key: Optional[str] = ...,
        arguments: Optional[Mapping[str, Any]] = ...,
        callback: Optional[_OnQueueUnbindCallback] = ...,
    ) -> None: ...

    def tx_commit(self, callback: Optional[_OnTxCommitCallback] = ...) -> None: ...
    def tx_rollback(self, callback: Optional[_OnTxRollbackCallback] = ...) -> None: ...
    def tx_select(self, callback: Optional[_OnTxSelectCallback] = ...) -> None: ...
