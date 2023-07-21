#!/usr/bin/env python3
# pylint: disable=invalid-name
# This program is intended to be invoked from the console, not to be used as a
# module.
'''A tool that calls the DAO updater.'''

from __future__ import print_function

import argparse
import os

import dao_utils

_OMEGAUP_ROOT = os.path.abspath(os.path.join(__file__, '..', '..'))


def _main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        '--script',
        type=argparse.FileType('r'),
        default=os.path.join(_OMEGAUP_ROOT, 'frontend/database/schema.sql'),
    )
    args = parser.parse_args()

    for dao in dao_utils.generate_dao(args.script.read()):
        if dao.file_type == 'dao':
            filename = os.path.join(
                _OMEGAUP_ROOT, 'frontend/server/src/DAO/Base', dao.filename)
        else:
            filename = os.path.join(_OMEGAUP_ROOT,
                                    'frontend/server/src/DAO/VO', dao.filename)
        with open(filename, 'w', encoding='utf-8') as f:
            f.write(dao.contents)


if __name__ == '__main__':
    _main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
