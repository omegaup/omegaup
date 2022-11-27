#!/usr/bin/env python3
"""Script that analyzes the MySQL type log and Psalm annotations.

This script ensures that all the Psalm annotations for MySQL query methods are
correct. It uses the MySQL type log (which is generated at runtime) after tests
are run, looks for the code locations of those MySQL method calls, and asserts
that the Psalm annotation that precedes the call has the expected type.
"""

import argparse
import collections
import logging
import re
import sys
from typing import (DefaultDict, Iterable, NamedTuple, Optional, Sequence, Set,
                    Tuple)

_RECORD_RE = re.compile(r'^([^:]+):(\d+)\s+(.*?)$')
_CALLSITE_RE = re.compile(r'.*->([gG]et(?:Row|One|All))\(')
_DOCSTRING_RE = re.compile(
    r'^\s*/\*\*\s*@var\s+([^$]+?)(?:\s*(\$.+?))?\s*\*/\s*$')


class Record(NamedTuple):
    """A single MySQL type log entry."""

    filename: str
    line_number: int
    type_name: str


class Docstring(NamedTuple):
    """Metadata about a single Psalm docstring."""

    line_number: int
    contents: str
    declared_type: str
    variable_name: Optional[str]


class CallInformation(NamedTuple):
    """Information about a single MySQL method call."""

    filename: str
    line_number: int
    method_name: str
    docstring: Optional[Docstring]


def _find_callsite(lines: Sequence[str], record: Record) -> Tuple[int, str]:
    """Find the type and location of a single MySQL method call."""

    for current_line_number in range(record.line_number - 1, -1, -1):
        match = _CALLSITE_RE.match(lines[current_line_number])
        if match:
            return current_line_number, match.group(1).lower()
    raise Exception(
        f'Callsite for {record.filename}:{record.line_number} not found')


def _get_call_information(lines: Sequence[str],
                          record: Record) -> CallInformation:
    """Get the CallInformation object for a single MySQL method call."""

    callsite_line_number, method_name = _find_callsite(lines, record)

    docstring: Optional[Docstring] = None

    # Look for the docstring up to 5 lines before the call site. This prevents
    # false positives.
    for docstring_line_number in range(callsite_line_number - 1,
                                       callsite_line_number - 5, -1):
        if (lines[docstring_line_number].endswith(';')
                or lines[docstring_line_number].endswith('}')):
            # If we reach a line that ends with a semicolon or closing brace,
            # we've gone too far.
            break
        docstring_match = _DOCSTRING_RE.match(lines[docstring_line_number])
        if docstring_match:
            docstring = Docstring(
                line_number=docstring_line_number,
                contents=lines[docstring_line_number].strip(),
                declared_type=docstring_match.group(1),
                variable_name=docstring_match.group(2) or None,
            )
            break

    return CallInformation(
        record.filename,
        callsite_line_number,
        method_name,
        docstring,
    )


def _process_records(lines: Sequence[str], records: Iterable[Record]) -> bool:
    success = True
    previous_record: Optional[Record] = None
    for record in sorted(records, key=lambda r: r.line_number):
        if (previous_record
                and previous_record.line_number == record.line_number):
            success = False
            logging.error(
                ('Conflicting type information for call in %s:%d:'
                 '\n\t%s\n\t%s'),
                record.filename,
                record.line_number,
                previous_record.type_name,
                record.type_name,
            )
            continue
        previous_record = record

        call_information = _get_call_information(lines, record)

        got_docstring = '<Missing docstring>'
        expected_docstring = ''
        if call_information.docstring:
            if record.type_name in call_information.docstring.declared_type:
                continue
            got_docstring = call_information.docstring.contents

        success = False
        if call_information.method_name == 'getall':
            if (call_information.docstring
                    and call_information.docstring.variable_name):
                expected_docstring = (
                    f'/** @var {record.type_name} '
                    f'{call_information.docstring.variable_name} */')
            else:
                expected_docstring = f'/** @var list<{record.type_name}> */'
        elif call_information.method_name == 'getrow':
            record_type = '|'.join(sorted(["null", record.type_name]))
            expected_docstring = f'/** @var {record_type} */'
        else:  # 'getone'
            if 'null' in record.type_name:
                expected_docstring = f'/** @var {record.type_name} */'
            else:
                record_type = '|'.join(sorted(["null", record.type_name]))
                expected_docstring = f'/** @var {record_type} */'

        logging.error(
            ('Mismatched docstring for call in %s:%s:'
             '\n\tExpected: %s\n\tFound:    %s'),
            record.filename,
            record.line_number,
            expected_docstring,
            got_docstring,
        )
    return success


def _main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument('logfile', type=argparse.FileType('r'))
    args = parser.parse_args()
    success = True

    # Collect all type records and group them by filename.
    type_records: DefaultDict[str, Set[Record]] = collections.defaultdict(set)
    for line in args.logfile:
        match = _RECORD_RE.match(line.strip())
        if not match:
            success = False
            logging.error('Malformed log line %r', line)
            continue
        record = Record(
            filename=match.group(1),
            line_number=int(match.group(2)),
            type_name=match.group(3),
        )
        type_records[record.filename].add(record)

    for filename, records in type_records.items():
        if '/DAO/Base/' in filename:
            # These files are autogenerated, so they don't need to be checked.
            continue
        with open(filename, encoding='utf-8') as f:
            lines = f.read().strip().split('\n')
        success &= _process_records(lines, records)
    if not success:
        sys.exit(1)


if __name__ == '__main__':
    _main()
