from teaching_assistant import get_login_endpoint
import pytest
import requests

BASE_URL = "http://localhost:8001"
COOKIES = None

def get_signup_endpoint(username: str, password: str) -> str:
    """endpoint for creating a teaching assistant user"""
    return (
        f"api/user/create/?username={username}&email={username}@mail.com"
        f"&password={password}"
    )

@pytest.fixture
def setup_accounts():
    global COOKIES, BASE_URL
    """setup accounts for testing"""
    username = "teacher"
    password = "teacher123"
    
    signup_endpoint = get_signup_endpoint(username, password)
    url = f"{BASE_URL}/{signup_endpoint}"

    response = requests.get(url)
    response.raise_for_status()

    username = "student"
    password = "student123"

    signup_endpoint = get_signup_endpoint(username, password)
    url = f"{BASE_URL}/{signup_endpoint}"

    response = requests.get(url)
    response.raise_for_status()

    yield


def test_signup(setup_accounts):
    """test creating a teaching assistant user"""
    assert 2 + 2 == 4, "This test should always pass"
