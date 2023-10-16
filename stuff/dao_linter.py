#!/usr/bin/env python3
# -*- coding: utf-8 -*-
'''The omegaUp DAO linter.'''

import importlib
import os
from typing import Optional, Sequence

from omegaup_hook_tools import linters


class DaoLinter(linters.Linter):
    '''Runs the DAO linter.'''

    # pylint: disable=R0903

    def __init__(self, options: Optional[linters.Options] = None) -> None:
        # pylint: disable=unused-argument
        super().__init__()

    def run_all(
            self, filenames: Sequence[str],
            contents_callback: linters.ContentsCallback
    ) -> linters.MultipleResults:
        '''Runs the linter against a subset of files.'''
        # pylint: disable=unused-argument

        # Given that this file may be loaded dynamically, we need to do some
        # loader hackery to get the dao_utils module loaded.
        dao_utils_module_spec = importlib.util.spec_from_file_location(
            'dao_utils',
            os.path.join(
                os.path.dirname(os.path.abspath(__file__)), 'dao_utils.py'))
        if dao_utils_module_spec is None:
            raise ModuleNotFoundError('dao_utils module not found')
        dao_utils = importlib.util.module_from_spec(dao_utils_module_spec)
        dao_utils_module_spec.loader.exec_module(dao_utils)  # type: ignore

        new_contents = {}
        original_contents = {}
        contents = contents_callback(
            'frontend/database/dao_schema.sql').decode('utf-8')
        for (filename, file_type,
             contents) in dao_utils.generate_dao(contents):  # type: ignore
            if file_type == 'dao':
                path = os.path.join('frontend/server/src/DAO/Base', filename)
            else:
                path = os.path.join('frontend/server/src/DAO/VO', filename)
            try:
                original_contents[path] = contents_callback(path)
            except FileNotFoundError:
                original_contents[path] = b''
            new_contents[path] = contents.encode('utf-8')

        return linters.MultipleResults(new_contents, original_contents,
                                       ['dao'])

    @property
    def name(self) -> str:
        '''Gets the name of the linter.'''
        return 'dao'


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
