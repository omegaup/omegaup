import builtins


class MySQLError(Exception):
  pass


class Warning(builtins.Warning, MySQLError):
  pass
