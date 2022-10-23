#!/usr/bin/env python3

from typing import List
import pytest
import argparse
import calendar
import collections
import datetime
import json
import logging
import operator
import sys
import os
sys.path.insert(
    0,
    os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
                 "."))
import lib.db  # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position
 
from plagiarism_detector import get_contests # can only import if in the same directory.

def main() -> None:

    parser = argparse.ArgumentParser(
        description='Runs the Plagiarism Detector')
    parser.add_argument('--local-downloader-dir')

    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)

    args = parser.parse_args()
    args.verbose = True
    lib.logs.init(parser.prog, args)

    logging.info('started')
    dbconn = lib.db.connect(lib.db.DatabaseConnectionArguments.from_args(args))

def test_get_contests(dbconn: lib.db.Connection) -> None:
    assert get_contests(dbconn) == [] 
    # dbconn not found error 

    '''
    fixture 'dbconn' not found
>       available fixtures: cache, capfd, capfdbinary, caplog,
        capsys, capsysbinary, class_mocker, doctest_namespace,
        mocker, module_mocker, monkeypatch, package_mocker, 
        pytestconfig, record_property, record_testsuite_property,
        record_xml_attribute, recwarn, session_mocker, stub, tmp_path,
        tmp_path_factory, tmpdir, tmpdir_factory, workspace
>       use 'pytest --fixtures [testpath]' for help on them.
    '''