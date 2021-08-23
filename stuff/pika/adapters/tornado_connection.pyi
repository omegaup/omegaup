from typing import Callable, Optional, Sequence, Union

import tornado.ioloop

from .. import connection
from . import base_connection
from .utils import connection_workflow, nbio_interface

_OnCloseCallback = Callable[['TornadoConnection', Exception], None]
_OnOpenCallback = Callable[['TornadoConnection'], None]
_OnOpenErrorCallback = Callable[['TornadoConnection', Union[str, Exception]], None]


class TornadoConnection(base_connection.BaseConnection[tornado.ioloop.IOLoop]):

    def __init__(
        self,
        parameters: Optional[connection.Parameters],
        on_open_callback: Optional[_OnOpenCallback],
        on_open_error_callback: Optional[_OnOpenErrorCallback],
        on_close_callback: Optional[_OnCloseCallback],
        custom_ioloop: Optional[
            Union[
                tornado.ioloop.IOLoop,
                nbio_interface.AbstractIOServices,
            ],
        ] = ...,
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
        custom_ioloop: Optional[tornado.ioloop.IOLoop] = ...,
        workflow: Optional[connection_workflow.AbstractAMQPConnectionWorkflow] = ...,
    ) -> connection_workflow.AbstractAMQPConnectionWorkflow: ...
