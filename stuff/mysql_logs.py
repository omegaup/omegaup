#!/usr/bin/env python3
'''Looking for inefficient queries in the MySQL log.'''
#  #from typing import Iterable, Tuple
from typing import Any, Iterable, Tuple
import mysql.connector
from mysql.connector import Error  # type: ignore


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
    for query in queries:
        query_text = query[0]
        try:
            cursor.execute(f"EXPLAIN {query_text}")
            explain_result = cursor.fetchall()

            # Get the index of the 'possible_keys' column
            column_names = [i[0] for i in cursor.description]  # type: ignore
            possible_keys_index = column_names.index('possible_keys')
            type_row_index = column_names.index('type')
            table_row_index = column_names.index('table')
            extra_row_index = column_names.index('Extra')

            # Check if any row has NULL or empty possible_keys
            no_matching_row = 'no matching row in const table'
            exclude = ['Languages',
                       'general_log',
                       'Roles', 'Groups_',
                       'Tags',
                       'urc']
            for row in explain_result:
                if str(row[extra_row_index]) == no_matching_row:
                    continue
                if row[type_row_index] != 'ALL':
                    continue
                if str(row[extra_row_index]) == 'Using index':
                    continue
                if (row[table_row_index] is None
                    or "<union" in row[table_row_index]
                    or "<derived" in row[table_row_index]
                    or row[table_row_index] in exclude):
                    continue
                if (query_text.startswith('DELETE ') and
                    ' WHERE ' not in query_text):
                    continue
                if (str("Users u") in query_text and
                    str("verification_id = ") in query_text):
                    continue
                if (str("Users_Badges") in query_text and
                    str("badge_alias = ") in query_text):
                    continue
                query_count += 1
                print(f"Found query with full table scan: {query_text}")
                print(query_text.split()[0],
                      row[table_row_index],
                      row[extra_row_index],
                      row[type_row_index],
                      row[possible_keys_index])
        except Error as e:
            print(f"Failed to explain query: {query_text}")
            print(f"Error: {e}")
    print(query_count, " inefficient queries found")


# Main function to handle the logic
def main() -> None:
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


if __name__ == "__main__":
    main()

#
