from __future__ import annotations

import ssl
from socket import socket
from typing import Any, Callable, Optional, Tuple, Union

from . import nbio_interface


class SocketConnectionMixin:

    def connect_socket(
        self,
        sock: socket,
        resolved_addr: Any,
        on_done: Callable[[Optional[BaseException]], None],
    ) -> nbio_interface.AbstractIOReference: ...


class StreamingConnectionMixin:

    def create_streaming_connection(
        self,
        protocol_factory: Callable[[], nbio_interface.AbstractStreamProtocol],
        sock: socket,
        on_done: Callable[
            [
                Union[
                    BaseException,
                    Tuple[
                        nbio_interface.AbstractStreamTransport,
                        nbio_interface.AbstractStreamProtocol,
                    ],
                ]
            ],
            None,
        ],
        ssl_context: Optional[ssl.SSLContext],
        server_hostname: Optional[str],
    ) -> nbio_interface.AbstractIOReference: ...
