from typing import Text, Optional

from . import connections
from . import constants

def connect(host: Text, user: Text, passwd: Text, db: Text,
            port: Optional[int] = 3306) -> connections.Connection: ...
