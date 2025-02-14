#!/usr/bin/env python3

'''Scrcript to test the omegaUp API.'''

import argparse
import logging
import os
import sys

import omegaup.api

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position



def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--api-token', type=str, help='omegaup api token')
    parser.add_argument('--url',
                        type=str,
                        help='omegaup api URL',
                        default='https://omegaup.com')
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    logging.info('Started')
    client = omegaup.api.Client(api_token=args.api_token, url=args.url)
    logging.info('Client created')
    problems = client.problem.list(
        only_quality_seal=True,
    )
    logging.info('Problems: %s', problems)


if __name__ == '__main__':
    main()
