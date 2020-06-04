#!/usr/bin/python3

'''Ensures the usage of just canonical tags on quality nominations.

This script reads all quality nominations suggestions contents
and ensures that only canonical tags are used.
'''

import argparse
import json
import logging
import os
import re
import unicodedata
import warnings
from typing import Mapping, Set, DefaultDict, Dict, Text

import MySQLdb.constants.ER

import lib.db as db
import lib.logs as logs

MAPPINGS = {
    # 'problemCategoryAlgorithmAndNetworkOptimization': ''
    'problemCategoryCompetitiveProgramming':
        'problemAdvancedLevelCompetitiveProgramming',
    'problemCategoryElementaryDataStructures':
        'problemIntermediateLevelDataStructuresAndAlgorithms',
    'problemCategoryIntroductionToProgramming':
        'problemBasicLevelIntroductionToProgramming',
    'problemCategoryKarelEducation':
        'problemBasicLevelKarel',
    'problemCategoryMathematicalProblems':
        'problemIntermediateLevelMathsInProgramming',
    # 'problemCategoryOpenResponse': ''
    'problemCategorySpecializedTopics':
        'problemAdvancedLevelSpecializedTopics',
}

DELETABLE_TAGS = [
    'problemCategoryAlgorithmAndNetworkOptimization',
    'problemCategoryOpenResponse',
]

NEW_TAG = 'problemIntermediateLevelAlgorithmsAnalysisAndDesign'


def setup_levels_tags(dbconn: MySQLdb.connections.Connection) -> None:
    '''Inserts new level tags, updates and deletes old category tags'''
    logging.info('Setting up level tags on database')
    with dbconn.cursor() as cur:
        cur.execute(
            '''
                DELETE FROM `Problems_Tags` WHERE `source` = `quality`
            '''
        )

        cur.execute(
            '''
                INSERT IGNORE INTO `Tags`(`name`) VALUES (%s)
            ''', (NEW_TAG))

        for key, value in MAPPINGS.items():
            # Falta eliminar esas asociaciones de tags con problemas
            # Problems_Tags
            cur.execute(
                '''
                    UPDATE
                        `Tags`
                    SET
                        `name` = %s
                    WHERE
                        `name` = %s;
                ''', (value, key))

        for item in DELETABLE_TAGS:
            cur.execute(
                '''
                    DELETE FROM
                        `Tags`
                    WHERE
                        `name` = %s;
                ''', (item))


# def standardize_tags(dbconn: MySQLdb.connections.Connection) -> None:
#     '''Reads quality_tag suggestions and updates or deletes them'''
#     with dbconn.cursor() as cur:
#         cur.execute('''
#             SELECT `qualitynomination_id`, `contents`
#             FROM `QualityNominations`
#             WHERE `nomination` = 'quality_tag';
#         ''')
#         to_update = []
#         for qualitynomination_id, json_contents in cur:
#             try:
#                 contents = json.loads(json_contents)
#             except json.JSONDecodeError:  # pylint: disable=no-member
#                 logging.exception(
#                     'Failed to parse contents on qualitynomination %s',
#                     qualitynomination_id
#                 )
#                 continue
#             if 'tags' not in contents:
#                 continue
#             canonicalized_tags = set()
#             for tag in contents['tags']:
#                 for lang in LANGS:
#                     if mapping[lang].get(tag):
#                         canonicalized_tags.add(mapping[lang][tag])
#                         break
#             contents['tags'] = list(canonicalized_tags)
#             to_update.append((json.dumps(contents), qualitynomination_id))

#         # Now update records
#         cur.executemany(
#             '''
#                 UPDATE `QualityNominations`
#                 SET `contents` = %s
#                 WHERE `qualitynomination_id` = %s;
#             ''',
#             to_update)
#     logging.info('Feedback problem tags updated.')


def main() -> None:
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(
        description='Tags standardization.')

    db.configure_parser(parser)
    logs.configure_parser(parser)

    args = parser.parse_args()
    logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = db.connect(args)
    warnings.filterwarnings('ignore', category=dbconn.Warning)
    try:
        setup_levels_tags(dbconn)
        # standardize_tags(dbconn)
        dbconn.commit()
    except:  # noqa: bare-except
        logging.exception('Failed to standardize tags.')
    finally:
        dbconn.close()
        logging.info('Finished')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
