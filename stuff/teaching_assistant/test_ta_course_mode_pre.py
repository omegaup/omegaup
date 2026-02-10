"""Setups the prerequisites for the teaching to run.
The pytest adds teacher, student, course, assignment,
problem and submission and verifies the success.
Next, the teaching assistant will run on the course.
"""
import os
import time
import zipfile
from typing import Any

import pytest
import requests

from teaching_assistant import get_login_endpoint
from test_ta_submission_mode import (  # pylint: disable=W0611
    get_create_problem_endpoint,
    get_create_run_endpoint,
    get_problem_details_endpoint,
    TEACHER_USERNAME,
    TEACHER_PASSWORD,
    STUDENT_USERNAME,
    STUDENT_PASSWORD,
    BASE_URL,
    COURSE_NAME,
    COURSE_ALIAS,
    COURSE_DESCRIPTION,
    ASSIGNMENT_NAME,
    ASSIGNMENT_ALIAS,
    ASSIGNMENT_DESCRIPTION,
    setup_accounts,
)

COOKIES = None

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
    return "api/course/assignmentDetails/"


def get_course_admin_details_endpoint() -> str:
    """endpoint for getting course admin details"""
    return "api/course/adminDetails/"


@pytest.fixture
def create_test_course() -> None:
    """test creating a course"""
    global COOKIES  # pylint: disable=W0603

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": TEACHER_USERNAME,
            "password": TEACHER_PASSWORD,
        },
        timeout=30,
    )
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

    response = requests.post(create_course_url, data=data, cookies=COOKIES,
                             timeout=30)
    response.raise_for_status()
    assert response.status_code == 200


@pytest.fixture
def create_test_assignment() -> None:
    """test creating an assignment in a course"""
    global COOKIES  # pylint: disable=W0603

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": TEACHER_USERNAME,
            "password": TEACHER_PASSWORD,
        },
        timeout=30,
    )
    response.raise_for_status()
    COOKIES = response.cookies

    # Fetch course details to get valid time bounds
    course_details_url = f"{BASE_URL}/{get_course_admin_details_endpoint()}"
    course_response = requests.get(
        course_details_url,
        params={"alias": COURSE_ALIAS},
        cookies=COOKIES,
        timeout=30
    )
    course_response.raise_for_status()
    course_data = course_response.json()

    # Minimum assignment duration in seconds (1 hour)
    min_assignment_duration = 3600

    # Validate and convert course_start
    raw_course_start = course_data.get('start_time')
    if raw_course_start is None:
        raise AssertionError(
            f"Course API response missing 'start_time'. "
            f"Response: {course_data}"
        )
    try:
        course_start = int(raw_course_start)
    except (ValueError, TypeError) as exc:
        raise AssertionError(
            f"Invalid 'start_time' value: {raw_course_start!r} "
            f"(expected integer or numeric string). Response: {course_data}"
        ) from exc

    # Validate and convert course_finish (optional field)
    raw_course_finish = course_data.get('finish_time')
    course_finish: int | None = None
    if raw_course_finish is not None:
        try:
            course_finish = int(raw_course_finish)
        except (ValueError, TypeError) as exc:
            raise AssertionError(
                f"Invalid 'finish_time' value: {raw_course_finish!r} "
                "(expected integer or numeric string). "
                f"Response: {course_data}"
            ) from exc

    # Validate course time ordering
    if course_finish is not None and course_finish <= course_start:
        raise AssertionError(
            f"Course finish_time ({course_finish}) must be greater than "
            f"start_time ({course_start}). Response: {course_data}"
        )

    # Ensure assignment times fall within course time bounds
    now = int(time.time())
    start_time = max(now, course_start)

    if course_finish is not None:
        finish_time = min(start_time + 18000, course_finish)
    else:
        finish_time = start_time + 18000

    # Ensure finish_time > start_time with minimum duration
    if finish_time <= start_time:
        finish_time = start_time + min_assignment_duration
        print(
            f"Warning: Adjusted finish_time to {finish_time} to ensure "
            f"minimum duration of {min_assignment_duration} seconds"
        )

    create_assignment_url = f"{BASE_URL}/{get_create_assignment_endpoint()}"
    data = {
        "course_alias": COURSE_ALIAS,
        "name": ASSIGNMENT_NAME,
        "alias": ASSIGNMENT_ALIAS,
        "description": ASSIGNMENT_DESCRIPTION,
        "start_time": start_time,
        "finish_time": finish_time,
        "assignment_type": "homework",
        "max_points": 0,
        "order": 1,
        "publish_time_delay": 0
    }

    response = requests.post(create_assignment_url, data=data, cookies=COOKIES,
                             timeout=30)
    response.raise_for_status()
    assert response.status_code == 200


@pytest.fixture
def get_assignment_details() -> Any:
    """test getting assignment details"""
    global COOKIES  # pylint: disable=W0603

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": TEACHER_USERNAME,
            "password": TEACHER_PASSWORD,
        },
        timeout=30,
    )
    response.raise_for_status()
    COOKIES = response.cookies

    assignment_details_url = f"{BASE_URL}/{get_assignment_details_endpoint()}"
    params = {
        "course": COURSE_ALIAS,
        "assignment": ASSIGNMENT_ALIAS
    }

    response = requests.get(assignment_details_url, params=params,
                            cookies=COOKIES, timeout=30)
    response.raise_for_status()

    problemset_id = response.json().get("problemset_id")
    assert problemset_id is not None

    assert response.status_code == 200
    yield problemset_id


@pytest.fixture
def create_test_problem() -> None:
    """test creating a problem"""
    global COOKIES  # pylint: disable=W0603

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": TEACHER_USERNAME,
            "password": TEACHER_PASSWORD,
        },
        timeout=30,
    )
    response.raise_for_status()
    COOKIES = response.cookies

    with zipfile.ZipFile(TEST_PROBLEM_ZIP, "w") as zipf:
        for root, dirs, files in os.walk(TEST_PROBLEM_DIR):
            for file in files:
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, TEST_PROBLEM_DIR)
                zipf.write(file_path, arcname)
            for directory in dirs:
                dir_path = os.path.join(root, directory)
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
        "languages": ("c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,"
                      "cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,"
                      "cs,pas,hs,lua,go,rs,js"),
        "email_clarifications": 1,
        "problem_level": "problemLevelBasicIntroductionToProgramming",
        "selected_tags": ("[{\"tagname\":\"problemTagBinarySearchTree\","
                          "\"public\":true}]")
    }

    with open(TEST_PROBLEM_ZIP, "rb") as problem_file:
        zip_files: Any = {
            "problem_contents": problem_file
        }

        response = requests.post(
            create_problem_url,
            data=data,
            files=zip_files,
            cookies=COOKIES,
            timeout=30
        )
        response.raise_for_status()

    if os.path.exists(TEST_PROBLEM_ZIP):
        os.remove(TEST_PROBLEM_ZIP)

    assert response.status_code == 200


@pytest.fixture
def add_problem_to_course() -> None:
    """Fixture to add a problem to a course assignment"""
    global COOKIES  # pylint: disable=W0603

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": TEACHER_USERNAME,
            "password": TEACHER_PASSWORD,
        },
        timeout=30,
    )
    response.raise_for_status()
    COOKIES = response.cookies

    add_problem_url = f"{BASE_URL}/{get_add_problem_to_course_endpoint()}"
    data = {
        "course_alias": COURSE_ALIAS,
        "assignment_alias": ASSIGNMENT_ALIAS,
        "problem_alias": PROBLEM_ALIAS
    }

    response = requests.post(add_problem_url, data=data, cookies=COOKIES,
                             timeout=30)
    response.raise_for_status()
    assert response.status_code == 200


@pytest.fixture
def add_student_to_course() -> None:
    """Fixture to add a student to a course"""
    global COOKIES  # pylint: disable=W0603

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": TEACHER_USERNAME,
            "password": TEACHER_PASSWORD,
        },
        timeout=30,
    )
    response.raise_for_status()
    COOKIES = response.cookies

    add_student_url = f"{BASE_URL}/{get_add_student_to_course_endpoint()}"
    data = {
        "usernameOrEmail": STUDENT_USERNAME,
        "course_alias": COURSE_ALIAS,
        "share_user_information": "true",
        "accept_teacher": "true"
    }

    response = requests.post(add_student_url, data=data, cookies=COOKIES,
                             timeout=30)
    response.raise_for_status()
    assert response.status_code == 200


@pytest.fixture
def create_test_run(
    get_assignment_details: Any  # pylint: disable=W0621, W0613
) -> Any:
    """test creating a run"""

    login_endpoint = get_login_endpoint(STUDENT_USERNAME, STUDENT_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": STUDENT_USERNAME,
            "password": STUDENT_PASSWORD,
        },
        timeout=30,
    )
    response.raise_for_status()
    student_cookies = response.cookies

    run_data = {
        "language": "py2",
        "problem_alias": PROBLEM_ALIAS,
        "source": ("#include <iostream>\n\nint main() {\n"
                   "    std::cin.tie(nullptr);\n"
                   "    std::ios_base::sync_with_stdio(false);\n\n"
                   "    int A, B;\n    std::cin >> A >> B;\n"
                   "    std::cout << A - B << '\\n';\n}"),
        "problemset_id": get_assignment_details
    }

    create_run_url = f"{BASE_URL}/{get_create_run_endpoint()}"
    response = requests.post(create_run_url, data=run_data,
                             cookies=student_cookies, timeout=30)
    response.raise_for_status()

    assert response.status_code == 200
    guid = response.json()['guid']
    yield guid


@pytest.fixture
def request_feedback(
    create_test_run: Any  # pylint: disable=W0621, W0613
) -> None:
    """Fixture to request feedback for a problem"""
    global COOKIES  # pylint: disable=W0603

    guid = create_test_run

    login_endpoint = get_login_endpoint(STUDENT_USERNAME, STUDENT_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.post(
        login_url,
        data={
            "usernameOrEmail": STUDENT_USERNAME,
            "password": STUDENT_PASSWORD,
        },
        timeout=30,
    )
    response.raise_for_status()
    COOKIES = response.cookies

    request_feedback_url = (f"{BASE_URL}/{get_request_feedback_endpoint()}"
                            f"?course_alias={COURSE_ALIAS}"
                            f"&assignment_alias={ASSIGNMENT_ALIAS}"
                            f"&guid={guid}")

    response = requests.post(request_feedback_url, cookies=COOKIES, timeout=30)
    response.raise_for_status()

    assert response.status_code == 200


def test_problem_and_run_setup(  # pylint: disable=R0913
    setup_accounts: Any,  # pylint: disable=W0621, W0613
    create_test_course: Any,  # pylint: disable=W0621, W0613
    create_test_assignment: Any,  # pylint: disable=W0621, W0613
    get_assignment_details: Any,  # pylint: disable=W0621, W0613
    create_test_problem: Any,  # pylint: disable=W0621, W0613
    add_problem_to_course: Any,  # pylint: disable=W0621, W0613
    add_student_to_course: Any,  # pylint: disable=W0621, W0613
    create_test_run: Any,  # pylint: disable=W0621, W0613
    request_feedback: Any  # pylint: disable=W0621, W0613
) -> None:
    """test that the problem and run are created successfully"""

    problem_details_url = (
        f"{BASE_URL}/"
        f"{get_problem_details_endpoint(PROBLEM_ALIAS)}"
    )

    response = requests.get(problem_details_url, cookies=COOKIES, timeout=30)
    response.raise_for_status()

    assert response.status_code == 200
    assert response.json()["alias"] == PROBLEM_ALIAS
