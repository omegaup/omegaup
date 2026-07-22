'''Shared pytest setup for the cron test suite.

Puts `stuff/` and `stuff/cron` on the import path so the tests can import the
cron modules (`cron.*`, `lib.*`) and the scripts' own sibling imports
(`database.*`, `utils`) without repeating the path boilerplate in every file.
'''
import os
import sys

_CRON_DIR = os.path.dirname(os.path.realpath(__file__))
_STUFF_DIR = os.path.dirname(_CRON_DIR)

for _path in (_STUFF_DIR, _CRON_DIR):
    if _path not in sys.path:
        sys.path.insert(0, _path)
