#!/usr/bin/python3

'''Ensures the usage of just canonical tags on quality nominations.

This script reads all quality nominations suggestions contents
and ensures that only canonical tags are used.
'''

import argparse
import json
import logging
import os
import unicodedata
import re
from typing import Mapping, Set

import MySQLdb.constants.ER

import cron.lib.db as db
import cron.lib.logs as logs

LOCALIZATIONS_ROOT = os.path.abspath(os.path.join(__file__,
                                                  '..', '..',
                                                  'frontend/www/js/omegaup'))
LANGS = ['en', 'es', 'pt']


def normalize_tag(tag: str) -> str:
    '''Normalizes tags, similar to Tags::normalize() in PHP'''
    # Remove empty spaces
    tag = tag.strip()
    # Remove accents
    tag = unicodedata.normalize('NFD',
                                tag).encode('ascii', 'ignore').decode("utf-8")
    # Use '-' for splitting if necessary
    tag = re.sub(r'[^a-z0-9]+', '-', tag.lower())
    return tag


def insert_new_tags(tags: Set[str],
                    dbconn: MySQLdb.connections.Connection) -> None:
    '''Inserts new problem tags inside Tags table on DB'''
    logging.info('Inserting new Tags on database')
    with dbconn.cursor() as cur:
        cur.executemany('''INSERT IGNORE INTO `Tags`(`name`)
                        VALUES (%s);''',
                        [(tag,) for tag in tags])


def get_inverse_mapping(
        dbconn: MySQLdb.connections.Connection) -> Mapping[
            str, Mapping[str, str]]:
    '''Gets the inverse mapping for problem tags entries in lang files'''
    logging.info('Getting tags mapping from lang files.')
    new_tags = set()
    inverse_mapping = {}
    for lang in LANGS:
        path = os.path.join(LOCALIZATIONS_ROOT, 'lang.%s.json' % (lang))
        with open(path, 'r') as f:
            mappings = json.load(f)
            new_mapping = {}
            for key, value in mappings.items():
                if key.startswith('problemTopic'):
                    normalized_tag = normalize_tag(key)
                    new_tags.add(normalized_tag)
                    new_mapping[normalize_tag(value)] = normalized_tag
            inverse_mapping[lang] = new_mapping
    insert_new_tags(new_tags, dbconn)
    return inverse_mapping


def migrate_tags(dbconn: MySQLdb.connections.Connection,
                 mapping: Mapping[str, Mapping[str, str]]) -> None:
    '''Reads all suggestions and modifies their tags if necessary'''
    with dbconn.cursor() as cur:
        cur.execute('''SELECT `qualitynomination_id`, `contents`
                        FROM `QualityNominations`
                        WHERE `nomination` = 'suggestion';''')
        to_update = []
        for row in cur:
            try:
                contents = json.loads(row[1])
            except json.JSONDecodeError:  # pylint: disable=no-member
                logging.exception('Failed to parse contents')
                continue
            if not contents.get('tags'):
                continue
            nomination_id = row[0]
            canonicalized_tags = set()
            for tag in contents['tags']:
                for lang in LANGS:
                    if mapping[lang].get(tag):
                        canonicalized_tags.add(mapping[lang][tag])
                        break
            contents['tags'] = list(canonicalized_tags)
            to_update.append((json.dumps(contents), nomination_id))

        # Now update records
        cur.executemany('''UPDATE `QualityNominations`
                            SET `contents` = %s
                            WHERE `qualitynomination_id` = %s''',
                        to_update)
    logging.info('Feedback problem tags updated.')


def main() -> None:
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
            mapping = get_inverse_mapping(dbconn)
        except:  # noqa: bare-except
            logging.exception('Failed to get mapping from lang json files')
            raise
        migrate_tags(dbconn, mapping)
        dbconn.commit()
    except:  # noqa: bare-except
        logging.exception('Failed to migrate canonical tags.')
    finally:
        dbconn.close()
        logging.info('Finished')


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
