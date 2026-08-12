"""
demo_autofill.py - Module for populating the system with dummy data.

This module provides a main function to populate the system with dummy data 
by calling various API methods.
It serves as the entry point for filling up the system with dummy data for 
testing or demonstration purposes.

Example:
    To fill up the system with dummy data:

        python3 demo_autofill.py
"""

from base import Base

def main():
    """
    Fill up the system with dummy data by calling various API methods.

    This function serves as the entry point to populate the system with dummy data
    for testing or demonstration purposes. It calls various API methods of the Base
    class to perform actions such as creating users, contests, courses, etc.
    """
    base = Base()

    for i in range(100):
        username = f"user{i+1}"
        password = "12345678"
        base.create_user(username, password)


if __name__ == "__main__":
    main()
