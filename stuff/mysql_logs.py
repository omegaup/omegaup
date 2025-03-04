import mysql.connector
from mysql.connector import Error

# Establish connection to MySQL
def create_connection(host_name, user_name, user_password, db_name):
    connection = None
    try:
        connection = mysql.connector.connect(
            host=host_name,
            user=user_name,
            passwd=user_password,
            database=db_name,
            port=13306
        )
        print("Connection to MySQL DB successful")
    except Error as e:
        print(f"The error '{e}' occurred")
    return connection

# Function to retrieve all queries from the general log
def get_queries_from_general_log(connection):
    cursor = connection.cursor()
    cursor.execute("""
        USE omegaup
    """)
    cursor.execute("""
        SELECT CONVERT(argument USING utf8) 
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

# Function to run EXPLAIN on each query
def explain_queries(connection, queries):
    cursor = connection.cursor()
    for query in queries:
        query_text = query[0]
        try:
            cursor.execute(f"EXPLAIN {query_text}")
            explain_result = cursor.fetchall()

            # Get the index of the 'possible_keys' column
            column_names = [i[0] for i in cursor.description]
            possible_keys_index = column_names.index('possible_keys')
            table_row_index = column_names.index('table')
            extra_row_index = column_names.index('Extra')

            # Check if any row has NULL or empty possible_keys
            for row in explain_result:
                if (str(row[extra_row_index]) != 'no matching row in const table') and\
                        (row[possible_keys_index] is None or row[possible_keys_index] == '') and\
                        (str(row[extra_row_index]) != 'Using index') and\
                        (row[table_row_index] is not None and "<union" not in row[table_row_index]) and\
                        (row[table_row_index] is not None and "<derived" not in row[table_row_index]) and\
                        (row[table_row_index] is not None and row[table_row_index] not in ['Languages', 'general_log', 'Roles', 'Groups_', 'Tags']) and\
                        not (query_text.startswith('DELETE ') and ' WHERE ' not in query_text) and\
                        not (str("Users u") in query_text and str("verification_id = ") in query_text) and\
                        not (str("Users_Badges") in query_text and str("badge_alias = ") in query_text): # https://github.com/omegaup/omegaup/issues/7773, # https://github.com/omegaup/omegaup/issues/7774
                    print(f"Found query with full table scan: {query_text}")
                    print(query_text.split()[0], row[table_row_index], row[extra_row_index])
                    #if ("s" == row[table_row_index]):
                    #print(query_text)
            #print(f"EXPLAIN for query: {query_text}")
            #for row in explain_result:
            #    print(row)
        except Error as e:
            print(f"Failed to explain query: {query_text}")
            print(f"Error: {e}")

# Main function to handle the logic
def main():
    # Use your credentials
    connection = create_connection(host_name="mysql",user_name="root",user_password="omegaup",db_name="omegaup",)
    
    if connection:
        queries = get_queries_from_general_log(connection)
        if queries:
            explain_queries(connection, queries)
        else:
            print("No queries found in the general log")
        
        connection.close()

if __name__ == "__main__":
    main()