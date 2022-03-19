#!/usr/bin/python3

'''test verification_code module.'''

import os
import sys

import credentials
import database.contest
import test_constants

sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))
import lib.db   # pylint: disable=wrong-import-position


def test_get_contest_contestants() -> None:
    '''Test get contest contestants'''

    dbconn = lib.db.connect(
        lib.db.DatabaseConnectionArguments(
            user=credentials.MYSQL_USER,
            password=credentials.MYSQL_PASSWORD,
            host=credentials.MYSQL_HOST,
            database=credentials.MYSQL_DATABASE,
            port=credentials.MYSQL_PORT,
            mysql_config_file=lib.db.default_config_file_path() or ''
        )
    )
    with dbconn.cursor(buffered=True, dictionary=True) as cur:
        contestants = database.contest.get_contest_contestants(
            cur=cur,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
        )

        for data in contestants:
            if data['contest_id'] == 1:
                assert data['certificate_cutoff'] == 2
            if data['contest_id'] == 2:
                assert data['certificate_cutoff'] == 3
