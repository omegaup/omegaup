#!/usr/bin/env python3
"""
Decodes JavaScript stack traces.

Uses the source mapping file to get the original source location for a
webpacked JavaScript file.  Source Map documentation can be found at
https://docs.google.com/document/d/1U1RGAehQwRypUTovF1KRlpiOFze0b-_2gc6fAH0KY0k/edit?pli=1#!
"""

import argparse
import bisect
import hashlib
import json
import os
import re
import urllib.parse
import urllib.request

from typing import Any, Dict, List, Mapping, Optional, Tuple, Union

_BLINK_STACK_FRAME_RE = re.compile(r'^(.*?) \((.*):(\d+):(\d+)\)$')
_GECKO_STACK_FRAME_RE = re.compile(r'^(.*?)@(.*):(\d+):(\d+)$')
_SOURCE_MAPPING_RE = re.compile(r'^//[@#] sourceMappingURL\s*=\s*(.*)$')
_BASE64_MAPPING = \
    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'


def _mangle_url(url: str) -> str:
    return hashlib.sha1(url.encode('utf-8')).hexdigest()


def _download(url: str) -> str:
    if url.startswith('/'):
        return url
    filename = os.path.join('.sources', _mangle_url(url))
    if not os.path.exists(filename):
        with urllib.request.urlopen(url) as req:
            with open(filename, 'wb') as fo:
                fo.write(req.read())
    return filename


def _parse_b64vlq(s: str) -> Tuple[List[int], int]:
    """Parses a Base64 Variable Length Quantity."""
    result: List[int] = []
    value = 0
    idx = 0
    shift = 0
    while idx < len(s):
        current = _BASE64_MAPPING.find(s[idx])
        if current == -1:
            assert value == 0 and shift == 0
            return result, idx
        value |= (current & 0x1f) << shift
        if current & 0x20:
            shift += 5
        else:
            if value & 1:
                sign = -1
            else:
                sign = 1
            result.append(sign * (value >> 1))
            value = 0
            shift = 0
        idx += 1
    assert value == 0 and shift == 0
    return result, idx


def _get_mapping(mapping_filename: str) -> Mapping[str, Any]:
    with open(mapping_filename, 'r', encoding='utf-8') as f:
        mapping_obj: Dict[str, Any] = json.load(f)
    encoded_mappings = mapping_obj['mappings']
    i = 0
    generated_line = 1
    generated_column = 1
    source_index = 0
    original_line = 1
    original_column = 1
    name_index = 0

    mappings: List[Tuple[Tuple[int, int], Union[Tuple[int, int, int],
                                                Tuple[int, int, int,
                                                      int]]]] = []

    while i < len(encoded_mappings):
        if encoded_mappings[i] == ';':
            generated_line += 1
            generated_column = 1
            i += 1
            continue
        if encoded_mappings[i] == ',':
            i += 1
            continue
        result, inc = _parse_b64vlq(encoded_mappings[i:])
        i += inc

        if len(result) >= 1:
            generated_column += result[0]
        if len(result) >= 2:
            source_index += result[1]
        if len(result) >= 3:
            original_line += result[2]
        if len(result) >= 4:
            original_column += result[3]
        if len(result) >= 5:
            name_index += result[4]
            mappings.append(
                ((generated_line, generated_column),
                 (source_index, original_line, original_column, name_index)))
        else:
            mappings.append(((generated_line, generated_column),
                             (source_index, original_line, original_column)))
    mapping_obj['mappings'] = mappings
    return mapping_obj


def _map_source(url: str, lineno: str, colno: str) -> str:
    if not os.path.isdir('.sources'):
        os.mkdir('.sources')
    source_filename = _download(url)
    mapping_filename: Optional[str] = None
    parsed: Optional[urllib.parse.ParseResult] = None
    path: Optional[str] = None
    try:
        parsed = urllib.parse.urlparse(url)
        path = f'frontend/www{parsed.path}'
    except ValueError:
        path = source_filename
    with open(source_filename, 'r', encoding='utf-8') as f:
        for line in f:
            match = _SOURCE_MAPPING_RE.match(line)
            if not match:
                continue
            if parsed:
                mapping_url = urllib.parse.urljoin(url, match.group(1))
                if parsed.query:
                    mapping_url += '?' + parsed.query
                mapping_filename = _download(mapping_url)
            else:
                mapping_filename = os.path.join(
                    os.path.dirname(source_filename), match.group(1))
    if mapping_filename:
        mapping = _get_mapping(mapping_filename)
        pos = (int(lineno), int(colno))
        generated_pos, original_mapping = mapping['mappings'][
            bisect.bisect_left(mapping['mappings'], (pos, ))]
        if generated_pos != pos:
            print(generated_pos, pos)
        source = mapping['sources'][original_mapping[0]]
        return f'{source}:{original_mapping[1]}:{original_mapping[2]}'
    return f'{path}:{lineno}:{colno}'


def _main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument('stack', type=argparse.FileType('r'))
    args = parser.parse_args()

    with args.stack as f:
        for line in f:
            for regex in (_BLINK_STACK_FRAME_RE, _GECKO_STACK_FRAME_RE):
                match = regex.match(line)
                if match:
                    source_map = _map_source(*match.groups()[1:])
                    print(f'{match.group(1)} ({source_map})')
                    break
            else:
                print(line.rstrip('\n'))


if __name__ == '__main__':
    _main()
