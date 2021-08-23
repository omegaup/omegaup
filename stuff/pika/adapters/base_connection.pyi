import abc
from typing import Callable, Generic, Optional, Sequence, TypeVar, Union

from .. import connection
from .utils import connection_workflow, nbio_interface

_OnCloseCallback = Callable[['BaseConnection', Exception], None]
_OnOpenCallback = Callable[['BaseConnection'], None]
_OnOpenErrorCallback = Callable[['BaseConnection', Union[str, Exception]], None]

_IOLoop = TypeVar('_IOLoop')


class BaseConnection(Generic[_IOLoop], connection.Connection):

    def __init__(
        self,
        parameters: Optional[connection.Parameters],
        on_open_callback: Optional[_OnOpenCallback],
        on_open_error_callback: Optional[_OnOpenErrorCallback],
        on_close_callback: Optional[_OnCloseCallback],
        nbio: nbio_interface.AbstractIOServices,
        internal_connection_workflow: bool,
    ) -> None: ...

    @classmethod
    @abc.abstractmethod
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
        custom_ioloop: Optional[_IOLoop] = ...,
        workflow: Optional[connection_workflow.AbstractAMQPConnectionWorkflow] = ...,
    ) -> connection_workflow.AbstractAMQPConnectionWorkflow: ...

    @property
    def ioloop(self) -> _IOLoop: ...
