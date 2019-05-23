#!/usr/bin/python3

'''Library of common database code shared across cron scripts.

Using this library consists of two parts:
- Configuring a command line parser with configure_parser.
- Getting a DB connection using arguments from the command line.
'''

import logging


def configure_parser(parser):
    '''Add Logging-related arguments to `parser`'''
    logging_args = parser.add_argument_group('Logging')
    logging_args.add_argument('--quiet', '-q', action='store_true',
                              help='Disables logging')
    logging_args.add_argument('--verbose', '-v', action='store_true',
                              help='Enables verbose logging')
    logging_args.add_argument('--logfile', type=str, default=None,
                              help='Enables logging to a file')


def init(program, args):
    '''Initializes the logging module using arguments from the command line

    Args:
       program (str): The name of the program for logging purposes.
       args (argparse.Namespace): Arguments resulting from parsing the command
                                  line with a parser configured with
                                  `configure_parser`.
    '''
    logging.basicConfig(filename=args.logfile,
                        format='%%(asctime)s:%s:%%(message)s' % program,
                        level=(logging.DEBUG if args.verbose else
                               logging.INFO if not args.quiet else
                               logging.ERROR))


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
