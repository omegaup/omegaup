'''Shared pytest setup for the cron test suite.

Puts `stuff/` on the import path so the tests can import the cron modules as
`cron.*` and `lib.*` without repeating the path boilerplate in every file.
'''
import os
import sys

_STUFF_DIR = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))

if _STUFF_DIR not in sys.path:
    sys.path.insert(0, _STUFF_DIR)
