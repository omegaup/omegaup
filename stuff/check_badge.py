#!/usr/bin/python3
# This program is intended to be invoked from the console, not to be used as a
# module.

'''
Script for the validation and processing of badges.
'''


import argparse
import json
import logging
import os


OMEGAUP_BADGES_ROOT = os.path.abspath(os.path.join(__file__, '..', '..',
                                                   'frontend/badges'))
_MAX_BADGE_SIZE = 15 * 1024
ICON_FILE = 'icon.svg'
LOCALIZATIONS_FILE = 'localizations.json'
QUERY_FILE = 'query.sql'
TEST_FILE = 'test.json'


def verify_badge(alias: str):
    '''Validates and processes badge information'''
    logging.info('Badge -> %s', alias)
    try:
        path = os.path.join(OMEGAUP_BADGES_ROOT, alias, ICON_FILE)
        filesize = os.stat(path).st_size
        if filesize > _MAX_BADGE_SIZE:
            raise ValueError('El tamaño de icon.svg es mayor a 15KB')
    except OSError:
        logging.warning('No se encontró %s, se usará el default.',
                        ICON_FILE)
    # SVG must be OK or not exist for this to pass.

    try:
        path = os.path.join(OMEGAUP_BADGES_ROOT, alias, LOCALIZATIONS_FILE)
        # Opens localizations json and adds the entries to:
        # /frontend/templates/es.lang
        # /frontend/templates/en.lang
        # /frontend/templates/pt.lang
        with open(path, 'r') as f:
            localizations = json.load(f)
            keys = ('en', 'es', 'pt')
            sub_keys = ('name', 'description')
            for key in keys:
                if key not in localizations:
                    raise AttributeError('No existe localizations["%s"].' %
                                         key)
                for subkey in sub_keys:
                    if subkey not in localizations[key]:
                        error_msg = ('No existe "%s" en localizations["%s"]' %
                                     (subkey, key))
                        raise AttributeError(error_msg)
            # add_entries_to_templates()
            logging.info('Las entradas serán cargadas a los archivos .lang')
    except OSError:
        logging.error('No se encontró %s', LOCALIZATIONS_FILE)
        raise

    if not os.path.isfile(os.path.join(OMEGAUP_BADGES_ROOT, alias,
                                       QUERY_FILE)):
        raise OSError('No se encontró el archivo %s' % QUERY_FILE)

    if not os.path.isfile(os.path.join(OMEGAUP_BADGES_ROOT, alias,
                                       TEST_FILE)):
        raise OSError('No se encontró el archivo %s' % TEST_FILE)
    logging.info('Los archivos .lang han sido actualizados')


def main():
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser()
    parser.add_argument('--badge', help='The badge to be analyzed')
    args = parser.parse_args()

    logging.basicConfig(format='%%(asctime)s:%s:%%(message)s' % parser.prog,
                        level=(logging.DEBUG))

    if args.badge is not None:
        try:
            verify_badge(args.badge)
        except:  # noqa: bare-except
            logging.exception('Failed to verify badge %s', args.badge)
            raise


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
