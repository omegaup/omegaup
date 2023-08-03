#!/usr/bin/env python3

'''Refreshes all libinteractive template packages.'''

import os
import re
import logging
import subprocess
import tempfile

_PROBLEMS_GIT_DIR = '/var/lib/omegaup/problems.git'
_TEMPLATES_DIR = '/var/www/omegaup.com/templates'
_LIBINTERACTIVE_PATH = '/usr/share/java/libinteractive.jar'
_LS_TREE_RE = re.compile(br'(\d+) (\w+) ([0-9a-f]+)\t([^\x00]*)\x00')


def generate(alias: str) -> None:
    '''Generate libinteractive templates for one problem.'''

    tree = subprocess.check_output(['/usr/bin/git', 'ls-tree', '-r', '-z',
                                    'HEAD:interactive/'],
                                   cwd=os.path.join(_PROBLEMS_GIT_DIR, alias))
    idlname = None
    with tempfile.TemporaryDirectory(
            prefix='refresh_libinteractive_') as dirname:
        for match in _LS_TREE_RE.finditer(tree):
            _, objtype, _, raw_filename = match.groups()
            if objtype != b'blob':
                continue
            filename = raw_filename.decode('utf-8')
            if filename.endswith('.idl'):
                idlname = filename
            elif (not filename.startswith('Main.')
                  and not filename.startswith('examples/')):
                continue
            outpath = os.path.join(dirname, filename)
            os.makedirs(os.path.dirname(outpath), exist_ok=True)
            with open(outpath, 'wb') as outfile:
                subprocess.check_call(['/usr/bin/git', 'cat-file', 'blob',
                                       f'HEAD:interactive/{filename}'],
                                      cwd=os.path.join(_PROBLEMS_GIT_DIR,
                                                       alias),
                                      stdout=outfile)
        if not idlname:
            logging.error('Could not find an idl for %s', alias)
            return
        try:
            subprocess.check_call(['/usr/bin/java', '-jar',
                                   _LIBINTERACTIVE_PATH, 'generate-all',
                                   idlname, '--package-directory',
                                   os.path.join(_TEMPLATES_DIR, alias),
                                   '--package-prefix', f'{alias}_',
                                   '--shift-time-for-zip'], cwd=dirname)
        except subprocess.CalledProcessError:
            logging.exception('Failed to generate the packages for %s', alias)


def _main() -> None:
    '''Main entrypoint.'''

    for alias in os.listdir(_TEMPLATES_DIR):
        logging.info('Refreshing files for problem %s...', alias)
        generate(alias)


if __name__ == '__main__':
    logging.getLogger().setLevel('INFO')
    _main()
