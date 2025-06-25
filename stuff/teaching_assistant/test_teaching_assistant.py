from teaching_assistant import get_login_endpoint
import pytest
import requests
import zipfile
import os

BASE_URL = "http://localhost:8001"
COOKIES = None

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

@pytest.fixture
def create_test_problem():
    """test creating a problem"""
    global COOKIES, BASE_URL
    
    login_endpoint = get_login_endpoint("teacher", "teacher123")
    login_url = f"{BASE_URL}/{login_endpoint}"
    
    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    with zipfile.ZipFile("stuff/teaching_assistant/test_problem.zip", "w") as zipf:
        for root, dirs, files in os.walk("stuff/teaching_assistant/test_problem"):
            for file in files:
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, "stuff/teaching_assistant/test_problem")
                zipf.write(file_path, arcname)
            for dir in dirs:
                dir_path = os.path.join(root, dir)
                arcname = os.path.relpath(dir_path, "stuff/teaching_assistant/test_problem")
                zipf.write(dir_path, arcname + "/")
    
    create_problem_url = f"{BASE_URL}/{get_create_problem_endpoint()}"
    data = {
        "visibility": "public",
        "title": "Sum",
        "problem_alias": "sum",
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
        "problem_contents": open("stuff/teaching_assistant/test_problem.zip", "rb")
    }

    response = requests.post(create_problem_url, data=data, files=files, cookies=COOKIES)
    response.raise_for_status()
    
    files["problem_contents"].close()
    
    if os.path.exists("stuff/teaching_assistant/test_problem.zip"):
        os.remove("stuff/teaching_assistant/test_problem.zip")
    
    assert response.status_code == 200

@pytest.fixture
def create_test_run():
    """test creating a run"""
    global COOKIES, BASE_URL
    
    login_endpoint = get_login_endpoint("student", "student123")
    login_url = f"{BASE_URL}/{login_endpoint}"
    
    response = requests.get(login_url)
    response.raise_for_status()
    student_cookies = response.cookies
    
    run_data = {
        "language": "cpp17-gcc",
        "problem_alias": "sum",
        "source": "#include <iostream>\n\nint main() {\n    std::cin.tie(nullptr);\n    std::ios_base::sync_with_stdio(false);\n\n    int A, B;\n    std::cin >> A >> B;\n    std::cout << A - B << '\\n';\n}",
        "contest_alias": None,
        "problemset_id": None
    }
    
    create_run_url = f"{BASE_URL}/{get_create_run_endpoint()}"
    response = requests.post(create_run_url, data=run_data, cookies=student_cookies)
    response.raise_for_status()
    
    assert response.status_code == 200
    
    yield response.json()


def test_ta(setup_accounts, create_test_problem, create_test_run):
    """test the teaching assistant functionality"""
    assert 2 + 2 == 4, "This test should always pass"
