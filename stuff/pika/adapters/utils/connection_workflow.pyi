from typing import Any, Callable, Union

from ... import compat, connection
from . import nbio_interface


class AMQPConnectorException(Exception):
    ...


class AMQPConnectorStackTimeout(AMQPConnectorException):
    ...


class AMQPConnectorAborted(AMQPConnectorException):
    ...


class AMQPConnectorWrongState(AMQPConnectorException):
    ...


class AMQPConnectorPhaseErrorBase(AMQPConnectorException):

    exception: Any = ...

    def __init__(self, exception: Any, *args: Any) -> None: ...


class AMQPConnectorSocketConnectError(AMQPConnectorPhaseErrorBase):
    ...


class AMQPConnectorTransportSetupError(AMQPConnectorPhaseErrorBase):
    ...


class AMQPConnectorAMQPHandshakeError(AMQPConnectorPhaseErrorBase):
    ...


class AMQPConnectionWorkflowAborted(AMQPConnectorException):
    ...


class AMQPConnectionWorkflowWrongState(AMQPConnectorException):
    ...


class AMQPConnectionWorkflowFailed(AMQPConnectorException):

    exceptions: Any = ...

    def __init__(self, exceptions: Any, *args: Any) -> None: ...


class AMQPConnector:

    def __init__(self, conn_factory: Any, nbio: Any) -> None: ...
    def start(self, addr_record: Any, conn_params: Any, on_done: Any) -> None: ...
    def abort(self) -> None: ...


class AbstractAMQPConnectionWorkflow(compat.AbstractBase):

    def start(
        self,
        connection_configs: connection.Parameters,
        connector_factory: Callable[[], AMQPConnector],
        native_loop: object,
        on_done: Callable[
            [
                Union[
                    connection.Connection,
                    AMQPConnectionWorkflowFailed,
                    AMQPConnectionWorkflowAborted,
                ],
            ],
            None
        ],
    ) -> None: ...

    def abort(self) -> None: ...


class AMQPConnectionWorkflow(AbstractAMQPConnectionWorkflow):

    def __init__(self, _until_first_amqp_attempt: bool = ...) -> None: ...

    def set_io_services(self, nbio: nbio_interface.AbstractIOServices) -> None: ...

    def start(
        self,
        connection_configs: connection.Parameters,
        connector_factory: Callable[[], AMQPConnector],
        native_loop: object,
        on_done: Callable[
            [
                Union[
                    connection.Connection,
                    AMQPConnectionWorkflowFailed,
                    AMQPConnectionWorkflowAborted,
                ],
            ],
            None
        ],
    ) -> None: ...

    def abort(self) -> None: ...
