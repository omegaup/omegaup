#!/usr/bin/env python3
'''Library of common database code shared across cron scripts.

Using this library consists of two parts:
- Configuring a command line parser with configure_parser.
- Getting a DB connection using arguments from the command line.
'''

import argparse
import contextlib
import datetime
import logging
import time

from typing import Any, Dict, Iterator

from pythonjsonlogger import jsonlogger  # type: ignore


class _CustomJsonFormatter(jsonlogger.JsonFormatter):  # type: ignore
    """A JSON formatter that adds the level."""

    def add_fields(
            self,
            log_data: Dict[str, str],
            record: logging.LogRecord,
            message_dict: Dict[str, Any],
    ) -> None:
        """Add fields to the record."""
        super().add_fields(log_data, record, message_dict)
        if not log_data.get('time'):
            log_data['time'] = datetime.datetime.utcnow().strftime(
                '%Y-%m-%dT%H:%M:%S.%fZ')
        if log_data.get('level'):
            log_data['level'] = log_data['level'].lower()
        else:
            log_data['level'] = record.levelname.lower()


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
                            format=f'%(asctime)s:{program}:%(message)s',
                            level=log_level)


@contextlib.contextmanager
def log_phase(phase: str) -> Iterator[Dict[str, Any]]:
    '''Logs a cron phase start and finish as structured fields.

    Emits `phase`, `status`, `duration_ms` and `error_class` on failure. The
    yielded dict can hold extra fields (e.g. row counts) added to the finish
    line. The exception is re-raised so callers keep their error handling.
    '''
    metrics: Dict[str, Any] = {}
    start = time.monotonic()
    logging.info('%s started', phase, extra={'phase': phase})
    try:
        yield metrics
    except Exception as exc:
        logging.error(
            '%s failed',
            phase,
            extra={
                'phase': phase,
                'status': 'failed',
                'duration_ms': round((time.monotonic() - start) * 1000),
                'error_class': type(exc).__name__,
                **metrics,
            },
        )
        raise
    logging.info(
        '%s completed',
        phase,
        extra={
            'phase': phase,
            'status': 'ok',
            'duration_ms': round((time.monotonic() - start) * 1000),
            **metrics,
        },
    )


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
