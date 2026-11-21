#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Compiles translation .lang templates into typescript and json assets."""

import os
import sys

# Ensure we can import stuff/i18n_linter.py
# The repository root is the parent directory of 'stuff'
REPO_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, REPO_ROOT)

# Ensure the working directory is set to the repository root
os.chdir(REPO_ROOT)

from stuff.i18n_linter import I18nLinter


def main() -> None:
    """Main compilation entrypoint."""
    linter = I18nLinter()

    def contents_callback(filename: str) -> bytes:
        try:
            with open(filename, 'rb') as f:
                return f.read()
        except FileNotFoundError:
            return b''

    # run_all returns linters.MultipleResults
    results = linter.run_all([], contents_callback)

    if not results.new_contents:
        print('All translation files are up to date.')
        return

    # Write the generated contents back
    for filename, content in results.new_contents.items():
        print(f'Writing {filename}...')
        with open(filename, 'wb') as f:
            f.write(content)


if __name__ == '__main__':
    main()
