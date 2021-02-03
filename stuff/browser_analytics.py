#!/usr/bin/python3
"""Analyze browser usage from Google Analytics.

In order to use this tool, export a .csv report of browsers (Audience >
Technology > Browser & OS), with Secondary dimension of Browser Version.

The mappings of some browser versions to their equivalent Chromium version may
need to be maintained every now and then.
"""

import argparse
import collections
import csv
import dataclasses

from typing import Callable, DefaultDict, List, Sequence, TextIO, Tuple


@dataclasses.dataclass
class Browser:
    """A Browser version"""
    name: str = ''
    version: str = ''
    users: int = 0
    users_share: float = 0


def _parse_report(report: TextIO,
                  column: str) -> Tuple[Browser, List[Browser]]:
    # pylint: disable=too-many-branches,too-many-statements
    csv_lines: List[str] = []
    # Strip the header. It consists of a series of lines that start with #
    # followed by an empty line.
    for line in report:
        if line.strip():
            continue
        break
    # Parse the contents.
    for line in report:
        line = line.strip()
        if not line:
            break
        csv_lines.append(line)

    browser_mapping: DefaultDict[Tuple[str, str],
                                 Browser] = collections.defaultdict(Browser)

    reader = csv.DictReader(csv_lines)
    totals = Browser(name='Total', users_share=1.)
    for row in reader:
        version = row['Browser Version'].split('.')[0]
        if not version.isnumeric():
            version = ''
        name = row['Browser']
        if name == 'Edge' and version >= '79':
            # Edge started using Chromium since version 79.
            name = 'Chrome'
        elif name == 'Android Webview' and version >= '36':
            # Android started using Chromium since Lollipop / version 36.
            name = 'Chrome'
        elif name == 'UC Browser':
            chromium_version_mapping = {
                '12': '57',
            }
            if version in chromium_version_mapping:
                name = 'Chrome'
                version = chromium_version_mapping[version]
        elif name == 'Samsung Internet':
            chromium_version_mapping = {
                '4': '44',
                '5': '51',
                '6': '56',
                '7': '59',
                '8': '63',
                '9': '67',
                '10': '71',
                '11': '75',
                '12': '79',
            }
            if version in chromium_version_mapping:
                name = 'Chrome'
                version = chromium_version_mapping[version]
        elif name == 'Opera':
            chromium_version_mapping = {
                '47': '48',
                '50': '63',
                '51': '64',
                '52': '65',
                '53': '66',
                '54': '67',
                '55': '68',
                '56': '69',
                '57': '70',
                '58': '71',
                '59': '72',
                '60': '73',
                '61': '74',
                '62': '75',
                '63': '76',
                '64': '77',
                '65': '78',
                '66': '79',
                '67': '80',
                '68': '80',
                '69': '83',
            }
            if version in chromium_version_mapping:
                name = 'Chrome'
                version = chromium_version_mapping[version]
        elif name == 'YaBrowser':
            chromium_version_mapping = {
                '20': '83',
            }
            if version in chromium_version_mapping:
                name = 'Chrome'
                version = chromium_version_mapping[version]
        elif name == 'Safari':
            # Some versions of Safari report the WebKit version, not the Safari
            # one.
            if version == '602':
                version = '10'
            if version == '604':
                version = '11'
            if version == '605':
                version = '11'
        key = (name, version)
        if key == ('', ''):
            # This is the totals row
            continue
        value = int(row[column].replace(',', ''))
        browser_mapping[key].users += value
        totals.users += value

    for (name, version), browser in browser_mapping.items():
        browser.name = name
        browser.version = version
        browser.users_share = browser.users / totals.users

    return totals, list(browser_mapping.values())


def _is_filtered(browser: Browser, ignore: Sequence[str]) -> bool:
    for descriptor in ignore:
        op_mapping: Sequence[Tuple[str, Callable[[int, int], bool]]] = (
            ('<=', lambda a, b: a <= b),
            ('=', lambda a, b: a == b),
            ('<', lambda a, b: a < b),
        )
        for op, fn in op_mapping:
            if op not in descriptor:
                continue
            name, version = descriptor.split(op)
            if browser.name == name and fn(int(browser.version), int(version)):
                return True
        if browser.name == descriptor:
            return True
    return False


def _main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--ignore',
                        default=[
                            'Android Browser',
                            'Android Runtime',
                            'Android Webview<36',
                            'Chrome<51',
                            'Firefox<68',
                            'Hexometer',
                            'Internet Explorer',
                            'Opera Mini',
                            'Safari<12',
                            'Samsung Internet<4',
                            '[FBAN',
                        ],
                        type=str,
                        nargs='*',
                        help='Ignore browser')
    parser.add_argument('--column', default='Users')
    parser.add_argument('--sort-by-share', action='store_true')
    parser.add_argument('report',
                        type=argparse.FileType('r'),
                        metavar='REPORT.CSV',
                        help='An exported .csv from Google Analytics')
    args = parser.parse_args()

    totals, browsers = _parse_report(args.report, args.column)

    if args.sort_by_share:
        browsers.sort(key=lambda b: b.users, reverse=True)
    else:
        browsers.sort(key=lambda b: (b.name, b.version))

    cumulative = 0.
    print(f'{"Browser name":20} {"Version":7} '
          f'{"Users":>6} {"Share%":>7} {"Cmltiv%":>7} ')
    print('=' * 51)
    for browser in browsers:
        if _is_filtered(browser, args.ignore):
            continue
        cumulative += browser.users
        print(f'{browser.name:20} {browser.version:>7} '
              f'{browser.users:6} '
              f'{browser.users_share*100:6.2f}% '
              f'{cumulative/totals.users*100:6.2f}%')
    print('=' * 51)
    print(f'{totals.name:20} {totals.version:>7} '
          f'{totals.users:6} '
          f'{totals.users_share*100:6.2f}% '
          f'{cumulative/totals.users*100:6.2f}%')


if __name__ == '__main__':
    _main()
