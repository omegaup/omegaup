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
    logging.info('BADGE %s:', alias)
    try:
        path = OMEGAUP_BADGES_ROOT + alias + '/icon.svg'
        filesize = os.stat(path).st_size
        if filesize / 1024 > 15.0:
            logging.warning('El tamaño de icon.svg excede los 15KB.')
        return False
    except:  # noqa: bare-except
        logging.exception('No se encontró icon.svg')
        # build_svg()
    # SVG must be OK for this to pass.

    try:
        path = OMEGAUP_BADGES_ROOT + alias + '/localizations.json'
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
                    logging.warning('No existe localizations[%s].',
                                    key)
                    return False
                for sub_key in sub_keys:
                    if sub_key not in localizations[key]:
                        logging.warning('No existe %s en localizations[%s]')
                        return False
            # add_entries_to_templates()
            logging.info('Las entradas serán cargadas a los archivos .lang')
    except:  # noqa: bare-except
        logging.exception('No se pudo abrir localizations.json')

    if not os.path.isfile(OMEGAUP_BADGES_ROOT + alias + '/query.sql'):
        logging.warning('No ha sido encontrado el archivo query.sql')
        return False

    if not os.path.isfile(OMEGAUP_BADGES_ROOT + alias + '/test.json'):
        logging.warning('No ha sido encontrado el archivo test.json')
        return False
    # run_test_for_badge()
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
            logging.warning('%s no pudo ser agregado/actualizado.\n', alias)


if __name__ == '__main__':
    main()

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
