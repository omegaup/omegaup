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
        contests = database.contest.get_contests(
            cur=cur,
            date_lower_limit=test_constants.DATE_LOWER_LIMIT,
            date_upper_limit=test_constants.DATE_UPPER_LIMIT,
        )

        assert contests == [
            {
                'alias': 'pasado',
                'certificate_cutoff': None,
                'contest_id': 2,
                'scoreboard_url': 'pNua7xzDZ3MQryrQb7qqTqnqpCaC2g',
            }
        ]
