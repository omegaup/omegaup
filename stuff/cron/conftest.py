'''Adds `stuff/` to the path so tests import cron modules as `cron.*`.

Only `stuff/` is added; `stuff/cron` would shadow the pipelines `database`.
'''
import os
import sys

_STUFF_DIR = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))

if _STUFF_DIR not in sys.path:
    sys.path.insert(0, _STUFF_DIR)
