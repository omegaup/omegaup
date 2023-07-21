#!/usr/bin/env python3
'''Library of common database code shared across cron scripts.

Using this library consists of two parts:
- Configuring a command line parser with configure_parser.
- Getting a DB connection using arguments from the command line.
'''

import argparse
import datetime
import logging

from typing import Any, Dict

from pythonjsonlogger import jsonlogger


class _CustomJsonFormatter(jsonlogger.JsonFormatter):
    """A JSON formatter that adds the level."""

    def __init__(self) -> None:
        # TODO(https://github.com/madzak/python-json-logger/pull/170): Remove
        # the type: ignore annotation when v2.0.8 is released.
        super().__init__()  # type: ignore

    def add_fields(
            self,
            log_record: Dict[str, str],
            record: logging.LogRecord,
            message_dict: Dict[str, Any],
    ) -> None:
        """Add fields to the record."""
        super().add_fields(log_record, record, message_dict)
        if not log_record.get('time'):
            log_record['time'] = datetime.datetime.utcnow().strftime(
                '%Y-%m-%dT%H:%M:%S.%fZ')
        if log_record.get('level'):
            log_record['level'] = log_record['level'].lower()
        else:
            log_record['level'] = record.levelname.lower()


def configure_parser(parser: argparse.ArgumentParser) -> None:
    '''Add Logging-related arguments to `parser`'''
    logging_args = parser.add_argument_group('Logging')
    logging_args.add_argument('--quiet',
                              '-q',
                              action='store_true',
                              help='Disables logging')
    logging_args.add_argument('--verbose',
                              '-v',
                              action='store_true',
                              help='Enables verbose logging')
    logging_args.add_argument('--log-json',
                              action='store_true',
                              help='Log with JSON')
    logging_args.add_argument('--logfile',
                              type=str,
                              default=None,
                              help='Enables logging to a file')


def init(program: str, args: argparse.Namespace) -> None:
    '''Initializes the logging module using arguments from the command line

    Args:
       program (str): The name of the program for logging purposes.
       args (argparse.Namespace): Arguments resulting from parsing the command
                                  line with a parser configured with
                                  `configure_parser`.
    '''
    log_level = (logging.DEBUG if args.verbose else
                 logging.INFO if not args.quiet else logging.ERROR)
    if args.log_json:
        if args.logfile:
            log_handler: logging.Handler = logging.FileHandler(args.logfile)
        else:
            log_handler = logging.StreamHandler()
        formatter = _CustomJsonFormatter()
        log_handler.setFormatter(formatter)
        logging.basicConfig(level=log_level,
                            handlers=[log_handler],
                            force=True)
    else:
        logging.basicConfig(filename=args.logfile or '',
                            format='%%(asctime)s:%s:%%(message)s' % program,
                            level=log_level)


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
