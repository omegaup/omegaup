#!/usr/bin/env python3
#  this script checks the MySQL log to identify inefficient
#  queries that might cause trouble in highly demanding conditions,
#  inefficient queries are determined by having an 'ALL' on the query type
'''Looking for inefficient queries in the MySQL log.'''
from typing import Any, Dict, Iterable, Tuple
import mysql.connector
from mysql.connector import Error  # type: ignore
from tqdm import tqdm  # type: ignore
import pandas as pd  # type: ignore


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
    connection: mysql.connector.MySQLConnection,
) -> Iterable[Tuple[Any, ...]]:
    '''Get queries from log'''
    cursor = connection.cursor()
    cursor.execute(""" USE omegaup """)
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


# Function to run EXPLAIN on queries and store results
def explain_queries(
        connection: mysql.connector.MySQLConnection,
        queries: Iterable[Tuple[Any, ...]]) -> list[dict[str, Any]]:
    '''Run explain command on queries and store results'''
    cursor = connection.cursor()
    results = []
    query_id_map: Dict[str, int] = {}

    queries = list(queries)
    progress_bar = tqdm(
        total=len(queries),
        desc="Processing queries...",
        unit="Query",
        mininterval=0.5,
    )

    for query in queries:
        query_text = query[0]
        if query_text not in query_id_map:
            query_id_map[query_text] = len(query_id_map) + 1

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
            exclude = [
                'Languages',
                'general_log',
                'Roles',
                'Groups_',
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

                results.append({
                    "Query ID": query_id_map[query_text],
                    "Query": query_text,
                    "Table": row[table_row_index],
                    "Extra": row[extra_row_index],
                    "Type": row[type_row_index],
                    "Possible Keys": row[possible_keys_index]
                })

        except Error as e:
            print(f"Failed to explain query: {query_text}")
            print(f"Error: {e}")

        progress_bar.update(1)

    progress_bar.close()
    # Convert list of dictionaries to a set of tuples to remove duplicates
    results = [dict(t) for t in {tuple(d.items()) for d in results}]

    print(f"{len(results)} inefficient queries recorded")
    return results


def save_to_excel(results: list[dict[str, str]]) -> None:
    '''Save results to an Excel file'''
    df = pd.DataFrame(results)
    df.to_excel("inefficient_queries.xlsx", index=False)
    print("Results saved to inefficient_queries.xlsx")


# Main function to handle the logic
def main() -> None:
    '''Main function to handle the logic'''
    # Use your credentials
    connection = create_connection(
        host_name="mysql",
        user_name="root",
        user_password="omegaup",
        db_name="omegaup")
    if connection:
        queries = get_queries_from_general_log(connection)
        if queries:
            results = explain_queries(connection, queries)
            save_to_excel(results)
        else:
            print("No queries found in the general log")
        connection.close()


if __name__ == "__main__":
    main()

#
