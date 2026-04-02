"""
base.py - Module containing Base class for handling API methods.

This module defines the Base class, which provides methods for interacting with 
various APIs such as user registration, creating contests, courses, etc.

Example:
    To use the Base class to register a new user:

        from base import Base

        base_instance = Base('https://localhost:8001')
        base_instance.register('example_user', 'example_password')
"""

from endpoints import OMEGAUP_ENDPOINTS

import requests

class Base:
    """
    Base class for handling API methods for user registration, contests, courses, etc.

    Attributes:
    - base_url (str): The base URL of the API endpoint.
    - endpoints (str): All the API endpoints.
    """

    def __init__(self):
        self.base_url = "http://localhost:8001"
        self.endpoints = OMEGAUP_ENDPOINTS


    def create_user(self, username, password):
        """
        Register a new user with the provided username and password.

        Parameters:
        - username (str): The username for the new user.
        - password (str): The password for the new user.

        Returns:
        - dict: A dictionary containing the response JSON if the user was successfully registered.
                None is returned if there was an error during registration.
        """
        endpoint = self.endpoints.get("create_user")

        url = f"{self.base_url}{endpoint}"

        params = {
            'username': username,
            'password': password,
            'email': f"{username}@omegaup.com"
        }

        try:
            response = requests.post(url, params=params, timeout=10)

            if response.status_code == 200:
                print(f"User '{username}' created successfully!")
                return response.json()
            else:
                print(f"Failed to register user '{username}'. Status code: {response.status_code}")

        except requests.exceptions.RequestException as e:
            print("Error:", e)
  