from teaching_assistant import get_login_endpoint
from test_ta_submission_mode_pre import (
    get_create_problem_endpoint,
    get_create_run_endpoint,
    get_problem_details_endpoint,
    TEACHER_USERNAME,
    TEACHER_PASSWORD,
    STUDENT_USERNAME,
    STUDENT_PASSWORD,
    setup_accounts,
)
import pytest
import requests
import time
import zipfile
import os

BASE_URL = "http://localhost:8001"
COOKIES = None

COURSE_NAME = "Course"
COURSE_ALIAS = "course"
COURSE_DESCRIPTION = "A course for testing."

ASSIGNMENT_NAME = "Assignment"
ASSIGNMENT_ALIAS = "assignment"
ASSIGNMENT_DESCRIPTION = "An assignment for testing."

PROBLEM_ALIAS = "subtract"
PROBLEM_TITLE = "Subtract"
TEST_PROBLEM_DIR = "stuff/teaching_assistant/test_problem_subtract"
TEST_PROBLEM_ZIP = f"{TEST_PROBLEM_DIR}.zip"

def get_create_course_endpoint() -> str:
    """endpoint for creating a problem"""
    return "api/course/create/"

def get_create_assignment_endpoint() -> str:
    """endpoint for creating an assignment"""
    return "api/course/createAssignment/"

def get_add_problem_to_course_endpoint() -> str:
    """endpoint for adding a problem to a course"""
    return "api/course/addProblem/"

def get_add_student_to_course_endpoint() -> str:
    """endpoint for adding a student to a course"""
    return "api/course/addStudent/"

def get_request_feedback_endpoint() -> str:
    """endpoint for requesting feedback"""
    return "api/course/requestFeedback/"

def get_assignment_details_endpoint() -> str:
    """endpoint for getting assignment details"""
    return f"api/course/assignmentDetails/"

@pytest.fixture
def create_test_course():
    """test creating a course"""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"
    
    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    create_course_url = f"{BASE_URL}/{get_create_course_endpoint()}"
    now = int(time.time())
    data = {
        "name": COURSE_NAME,
        "alias": COURSE_ALIAS,
        "description": COURSE_DESCRIPTION,
        "start_time": now,
        "finish_time": now + 18000,
        "admission_mode": "private",
        "school_id": 1,
        "needs_basic_information": "false",
        "requests_user_information": "required",
        "languages": "py2,py3"
    }

    response = requests.post(create_course_url, data=data, cookies=COOKIES)
    response.raise_for_status()
    assert response.status_code == 200

@pytest.fixture
def create_test_assignment():
    """test creating an assignment in a course"""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    create_assignment_url = f"{BASE_URL}/{get_create_assignment_endpoint()}"
    now = int(time.time())
    data = {
        "course_alias": COURSE_ALIAS,
        "name": ASSIGNMENT_NAME,
        "alias": ASSIGNMENT_ALIAS,
        "description": ASSIGNMENT_DESCRIPTION,
        "start_time": now,
        "finish_time": now + 18000,
        "assignment_type": "homework",
        "max_points": 0,
        "order": 1,
        "publish_time_delay": 0
    }

    response = requests.post(create_assignment_url, data=data, cookies=COOKIES)
    response.raise_for_status()
    assert response.status_code == 200

@pytest.fixture
def get_assignment_details():
    """test getting assignment details"""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    assignment_details_url = f"{BASE_URL}/{get_assignment_details_endpoint()}"
    params = {
        "course": COURSE_ALIAS,
        "assignment": ASSIGNMENT_ALIAS
    }

    response = requests.get(assignment_details_url, params=params, cookies=COOKIES)
    response.raise_for_status()
    
    problemset_id = response.json().get("problemset_id")
    assert problemset_id is not None

    assert response.status_code == 200
    yield problemset_id

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
def add_problem_to_course():
    """Fixture to add a problem to a course assignment"""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    add_problem_url = f"{BASE_URL}/{get_add_problem_to_course_endpoint()}"
    data = {
        "course_alias": COURSE_ALIAS,
        "assignment_alias": ASSIGNMENT_ALIAS,
        "problem_alias": PROBLEM_ALIAS
    }

    response = requests.post(add_problem_url, data=data, cookies=COOKIES)
    response.raise_for_status()
    assert response.status_code == 200

@pytest.fixture
def add_student_to_course():
    """Fixture to add a student to a course"""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    add_student_url = f"{BASE_URL}/{get_add_student_to_course_endpoint()}"
    data = {
        "usernameOrEmail": STUDENT_USERNAME,
        "course_alias": COURSE_ALIAS,
        "share_user_information": "true",
        "accept_teacher": "true"
    }

    response = requests.post(add_student_url, data=data, cookies=COOKIES)
    response.raise_for_status()
    assert response.status_code == 200

@pytest.fixture
def create_test_run(get_assignment_details):
    """test creating a run"""
    global COOKIES, BASE_URL
    
    login_endpoint = get_login_endpoint(STUDENT_USERNAME, STUDENT_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"
    
    response = requests.get(login_url)
    response.raise_for_status()
    student_cookies = response.cookies
    
    run_data = {
        "language": "py2",
        "problem_alias": PROBLEM_ALIAS,
        "source": "#include <iostream>\n\nint main() {\n    std::cin.tie(nullptr);\n    std::ios_base::sync_with_stdio(false);\n\n    int A, B;\n    std::cin >> A >> B;\n    std::cout << A - B << '\\n';\n}",
        "problemset_id": get_assignment_details
    }
    
    create_run_url = f"{BASE_URL}/{get_create_run_endpoint()}"
    response = requests.post(create_run_url, data=run_data, cookies=student_cookies)
    response.raise_for_status()
    
    assert response.status_code == 200
    guid = response.json()['guid']
    yield guid

@pytest.fixture
def request_feedback(create_test_run):
    """Fixture to request feedback for a problem"""
    global COOKIES, BASE_URL

    guid = create_test_run

    login_endpoint = get_login_endpoint(STUDENT_USERNAME, STUDENT_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    request_feedback_url = f"{BASE_URL}/{get_request_feedback_endpoint()}?course_alias={COURSE_ALIAS}&assignment_alias={ASSIGNMENT_ALIAS}&guid={guid}"

    response = requests.post(request_feedback_url, cookies=COOKIES)
    response.raise_for_status()
    
    assert response.status_code == 200

def test_problem_and_run_setup(setup_accounts, create_test_course, create_test_assignment, get_assignment_details, create_test_problem, add_problem_to_course, add_student_to_course, create_test_run, request_feedback):
    """test that the problem and run are created successfully"""
    global COOKIES, BASE_URL
    
    problem_details_url = f"{BASE_URL}/{get_problem_details_endpoint(PROBLEM_ALIAS)}"
    
    response = requests.get(problem_details_url, cookies=COOKIES)
    response.raise_for_status()
    
    assert response.status_code == 200
    assert response.json()["alias"] == PROBLEM_ALIAS
