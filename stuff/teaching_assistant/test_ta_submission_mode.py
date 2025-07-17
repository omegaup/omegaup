"""Test module for teaching assistant submission mode pre functionality."""
import logging
import pytest
import requests

from teaching_assistant import get_login_endpoint
import subprocess

from teaching_assistant import (
    get_runs_from_course_endpoint,
    get_runs_submission_feedback_endpoint,
    set_submission_feedback_endpoint
)

COURSE_NAME = "Course"
COURSE_ALIAS = "course"
COURSE_DESCRIPTION = "A course for testing."

ASSIGNMENT_NAME = "Assignment"
ASSIGNMENT_ALIAS = "assignment"
ASSIGNMENT_DESCRIPTION = "An assignment for testing."

BASE_URL = "http://localhost:8001"
COOKIES = None

TEACHER_USERNAME = "teacher"
TEACHER_PASSWORD = "teacher123"
STUDENT_USERNAME = "student"
STUDENT_PASSWORD = "student123"


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
def setup_accounts() -> None:
    """setup accounts for testing"""
    try:
        signup_endpoint = get_signup_endpoint(TEACHER_USERNAME,
                                              TEACHER_PASSWORD)
        url = f"{BASE_URL}/{signup_endpoint}"

        response = requests.get(url, timeout=30)
        response.raise_for_status()

        signup_endpoint = get_signup_endpoint(STUDENT_USERNAME,
                                              STUDENT_PASSWORD)
        url = f"{BASE_URL}/{signup_endpoint}"

        response = requests.get(url, timeout=30)
        response.raise_for_status()
    except requests.RequestException:
        logging.error("Account might already exist, Proceeding with tests.")


@pytest.fixture
def extract_submission_id():
    """Fixture to extract only the submission from the course"""
    global COOKIES

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url, timeout=30)
    response.raise_for_status()
    COOKIES = response.cookies
    
    runs_endpoint = get_runs_from_course_endpoint(
        course_alias=COURSE_ALIAS,
        assignment_alias=ASSIGNMENT_ALIAS
    )
    runs_url = f"{BASE_URL}/{runs_endpoint}"

    response = requests.get(runs_url, timeout=30, cookies=COOKIES)
    response.raise_for_status()

    runs = response.json()['runs']
    assert len(runs) == 1

    guid = runs[0]['guid']
    yield guid

@pytest.fixture
def extract_feedback_id(extract_submission_id):
    """Fixture to extract only the feedback from the submission"""
    global COOKIES

    guid = extract_submission_id

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url, timeout=30)
    response.raise_for_status()
    COOKIES = response.cookies

    submission_feedback_endpoint = get_runs_submission_feedback_endpoint(
        run_alias=guid
    )
    submission_feedback_url = f"{BASE_URL}/{submission_feedback_endpoint}"
    
    response = requests.get(submission_feedback_url, timeout=30, cookies=COOKIES)
    response.raise_for_status()

    feedbacks = response.json()

    assert len(feedbacks) == 3

    feedback_id = feedbacks[1]['submission_feedback_id']
    yield feedback_id

@pytest.fixture
def add_student_feedback(extract_submission_id, extract_feedback_id):
    guid = extract_submission_id
    feedback_id = extract_feedback_id

    login_endpoint = get_login_endpoint(STUDENT_USERNAME, STUDENT_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url, timeout=30)
    response.raise_for_status()
    COOKIES = response.cookies

    submission_feedback_endpoint = set_submission_feedback_endpoint(
        run_alias=guid,
        course_alias=COURSE_ALIAS,
        assignment_alias=ASSIGNMENT_ALIAS,
        feedback="May you check again?",
        line_number="1",
        submission_feedback_id=feedback_id
    )
    submission_feedback_url = f"{BASE_URL}/{submission_feedback_endpoint}"

    response = requests.get(submission_feedback_url, timeout=30, cookies=COOKIES)
    response.raise_for_status()
    yield guid

@pytest.fixture
def run_teaching_assistant(add_student_feedback):
    """Fixture to run the teaching assistant"""
    guid = add_student_feedback
    command = [
        "python", "teaching_assistant.py",
        "--skip-confirm",
        "--username", TEACHER_USERNAME,
        "--password", TEACHER_PASSWORD,
        "--student_name", STUDENT_USERNAME,
        "--key", "sk-27343f1eb8f64d238a39bebdbcff8d03",
        "--language", "English",
        "--course_alias", COURSE_ALIAS,
        "--assignment_alias", ASSIGNMENT_ALIAS,
        "--test_mode",
        "--llm", "omegaup",
        "--key", "omegaup",
        "--submission_id_mode", "true",
        "--submission_id", guid,
        "--ta_feedback_indicator", "AI generated "
    ]
    try:
        result = subprocess.run(
            command,
            cwd="stuff/teaching_assistant",
            check=True,
            timeout=60,
            capture_output=True,
            text=True
        )
        logging.info("STDOUT: %s", result.stdout)
        logging.info("STDERR: %s", result.stderr)
        logging.info("Return code: %s", result.returncode)
    except subprocess.CalledProcessError as e:
        logging.error(f"Teaching assistant subprocess failed: {e}")
        logging.error("STDOUT: %s", e.stdout)
        logging.error("STDERR: %s", e.stderr)
    except subprocess.TimeoutExpired:
        logging.error("Teaching assistant subprocess timed out.")

def test_teaching_assistant_submission_mode(
    setup_accounts,
    extract_submission_id,
    add_student_feedback,
    run_teaching_assistant
):
    global COOKIES

    guid = extract_submission_id

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url, timeout=30)
    response.raise_for_status()
    COOKIES = response.cookies

    submission_feedback_endpoint = get_runs_submission_feedback_endpoint(
        run_alias=guid
    )
    submission_feedback_url = f"{BASE_URL}/{submission_feedback_endpoint}"

    response = requests.get(submission_feedback_url, timeout=30, cookies=COOKIES)
    response.raise_for_status()

    feedbacks = response.json()

    assert len(feedbacks) >= 2

    feedback_thread = feedbacks[1]['feedback_thread']
    assert feedback_thread is not None, "Feedback thread should not be None"
    assert len(feedback_thread) == 2
    