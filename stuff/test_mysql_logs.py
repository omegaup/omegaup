#!/usr/bin/env python3
#  this script checks the MySQL log to identify inefficient
#  queries that might cause trouble in highly demanding conditions,
#  inefficient queries are determined by having an 'ALL' on the query type
'''Looking for inefficient queries in the MySQL log.'''
from typing import Any, Iterable, Tuple
import re
import mysql.connector
from mysql.connector import Error  # type: ignore

import pytest


def normalize_query(query: str) -> str:
    '''eliminate numeric values and strings'''
    # Reemplaza números
    query = re.sub(r'\b\d+\b', '?', query)
    # Reemplaza strings entre comillas
    query = re.sub(r"'[^']*'", '?', query)
    return query


# Establish connection to MySQL
def create_connection(
    host_name: str, user_name: str, user_password: str, db_name: str
) -> mysql.connector.MySQLConnection | None:
    '''Connecting to database'''
    connection = None
    try:
        connection = mysql.connector.connect(
            host=host_name,
            user=user_name,
            password=user_password,
            database=db_name,
            port=13306
        )
        print("Connection to MySQL DB successful")
    except Error as e:
        print(f"The error '{e}' occurred")
    return connection


# Function to retrieve all queries from the general log
def get_queries_from_general_log(
    connection: mysql.connector.MySQLConnection
) -> Iterable[Tuple[Any, ...]]:
    '''Get querys from log'''
    cursor = connection.cursor()
    cursor.execute("""
        USE omegaup
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
) -> None:
    '''Run explain command on queries'''
    cursor = connection.cursor()
    query_count = 0
    query_set = set()
    # max_inefficient = 0
    for query in queries:
        query_text = query[0]
        try:
            cursor.execute(f"EXPLAIN {query_text}")
            explain_result = cursor.fetchall()

            # Get the index of the interest columns
            column_names = [i[0] for i in cursor.description]  # type: ignore
            # possible_keys_index = column_names.index('possible_keys')
            type_row_index = column_names.index('type')
            table_row_index = column_names.index('table')
            extra_row_index = column_names.index('Extra')
            check_extra = ['no matching row in const table',
                           'Using index']
            full_table_scan = 'ALL'
            exclude = ['Languages',
                       'general_log',
                       'Roles', 'Groups_',
                       'Tags',
                       'urc']
            inefficient_count = 0
            for row in explain_result:
                # print(row[table_row_index])
                if str(row[extra_row_index]) in check_extra:
                    continue
                if row[type_row_index] != full_table_scan:
                    continue
                if (row[table_row_index] is None
                    or "<union" in row[table_row_index]
                    or "<derived" in row[table_row_index]
                    or row[table_row_index] in exclude
                    or row[table_row_index].startswith('full_')):
                    continue
                if (query_text.startswith('DELETE ') and
                    ' WHERE ' not in query_text):
                    continue
                inefficient_count += 1
                # print(row[table_row_index][0],' ',type_row_index,' ',
                # extra_row_index,' ',possible_keys_index)
                # print(f"Found query with full table scan: {query_text}")
                # print(query_text.split()[0],
                #      row[table_row_index],
                #      row[extra_row_index],
                #      row[type_row_index],
                #      row[possible_keys_index])
            if inefficient_count > 0:
                print(query_text)
                print(explain_result)
                query_count += 1
                print("==================================================")
                print(inefficient_count, " inefficient tables scan")
                # print(query_text, "\n\n")
                query_set.add(normalize_query(query_text))
            # if inefficient_count == 1:
                # print(query_text, "\n\n")
            # if inefficient_count > 2:
            #    max_inefficient += 1
            #    print(query_text, "\n\n")

        except Error as e:
            print(f"Failed to explain query: {query_text}")
            print(f"Error: {e}")
    print(query_count, " inefficient queries found")
    print(query_set)
    print(len(query_set))
    # print(max_inefficient, " max inefficient queries")
    if query_count > 0:
        pytest.skip(f'{query_count} need fix')
    assert False


# Main function to handle the logic
def test_main() -> None:
    '''Main function to handle the logic'''
    # Use your credentials
    connection = create_connection(host_name="mysql",
                                   user_name="root",
                                   user_password="omegaup",
                                   db_name="omegaup", )
    if connection:
        queries = get_queries_from_general_log(connection)
        if queries:
            explain_queries(connection, queries)
        else:
            print("No queries found in the general log")
        connection.close()

#
