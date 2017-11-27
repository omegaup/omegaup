#!/usr/bin/python3

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

def generate(alias):
    '''Generate libinteractive templates for one problem.'''

    tree = subprocess.check_output(['/usr/bin/git', 'ls-tree', '-r', '-z',
                                    'HEAD:interactive/'],
                                   cwd=os.path.join(_PROBLEMS_GIT_DIR, alias))
    idlname = None
    with tempfile.TemporaryDirectory(prefix='refresh_libinteractive_') as dirname:
        for match in _LS_TREE_RE.finditer(tree):
            _, objtype, _, filename = match.groups()
            if objtype != b'blob':
                continue
            filename = str(filename, encoding='utf-8')
            if filename.endswith('.idl'):
                idlname = filename
            outpath = os.path.join(dirname, filename)
            os.makedirs(os.path.dirname(outpath), exist_ok=True)
            with open(outpath, 'wb') as outfile:
                subprocess.check_call(['/usr/bin/git', 'cat-file', 'blob',
                                       'HEAD:interactive/%s' % filename],
                                      cwd=os.path.join(_PROBLEMS_GIT_DIR, alias),
                                      stdout=outfile)
        if not idlname:
            logging.error('Could not find an idl for %s', alias)
            return
        try:
            subprocess.check_call(['/usr/bin/java', '-jar',
                                   _LIBINTERACTIVE_PATH, 'generate-all',
                                   idlname, '--package-directory',
                                   os.path.join(_TEMPLATES_DIR, alias),
                                   '--package-prefix', '%s_' % alias,
                                   '--shift-time-for-zip'], cwd=dirname)
        except:
            logging.exception('Failed to generate the packages for %s', alias)


def main():
    '''Main entrypoint.'''

    for alias in os.listdir(_TEMPLATES_DIR):
        logging.info('Refreshing files for problem %s...', alias)
        generate(alias)

if __name__ == '__main__':
    logging.getLogger().setLevel('INFO')
    main()
