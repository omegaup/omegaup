#!/usr/bin/env python3
#  this script checks the MySQL log to identify inefficient
#  queries that might cause trouble in highly demanding conditions,
#  inefficient queries are determined by having an 'ALL' on the query type
'''Looking for inefficient queries in the MySQL log.'''
import logging
import sys
import os
import csv
from typing import Any, Iterable, Tuple, Optional, List, Dict, Set
import configparser
import re
import mysql.connector
import yaml
from mysql.connector import Error  # type: ignore
from tqdm import tqdm  # type: ignore


ALLOWLIST_VERSION = 1
ALLOWLIST_ENTRY_FIELDS = {
    'normalized_query',
    'table',
    'issue',
    'reason',
}
DEFAULT_ALLOWLIST_PATH = os.path.join(
    os.path.dirname(__file__),
    'inefficient_queries_allowlist.yml',
)


class AllowlistValidationError(ValueError):
    '''Raised when the inefficient queries allowlist is not valid.'''


def normalize_query(query: str) -> str:
    '''
    Return a normalized version of the query for grouping and printing.

    Replaces:
    - numbers with '?'
    - single-quoted and double-quoted strings with '?'
    - consecutive whitespace with a single space
    '''
    query = re.sub(r'\b\d+\b', '?', query)
    query = re.sub(r"'[^']*'", '?', query)
    query = re.sub(r'"[^"]*"', '?', query)
    query = re.sub(r'\s+', ' ', query)
    return query.strip()


def build_query_family(normalized_query: str) -> str:
    '''Return a broader representation used only to group CSV rows.'''
    return re.sub(
        r'\bIN\s*\(\s*\?(?:\s*,\s*\?)*\s*\)',
        'IN (?)',
        normalized_query,
        flags=re.IGNORECASE,
    )


def _get_non_empty_string(
    entry: Dict[str, Any],
    field: str,
    entry_number: int,
) -> str:
    '''Return a required string field from an allowlist entry.'''
    value = entry.get(field)
    if not isinstance(value, str) or not value.strip():
        raise AllowlistValidationError(
            f'Allowlist entry {entry_number} field "{field}" must be '
            'a non-empty string.'
        )
    if value != value.strip():
        raise AllowlistValidationError(
            f'Allowlist entry {entry_number} field "{field}" must not '
            'have leading or trailing whitespace.'
        )
    return value


def load_allowlist(path: str) -> List[Dict[str, Any]]:
    '''Load and validate the inefficient queries allowlist.'''
    try:
        with open(path, 'r', encoding='utf-8') as allowlist_file:
            data = yaml.safe_load(allowlist_file)
    except FileNotFoundError as exc:
        raise FileNotFoundError(
            f'Allowlist file not found: {path}'
        ) from exc
    except yaml.YAMLError as exc:
        raise AllowlistValidationError(
            f'Invalid YAML in allowlist {path}: {exc}'
        ) from exc

    if not isinstance(data, dict):
        raise AllowlistValidationError(
            'Allowlist must be a mapping with "version" and "entries".'
        )

    expected_top_level_fields = {'version', 'entries'}
    if set(data) != expected_top_level_fields:
        raise AllowlistValidationError(
            'Allowlist must contain exactly the fields "version" and '
            '"entries".'
        )

    version = data['version']
    if (
        not isinstance(version, int)
        or isinstance(version, bool)
        or version != ALLOWLIST_VERSION
    ):
        raise AllowlistValidationError(
            f'Allowlist version must be {ALLOWLIST_VERSION}.'
        )

    entries = data['entries']
    if not isinstance(entries, list):
        raise AllowlistValidationError(
            'Allowlist field "entries" must be a list.'
        )

    validated_entries: List[Dict[str, Any]] = []
    seen: Set[Tuple[str, str]] = set()
    for entry_number, entry in enumerate(entries, start=1):
        if not isinstance(entry, dict):
            raise AllowlistValidationError(
                f'Allowlist entry {entry_number} must be a mapping.'
            )
        if set(entry) != ALLOWLIST_ENTRY_FIELDS:
            raise AllowlistValidationError(
                f'Allowlist entry {entry_number} must contain exactly: '
                'normalized_query, table, issue, reason.'
            )

        normalized_query = _get_non_empty_string(
            entry,
            'normalized_query',
            entry_number,
        )
        if normalize_query(normalized_query) != normalized_query:
            raise AllowlistValidationError(
                f'Allowlist entry {entry_number} field '
                '"normalized_query" must already be normalized.'
            )

        table = _get_non_empty_string(entry, 'table', entry_number)
        _get_non_empty_string(entry, 'reason', entry_number)

        issue = entry['issue']
        if (
            not isinstance(issue, int)
            or isinstance(issue, bool)
            or issue <= 0
        ):
            raise AllowlistValidationError(
                f'Allowlist entry {entry_number} field "issue" must be '
                'a positive integer.'
            )

        identity = (normalized_query, table)
        if identity in seen:
            raise AllowlistValidationError(
                f'Allowlist entry {entry_number} duplicates the identity '
                f'{identity}.'
            )
        seen.add(identity)
        validated_entries.append(entry)

    return validated_entries


def build_inefficiency_key(
    result: Dict[str, str],
) -> Tuple[str, str]:
    '''Return the stable identity of an inefficient EXPLAIN row.'''
    return (result['Normalized Query'], result['Table'])


def deduplicate_results(
    results: Iterable[Dict[str, str]],
) -> List[Dict[str, str]]:
    '''Remove duplicate query-table results while preserving their order.'''
    seen: Set[Tuple[str, str]] = set()
    deduplicated: List[Dict[str, str]] = []
    for result in results:
        key = build_inefficiency_key(result)
        if key in seen:
            continue
        seen.add(key)
        deduplicated.append(result)
    return deduplicated


def sort_results_for_csv(
    results: Iterable[Dict[str, str]],
) -> List[Dict[str, str]]:
    '''Sort results so similar query families are adjacent in the CSV.'''
    return sorted(
        results,
        key=lambda result: (
            result['Query Family'],
            result['Normalized Query'],
            result['Table'],
            int(result['Query ID']),
        ),
    )


def create_connection(
    host_name: str,
    port: int,
    user_name: str,
    user_password: str,
    db_name: str
) -> Optional[mysql.connector.MySQLConnection]:
    """
    Open a MySQL Connection
    """
    defaults: Dict[str, str] = {}
    config_file = os.getenv('OMEGAUP_MYSQL_CONFIG_FILE')
    if not config_file:
        config_file = os.path.join(os.path.expanduser('~'), '.my.cnf')
    if os.path.isfile(config_file):
        parser = configparser.ConfigParser()
        parser.read(config_file)
        if parser.has_section('client'):
            defaults = dict(parser['client'])

    host = os.getenv(
        'OMEGAUP_MYSQL_HOST',
        os.getenv('MYSQL_HOST', defaults.get('host', host_name)),
    )
    port = int(
        os.getenv(
            'OMEGAUP_MYSQL_PORT',
            os.getenv('MYSQL_TCP_PORT', defaults.get('port', port)),
        )
    )
    user = os.getenv('OMEGAUP_MYSQL_USER', defaults.get('user', user_name))
    db = os.getenv(
        'OMEGAUP_MYSQL_DB',
        os.getenv('MYSQL_DATABASE', db_name),
    )
    pw_env = os.getenv(
        'OMEGAUP_MYSQL_PASSWORD',
        os.getenv(
            'MYSQL_ROOT_PASSWORD',
            defaults.get('password', user_password)
        ),
    )

    try:
        conn = mysql.connector.connect(
            host=host,
            port=port,
            user=user,
            password=pw_env,
            database=db,
        )
        return conn
    except Error as e:
        logging.error("The error '%s' occurred", e)
        return None


def get_queries_from_general_log(
    connection: mysql.connector.MySQLConnection
) -> List[str]:
    '''
    Fetch SELECT/UPDATE/DELETE statements from log
    '''
    cursor = connection.cursor()
    try:
        cursor.execute("""
            SELECT CONVERT(argument USING utf8) AS logs
            FROM mysql.general_log
            WHERE argument IS NOT NULL
            AND (
                argument LIKE "SELECT%" OR
                argument LIKE "UPDATE%" OR
                argument LIKE "DELETE%"
                )
        """)
        rows = cursor.fetchall()
        return [r[0] for r in rows]
    except Error as e:
        logging.error("The error '%s' occurred", e)
        return []
    finally:
        cursor.close()


def explain_queries(
    connection: mysql.connector.MySQLConnection,
    queries: Iterable[Tuple[Any, ...]]
) -> List[Dict[str, str]]:
    """
    Run EXPLAIN for each query and detect inefficiencies.

    Marks a query inefficient if any EXPLAIN row meets:
    - type == 'ALL'  OR  key is NULL/empty

    Excludes small/irrelevant tables:
    - Languages, Roles, Groups_, Tags, Countries, general_log, urc
    """
    results: List[Dict[str, str]] = []
    cursor = connection.cursor()
    query_id_map: Dict[str, int] = {}

    queries_list = list(queries)
    progress_bar = tqdm(
        total=len(queries_list),
        desc="Processing queries",
        unit="query",
        mininterval=0.5,
    )
    try:
        for query in queries_list:
            query_text = query[0]
            normalized_query = normalize_query(query_text)
            if query_text not in query_id_map:
                query_id_map[query_text] = len(query_id_map) + 1

            try:
                cursor.execute(f"EXPLAIN {query_text}")
                explain_result = cursor.fetchall()

                desc = cursor.description  # type: ignore[attr-defined]
                column_names = [i[0] for i in desc]
                type_idx = column_names.index('type')
                table_idx = column_names.index('table')
                extra_idx = column_names.index('Extra')
                key_idx = (
                    column_names.index('key')
                    if 'key' in column_names else None
                )
                possible_keys_idx = (
                    column_names.index('possible_keys')
                    if 'possible_keys' in column_names else None
                )

                check_extra = [
                    'no matching row in const table',
                    'Using index',
                ]
                exclude = [
                    'Languages',
                    'general_log',
                    'Roles',
                    'Groups_',
                    'Tags',
                    'Countries',
                    'urc',
                ]

                for row in explain_result:
                    extra_val = str(row[extra_idx])
                    if extra_val in check_extra:
                        continue

                    table_name = str(row[table_idx]) if row[table_idx] else ''
                    if (
                        not table_name
                        or '<union' in table_name
                        or '<derived' in table_name
                        or table_name in exclude
                        or table_name.startswith('full_')
                    ):
                        continue

                    if (
                        query_text.startswith('DELETE ')
                        and ' WHERE ' not in query_text
                    ):
                        continue

                    is_all = (row[type_idx] == 'ALL')
                    key_val = row[key_idx] if key_idx is not None else None
                    key_is_null = (
                        key_val is None or str(key_val).strip() == ''
                    )
                    if not (is_all or key_is_null):
                        continue

                    results.append({
                        "Query ID": str(query_id_map[query_text]),
                        "Query": query_text,
                        "Normalized Query": normalized_query,
                        "Query Family": build_query_family(normalized_query),
                        "Table": table_name,
                        "Type": str(row[type_idx]),
                        "Key": "" if key_val is None else str(key_val),
                        "Possible Keys": (
                            "" if possible_keys_idx is None
                            else str(row[possible_keys_idx])
                        ),
                        "Extra": extra_val,
                    })
            except Error as e:
                logging.error("Failed to explain query: %s", query_text)
                logging.error("Error: %s", e)

            progress_bar.update(1)

    finally:
        cursor.close()
        progress_bar.close()

    return deduplicate_results(results)


def save_to_csv(results: List[Dict[str, str]]) -> Optional[str]:
    """
    Save results to stuff/inefficient_queries.csv (UTF-8).
    Returns the file path on success, None on error.
    """
    try:
        os.makedirs("stuff", exist_ok=True)
        path = "stuff/inefficient_queries.csv"
        fieldnames = [
            "Query ID",
            "Query",
            "Normalized Query",
            "Query Family",
            "Table",
            "Type",
            "Key",
            "Possible Keys",
            "Extra",
        ]
        with open(path, "w", newline="", encoding="utf-8") as f:
            writer = csv.DictWriter(f, fieldnames=fieldnames)
            writer.writeheader()
            writer.writerows(sort_results_for_csv(results))
        return path
    except Error as exc:
        logging.error("Failed to save CSV: %s", exc)
        return None


def _main() -> None:
    """
    Main function to handle the logic.
    """
    try:
        load_allowlist(DEFAULT_ALLOWLIST_PATH)
    except (OSError, AllowlistValidationError) as exc:
        logging.error('Failed to load allowlist: %s', exc)
        sys.exit(1)

    connection = create_connection(
        host_name="mysql",
        port=13306,
        user_name="root",
        user_password="",
        db_name="omegaup-test",
    )
    if connection is None:
        logging.error("Could not connect to MySQL")
        sys.exit(1)

    try:
        queries_raw = get_queries_from_general_log(connection)
        if not queries_raw:
            logging.warning("No queries found in the general log")
            sys.exit(0)

        queries_list = [(q,) for q in queries_raw]
        rows = explain_queries(connection, queries_list)

        if rows:
            try:
                saved = save_to_csv(rows)
                logging.warning(
                    "%d inefficient query-table pairs; saved to %s",
                    len(rows),
                    saved
                )
            except (OSError, ValueError) as exc:
                logging.error("Failed to save CSV: %s", exc)
        else:
            logging.warning("0 inefficient queries")

        sys.exit(0)
    finally:
        connection.close()


if __name__ == '__main__':
    _main()
