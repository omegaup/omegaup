#!/usr/bin/python

import glob
import os
import re

REGEX = re.compile(r'^\s*(.*?)\s*=\s*(.*)\s*$')

for lang in glob.glob('frontend/templates/*.lang'):
    name = os.path.basename(lang)
    name = os.path.splitext(name)[0]
    target = 'frontend/www/js/lang.%s.js' % name
    with open(target, 'w') as dst:
        dst.write('OmegaUp.T = {\n')
        with open(lang, 'r') as src:
            for line in src:
                match = REGEX.match(line)
                if match:
                    dst.write('\t%s: %s,\n' % match.groups(1))
        dst.write('};\n')
