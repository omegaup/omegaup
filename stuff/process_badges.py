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


def process_badge(alias: str):
    '''Validates and processes badge information'''
    logging.info('BADGE %s', alias)
    try:
        path = os.path.join(OMEGAUP_BADGES_ROOT, alias, 'icon.svg')
        filesize = os.stat(path).st_size
        if filesize > _MAX_BADGE_SIZE:
            raise ValueError('El tamaño de icon.svg es mayor a 15KB')
    except OSError:
        logging.warning('No se encontró icon.svg, se usará el default')
    # SVG must be OK or not exist for this to pass.

    try:
        path = os.path.join(OMEGAUP_BADGES_ROOT, alias, 'localizations.json')
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
        logging.error('No se encontró localizations.json')
        raise

    if not os.path.isfile(os.path.join(OMEGAUP_BADGES_ROOT, alias,
                                       'query.sql')):
        raise OSError('No se encontró el archivo query.sql')

    if not os.path.isfile(os.path.join(OMEGAUP_BADGES_ROOT, alias,
                                       'test.json')):
        raise OSError('No se encontró el archivo test.json')
    # run_test_for_badge()
    logging.info('%s ha sido correctamente agregado/actualizado.\n', alias)


def main():
    '''Main entrypoint.'''
    parser = argparse.ArgumentParser()
    parser.add_argument('--verbose', action='store_true')
    args = parser.parse_args()

    if args.verbose:
        logging.getLogger().setLevel('DEBUG')

    # Get all subfolders in /frontend/badges/
    aliases = [f.name for f in os.scandir(OMEGAUP_BADGES_ROOT)
               if f.is_dir()]
    for alias in aliases:
        process_badge(alias)


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
