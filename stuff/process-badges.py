#!/usr/bin/python3
# pylint: disable=invalid-name
# This program is intended to be invoked from the console, not to be used as a
# module.

'''
Script for the validation and processing of badges.
'''


import argparse
import json
import logging
import os


OMEGAUP_BADGES_ROOT = (os.path.abspath(os.path.join(__file__, '..', '..'))
                       + '/frontend/badges/')


def process_badge(alias):
    '''Validates and processes badge information'''
    logging.info('Badge %s:', alias)
    try:
        path = OMEGAUP_BADGES_ROOT + alias + '/icon.svg'
        filesize = os.stat(path).st_size
        if (filesize / 1024 > 15.0):
            logging.warn('El tamaño de icon.svg excede los 15KB.')
        return False
    except:  # noqa: bare-except
        logging.exception('No se pudo abrir icon.svg')
        # build_svg()
    # SVG must be OK for this to pass.

    try:
        path = OMEGAUP_BADGES_ROOT + alias + '/localizations.json'
        with open(path, 'r') as f:
            localizations = json.load(f)
            if (localizations['es']['name']
                and localizations['es']['description']
                and localizations['en']['name']
                and localizations['en']['description']
                and localizations['pt']['name']
                and localizations['pt']['description']):
                logging.info('Agregaré las localizaciones.')
                # add_localization_entries()
            else:
                logging.warn('El archivo localizations.json es incorrecto.')
            return False
    except:  # noqa: bare-except
        logging.exception('No se pudo abrir localizations.json')

    if not os.path.isfile(OMEGAUP_BADGES_ROOT + alias + 'tests.json'):
        logging.warn('No ha sido encontrado el archivo test.json')
        return False

    if not os.path.isfile(OMEGAUP_BADGES_ROOT + alias + 'query.sql'):
        logging.warn('No ha sido encontrado el archivo query.sql')
        return False

    return True


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
        if process_badge(alias):
            logging.info('%s ha sido correctamente agregado/actualizado.\n',
                         alias)
        else:
            logging.warn('%s no pudo ser agregado/actualizado.\n', alias)


if __name__ == '__main__':
    main()
