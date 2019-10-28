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
import collections
from typing import Mapping, Set, DefaultDict, Dict, Text

import MySQLdb.constants.ER

import lib.db as db
import lib.logs as logs

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
    inverse_mapping: DefaultDict[
        Text, Dict[Text, Text]] = collections.defaultdict(dict)
    for lang in LANGS:
        path = os.path.join(LOCALIZATIONS_ROOT, 'lang.%s.json' % (lang))
        with open(path, 'r') as f:
            mappings = json.load(f)
            for key, value in mappings.items():
                if key.startswith('problemTopic'):
                    new_tags.add(key)
                    inverse_mapping[lang][normalize_tag(value)] = key

    # Update problemtopictag to problemTopicTag
    # and also preserve problemTopicTags
    for tag in new_tags:
        inverse_mapping['en'][normalize_tag(tag)] = tag
        inverse_mapping['en'][tag] = tag

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
        for qualitynomination_id, json_contents in cur:
            try:
                contents = json.loads(json_contents)
            except json.JSONDecodeError:  # pylint: disable=no-member
                logging.exception(
                    'Failed to parse contents on qualitynomination %s',
                    qualitynomination_id
                )
                continue
            if 'tags' not in contents:
                continue
            canonicalized_tags = set()
            for tag in contents['tags']:
                for lang in LANGS:
                    if mapping[lang].get(tag):
                        canonicalized_tags.add(mapping[lang][tag])
                        break
            contents['tags'] = list(canonicalized_tags)
            to_update.append((json.dumps(contents), qualitynomination_id))

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
    try:
        mapping = get_inverse_mapping(dbconn)
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
