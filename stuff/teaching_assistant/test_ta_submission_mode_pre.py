from teaching_assistant import get_login_endpoint
import pytest
import requests
import zipfile
import os
import logging

BASE_URL = "http://localhost:8001"
COOKIES = None

TEACHER_USERNAME = "teacher"
TEACHER_PASSWORD = "teacher123"
STUDENT_USERNAME = "student"
STUDENT_PASSWORD = "student123"

PROBLEM_ALIAS = "sum"
PROBLEM_TITLE = "Sum"
TEST_PROBLEM_DIR = "stuff/teaching_assistant/test_problem_sum"
TEST_PROBLEM_ZIP = f"{TEST_PROBLEM_DIR}.zip"

def get_signup_endpoint(username: str, password: str) -> str:
    """endpoint for creating a teaching assistant user"""
    return (
        f"api/user/create/?username={username}&email={username}@mail.com"
        f"&password={password}"
    )

def get_create_problem_endpoint() -> str:
    """endpoint for creating a problem"""
    return "api/problem/create/"

def get_create_run_endpoint() -> str:
    """endpoint for creating a run"""
    return "api/run/create/"

def get_problem_details_endpoint(problem_alias: str) -> str:
    """endpoint for getting problem details"""
    return f"api/problem/details?problem_alias={problem_alias}"

@pytest.fixture
def setup_accounts():
    global COOKIES, BASE_URL
    """setup accounts for testing"""
    try:
        signup_endpoint = get_signup_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
        url = f"{BASE_URL}/{signup_endpoint}"

        response = requests.get(url)
        response.raise_for_status()

        signup_endpoint = get_signup_endpoint(STUDENT_USERNAME, STUDENT_PASSWORD)
        url = f"{BASE_URL}/{signup_endpoint}"

        response = requests.get(url)
        response.raise_for_status()
    except requests.RequestException as _:
        logging.error(f"Account might already exist, Proceeding with tests.")

@pytest.fixture
def create_test_problem():
    """test creating a problem"""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    with zipfile.ZipFile(TEST_PROBLEM_ZIP, "w") as zipf:
        for root, dirs, files in os.walk(TEST_PROBLEM_DIR):
            for file in files:
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, TEST_PROBLEM_DIR)
                zipf.write(file_path, arcname)
            for dir in dirs:
                dir_path = os.path.join(root, dir)
                arcname = os.path.relpath(dir_path, TEST_PROBLEM_DIR)
                zipf.write(dir_path, arcname + "/")

    create_problem_url = f"{BASE_URL}/{get_create_problem_endpoint()}"
    data = {
        "visibility": "public",
        "title": PROBLEM_TITLE,
        "problem_alias": PROBLEM_ALIAS,
        "validator": "token-numeric",
        "time_limit": 1000,
        "validator_time_limit": 0,
        "overall_wall_time_limit": 1000,
        "extra_wall_time": 0,
        "memory_limit": 32768,
        "output_limit": 10240,
        "input_limit": 10240,
        "source": "omegaUp classics",
        "show_diff": "examples",
        "allow_user_add_tags": "true",
        "languages": "c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js",
        "email_clarifications": 1,
        "problem_level": "problemLevelBasicIntroductionToProgramming",
        "selected_tags": "[{\"tagname\":\"problemTagBinarySearchTree\",\"public\":true}]"
    }

    files = {
        "problem_contents": open(TEST_PROBLEM_ZIP, "rb")
    }

    response = requests.post(create_problem_url, data=data, files=files, cookies=COOKIES)
    response.raise_for_status()

    files["problem_contents"].close()

    if os.path.exists(TEST_PROBLEM_ZIP):
        os.remove(TEST_PROBLEM_ZIP)

    assert response.status_code == 200

@pytest.fixture
def create_test_run():
    """test creating a run"""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(STUDENT_USERNAME, STUDENT_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    student_cookies = response.cookies

    run_data = {
        "language": "cpp17-gcc",
        "problem_alias": PROBLEM_ALIAS,
        "source": "#include <iostream>\n\nint main() {\n    std::cin.tie(nullptr);\n    std::ios_base::sync_with_stdio(false);\n\n    int A, B;\n    std::cin >> A >> B;\n    std::cout << A - B << '\\n';\n}",
        "contest_alias": None,
        "problemset_id": None
    }

    create_run_url = f"{BASE_URL}/{get_create_run_endpoint()}"
    response = requests.post(create_run_url, data=run_data, cookies=student_cookies)
    response.raise_for_status()

    assert response.status_code == 200

    yield response.json()


def test_problem_and_run_setup(setup_accounts, create_test_problem, create_test_run):
    """test that the problem and run are created successfully"""
    global COOKIES, BASE_URL

    problem_details_url = f"{BASE_URL}/{get_problem_details_endpoint(PROBLEM_ALIAS)}"

    response = requests.get(problem_details_url, cookies=COOKIES)
    response.raise_for_status()

    assert response.status_code == 200
    assert response.json()["alias"] == PROBLEM_ALIAS
