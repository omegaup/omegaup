import asyncio
from typing import Callable, Optional, Sequence, Union

from .. import connection
from . import base_connection
from .utils import connection_workflow

_OnCloseCallback = Callable[['AsyncioConnection', Exception], None]
_OnOpenCallback = Callable[['AsyncioConnection'], None]
_OnOpenErrorCallback = Callable[['AsyncioConnection', Union[str, Exception]], None]


class AsyncioConnection(base_connection.BaseConnection[asyncio.AbstractEventLoop]):

    def __init__(
        self,
        parameters: Optional[connection.Parameters],
        on_open_callback: Optional[_OnOpenCallback],
        on_open_error_callback: Optional[_OnOpenErrorCallback],
        on_close_callback: Optional[_OnCloseCallback],
        custom_ioloop: Optional[asyncio.AbstractEventLoop] = ...,
        internal_connection_workflow: bool = ...,
    ) -> None: ...

    @classmethod
    def create_connection(
        cls,
        connection_configs: Sequence[connection.Parameters],
        on_done: Callable[
            [
                Union[
                    connection.Connection,
                    connection_workflow.AMQPConnectionWorkflowFailed,
                    connection_workflow.AMQPConnectionWorkflowAborted,
                ],
            ],
            None
        ],
        custom_ioloop: Optional[asyncio.AbstractEventLoop] = ...,
        workflow: Optional[connection_workflow.AbstractAMQPConnectionWorkflow] = ...,
    ) -> connection_workflow.AbstractAMQPConnectionWorkflow: ...
