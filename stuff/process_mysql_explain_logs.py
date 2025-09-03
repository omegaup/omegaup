#!/usr/bin/env python3
#  this script checks the MySQL log to identify inefficient
#  queries that might cause trouble in highly demanding conditions,
#  inefficient queries are determined by having an 'ALL' on the query type
'''Looking for inefficient queries in the MySQL log.'''
import logging
import sys
import os
from typing import Any, Iterable, Tuple, Optional
import re
import mysql.connector
from mysql.connector import Error  # type: ignore


def normalize_query(query: str) -> str:
    '''eliminate numeric values and strings'''
    # replace numbers
    query = re.sub(r'\b\d+\b', '?', query)
    # replace single-quoted strings
    query = re.sub(r"'[^']*'", '?', query)
    # replace double-quoted strings
    query = re.sub(r'"[^"]*"', '?', query)
    return query


# Establish connection to MySQL
def create_connection(
    host_name: str,
    port: int,
    user_name: str,
    user_password: str,
    db_name: str
) -> Optional[mysql.connector.MySQLConnection]:
    """Connect to MySQL (try env pw, then empty, then 'omegaup')."""
    host = os.getenv(
        'OMEGAUP_MYSQL_HOST',
        os.getenv('MYSQL_HOST', host_name),
    )
    port = int(
        os.getenv(
            'OMEGAUP_MYSQL_PORT',
            os.getenv('MYSQL_TCP_PORT', port),
        )
    )
    user = os.getenv('OMEGAUP_MYSQL_USER', user_name)
    db = os.getenv(
        'OMEGAUP_MYSQL_DB',
        os.getenv('MYSQL_DATABASE', db_name),
    )
    pw_env = os.getenv(
        'OMEGAUP_MYSQL_PASSWORD',
        os.getenv('MYSQL_ROOT_PASSWORD', user_password),
    )

    candidates = []
    for pw_candidate in (pw_env, '', 'omegaup'):
        if pw_candidate is None or pw_candidate in candidates:
            continue
        candidates.append(pw_candidate)

    for pw in candidates:
        try:
            conn = mysql.connector.connect(
                host=host,
                port=port,
                user=user,
                password=pw,
                database=db,
            )
            logging.warning('Connection to MySQL DB successful')
            return conn
        except Error as e:
            if getattr(e, 'errno', None) == 1045:
                continue
            logging.error("The error '%s' occurred", e)
            return None

    logging.error('Auth failed with all password attempts')
    return None


# Function to retrieve all queries from the general log
def get_queries_from_general_log(
    connection: mysql.connector.MySQLConnection
) -> Iterable[Tuple[Any, ...]]:
    '''Get querys from log'''
    cursor = connection.cursor()
    cursor.execute("""
        USE `omegaup-test`
    """)
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
    queries = cursor.fetchall()
    return queries


def explain_queries(
    connection: mysql.connector.MySQLConnection,
    queries: Iterable[Tuple[Any, ...]]
) -> bool:
    '''Run explain command on queries'''
    success = True
    cursor = connection.cursor()
    query_set = set()
    for query in queries:
        query_text = query[0]
        try:
            cursor.execute(f"EXPLAIN {query_text}")
            explain_result = cursor.fetchall()

            # Get the index of the interest columns
            column_names = [i[0] for i in cursor.description]  # type: ignore
            type_row_index = column_names.index('type')
            table_row_index = column_names.index('table')
            extra_row_index = column_names.index('Extra')
            # Add the key filter mentioned in the document
            key_row_index = (
                column_names.index('key')
                if 'key' in column_names else None
            )
            possible_keys_row_index = (
                column_names.index('possible_keys')
                if 'possible_keys' in column_names else None
            )
            check_extra = ['no matching row in const table',
                           'Using index']
            full_table_scan = 'ALL'
            exclude = ['Languages',
                       'general_log',
                       'Roles', 'Groups_',
                       'Tags', 'Countries',
                       'urc']
            inefficient_count = 0
            diagnostic = ''
            for row in explain_result:
                # Skip benign/irrelevant cases
                if str(row[extra_row_index]) in check_extra:
                    continue
                if (
                    row[table_row_index] is None
                    or "<union" in str(row[table_row_index])
                    or "<derived" in str(row[table_row_index])
                    or row[table_row_index] in exclude
                    or str(row[table_row_index]).startswith('full_')
                ):
                    continue
                if (
                    query_text.startswith('DELETE ')
                    and ' WHERE ' not in query_text
                ):
                    continue

                # Inefficient if type == 'ALL' OR
                # key is NULL/empty (when available)
                is_all = (row[type_row_index] == full_table_scan)
                key_val = (
                    row[key_row_index]
                    if key_row_index is not None else None
                )
                key_is_null = (
                    key_val is None or str(key_val).strip() == ''
                )
                if not (is_all or key_is_null):
                    continue

                inefficient_count += 1
                diag_key = str(key_val)
                diag_pkeys = (
                    str(row[possible_keys_row_index])
                    if possible_keys_row_index is not None else ''
                )
                diagnostic = (
                    diagnostic +
                    f"type={row[type_row_index]} table={row[table_row_index]} "
                    f"key={diag_key} possible_keys={diag_pkeys} "
                    f"extra={row[extra_row_index]}\n"
                )
            if inefficient_count > 0:
                query_set.add(normalize_query(query_text +
                                              '\n\ndetected problems:\n' +
                                              diagnostic))
        except Error as e:
            logging.error("Failed to explain query: %s", query_text)
            logging.error("Error: %s", e)
            success = False
    if len(query_set) > 0:
        success = False
        for clean_query in query_set:
            logging.warning(clean_query)
        logging.warning('%d inefficient queries found', len(query_set))
    return success


# Main function to handle the logic
def _main() -> None:
    '''Main function to handle the logic'''
    # Use your credentials
    connection = create_connection(host_name="mysql",
                                   port=13306,
                                   user_name="root",
                                   user_password="omegaup",
                                   db_name="omegaup-test", )
    if connection:
        queries = get_queries_from_general_log(connection)
        if queries:
            if not explain_queries(connection, queries):
                sys.exit(0)
        else:
            logging.warning("No queries found in the general log")
            sys.exit(0)
        connection.close()


if __name__ == '__main__':
    _main()
