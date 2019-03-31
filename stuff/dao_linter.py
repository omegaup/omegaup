#!/usr/bin/python3
# -*- coding: utf-8 -*-
'''The omegaUp DAO linter.'''

import importlib
import os

from hook_tools import linters


class DaoLinter(linters.Linter):
    '''Runs the DAO linter.'''

    # pylint: disable=R0903

    def __init__(self, options=None):
        # pylint: disable=unused-argument
        super().__init__()

    def run_one(self, filename, contents):
        '''Runs the linter against |contents|.'''
        # pylint: disable=no-self-use, unused-argument
        return contents, []

    def run_all(self, file_contents, contents_callback):
        '''Runs the linter against a subset of files.'''
        # pylint: disable=no-self-use, unused-argument

        dao_utils_module_spec = importlib.util.spec_from_file_location(
            'dao_utils',
            os.path.join(
                os.path.dirname(os.path.abspath(__file__)), 'dao_utils.py'))
        dao_utils = importlib.util.module_from_spec(dao_utils_module_spec)
        dao_utils_module_spec.loader.exec_module(dao_utils)

        new_contents = {}
        original_contents = {}
        for filename, contents in dao_utils.generate_dao(
                contents_callback('frontend/database/schema.sql').decode(
                    'utf-8')):
            path = os.path.join('frontend/server/libs/dao/base', filename)
            original_contents[path] = contents_callback(path)
            new_contents[path] = contents.encode('utf-8')

        return new_contents, original_contents, ['dao']

    @property
    def name(self):
        '''Gets the name of the linter.'''
        return 'dao'


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
