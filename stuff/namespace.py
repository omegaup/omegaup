#!/usr/bin/env python3
"""Utility to aid in PHP namespace migration."""

import argparse
import re
import subprocess

from typing import Iterable


def _find_files(search: str) -> Iterable[str]:
    return [
        x.decode('utf-8') for x in subprocess.check_output([
            '/usr/bin/git', 'grep', '--null',
            '--files-with-matches', '--perl-regexp', r'(?<!\\)\b{}\b'.format(
                re.escape(search)), '--', '*.php'
        ]).strip(b'\x00').split(b'\x00')
    ]


def _sed(filename: str, search: str, fqcn: str) -> None:
    with open(filename, encoding='utf-8') as f:
        original_contents = f.read()
    contents = original_contents
    contents = re.sub(r'(?<!\\)\b{}(?=::|\()'.format(re.escape(search)), fqcn,
                      contents)
    contents = re.sub(
        r'(extends|instanceof|catch|@[a-zA-Z]+) {}\b'.format(
            re.escape(search)), r'\1 {}'.format(fqcn), contents)
    contents = re.sub(r': *(\?)?{} {{'.format(re.escape(search)),
                      r': \1{} {{'.format(fqcn), contents)
    contents = re.sub(r'(?<!\\)\b{} \$'.format(re.escape(search)),
                      r'{} $'.format(fqcn), contents)
    contents = re.sub(r'(?<=[?|])\b{}'.format(re.escape(search)), fqcn,
                      contents)
    if contents == original_contents:
        return
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(contents)


def _main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument('search')
    parser.add_argument('namespace')
    parser.add_argument('--rename-class', type=str)
    args = parser.parse_args()

    for filename in _find_files(args.search):
        _sed(
            filename, args.search, r'\\{}\\{}'.format(
                args.namespace, args.rename_class or args.search))


if __name__ == '__main__':
    _main()
