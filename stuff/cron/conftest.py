'''Shared pytest setup for the cron test suite.

Puts `stuff/` on the import path so the tests can import the cron modules and
their helpers through the `cron` package (`cron.update_ranks`, `cron.utils`,
...) and the shared `lib.*` modules, without repeating the path boilerplate in
every test file.

Only `stuff/` is added on purpose: adding `stuff/cron` would expose its
`database` package under the bare name `database`, shadowing the unrelated
`database` package used by the stuff/pipelines tests.
'''
import os
import sys

_STUFF_DIR = os.path.dirname(os.path.dirname(os.path.realpath(__file__)))

if _STUFF_DIR not in sys.path:
    sys.path.insert(0, _STUFF_DIR)
