#!/usr/bin/python3

'''Ensures the usage of just canonical tags on quality nominations.

This script reads all quality nominations suggestions contents
and ensures that only canonical tags are used.
'''

import argparse
import json
import logging
import os
from typing import Dict

import unicodedata
import re

import MySQLdb

import cron.lib.db as db
import cron.lib.logs as logs

LOCALIZATIONS_ROOT = os.path.abspath(os.path.join(__file__, '..',
                                    '..', 'frontend/www/js/omegaup'))
LANGS = ['en', 'es', 'pt']


def normalize_tag(tag):
    # Remove empty spaces
    tag = tag.strip()
    # Remove accents
    tag = unicodedata.normalize('NFD', tag).encode('ascii', 'ignore')\
            .decode("utf-8")
    # Use '-' for splitting if necessary
    tag = re.sub(r'[^a-z0-9]', '-', tag.lower())
    tag = re.sub(r'--+', '-', tag)
    return tag


def get_inverse_mapper():
    inverse_mapper = {}
    for lang in LANGS:
        path = os.path.join(LOCALIZATIONS_ROOT, 'lang.%s.json' % (lang))
        with open(path, 'r') as f:
            mappings = json.load(f)
            inverse_mapping = {}
            for k, v in mappings.items():
                if k.startswith('problemTopic'):
                    inverse_mapping[normalize_tag(v)] = normalize_tag(k)
            inverse_mapper[lang] = inverse_mapping
    return inverse_mapper


def migrate_tags(cur: MySQLdb.cursors.DictCursor,
                 mapper: Dict[str, Dict[str, str]]):
    '''Reads all suggestions and modify their tags if necessary'''
    cur.execute('''SELECT qn.`qualitynomination_id`, qn.`contents`
                    FROM `QualityNominations` as qn
                    WHERE `nomination` = 'suggestion';''')
    for row in cur:
        logging.info(row)


def main():
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser(
        description='Migrate canonical tags.')

    db.configure_parser(parser)
    logs.configure_parser(parser)

    args = parser.parse_args()
    logs.init(parser.prog, args)

    logging.info('Started')
    dbconn = db.connect(args)
    # warnings.filterwarnings('ignore', category=dbconn.Warning)
    try:
        try:
            mapper = get_inverse_mapper()
        except:
            logging.exception('Failed to get mapper from lang json files')
            raise

        with dbconn.cursor(cursorclass=MySQLdb.cursors.DictCursor) as cur:
            migrate_tags(cur, mapper)
        dbconn.commit()
    except:
        logging.exception('Failed to migrate canonical tags.')
    finally:
        dbconn.close()
        logging.info('Finished')

if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
