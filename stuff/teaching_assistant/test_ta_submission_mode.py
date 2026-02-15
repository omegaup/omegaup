"""Tests if the teaching assistant submission mode works.
The pytest extracts the submission id for the run, adds a
student feedback, runs the teaching assistant for
that submission and then checks if the feedback is posted.
"""
import logging
from typing import Any
import subprocess
import pytest
import requests

from teaching_assistant import get_login_endpoint

from teaching_assistant import (
    get_runs_from_course_endpoint,
    get_runs_submission_feedback_endpoint,
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


def get_signup_endpoint() -> str:
    """endpoint for creating a teaching assistant user"""
    return "api/user/create/"


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
        url = f"{BASE_URL}/{get_signup_endpoint()}"

        response = requests.post(
            url,
            data={
                "username": TEACHER_USERNAME,
                "email": f"{TEACHER_USERNAME}@mail.com",
                "password": TEACHER_PASSWORD,
            },
            timeout=30,
        )
        response.raise_for_status()

        response = requests.post(
            url,
            data={
                "username": STUDENT_USERNAME,
                "email": f"{STUDENT_USERNAME}@mail.com",
                "password": STUDENT_PASSWORD,
            },
            timeout=30,
        )
        response.raise_for_status()
    except requests.RequestException:
        logging.error("Account might already exist, Proceeding with tests.")


@pytest.fixture
def extract_submission_id() -> Any:
    """Fixture to extract only the submission from the course"""
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
def extract_feedback_id(
    extract_submission_id: Any  # pylint: disable=W0621, W0613
) -> Any:
    """Fixture to extract only the feedback from the submission"""
    global COOKIES  # pylint: disable=W0603

    guid = extract_submission_id

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

    submission_feedback_endpoint = get_runs_submission_feedback_endpoint(
        run_alias=guid
    )
    submission_feedback_url = f"{BASE_URL}/{submission_feedback_endpoint}"

    response = requests.get(
        submission_feedback_url,
        timeout=30,
        cookies=COOKIES
    )
    response.raise_for_status()

    feedbacks = response.json()

    assert len(feedbacks) == 3

    feedback_id = feedbacks[1]['submission_feedback_id']
    yield feedback_id


@pytest.fixture
def add_student_feedback(
    extract_submission_id: Any,  # pylint: disable=W0621, W0613
    extract_feedback_id: Any  # pylint: disable=W0621, W0613
) -> Any:
    """Fixture to add student feedback to the submission"""
    global COOKIES  # pylint: disable=W0603
    guid = extract_submission_id
    feedback_id = extract_feedback_id

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

    submission_feedback_url = f"{BASE_URL}/api/submission/setFeedback/"

    response = requests.post(
        submission_feedback_url,
        data={
            "guid": guid,
            "course_alias": COURSE_ALIAS,
            "assignment_alias": ASSIGNMENT_ALIAS,
            "feedback": "May you check again?",
            "range_bytes_start": 1,
            "submission_feedback_id": feedback_id,
        },
        timeout=30,
        cookies=COOKIES,
    )
    response.raise_for_status()
    yield guid


@pytest.fixture
def run_teaching_assistant(
    add_student_feedback: Any  # pylint: disable=W0621, W0613
) -> None:
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
        logging.error("Teaching assistant subprocess failed: %s", e)
        logging.error("STDOUT: %s", e.stdout)
        logging.error("STDERR: %s", e.stderr)
    except subprocess.TimeoutExpired:
        logging.error("Teaching assistant subprocess timed out.")


def test_teaching_assistant_submission_mode(
    extract_submission_id: Any,  # pylint: disable=W0621, W0613
    add_student_feedback: Any,  # pylint: disable=W0621, W0613
    run_teaching_assistant: Any  # pylint: disable=W0621, W0613
) -> None:
    """Test the teaching assistant submission mode functionality."""
    global COOKIES  # pylint: disable=W0603

    guid = extract_submission_id

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

    submission_feedback_endpoint = get_runs_submission_feedback_endpoint(
        run_alias=guid
    )
    submission_feedback_url = f"{BASE_URL}/{submission_feedback_endpoint}"

    response = requests.get(
        submission_feedback_url,
        timeout=30,
        cookies=COOKIES
    )
    response.raise_for_status()

    feedbacks = response.json()

    assert len(feedbacks) >= 2

    feedback_thread = feedbacks[1]['feedback_thread']
    logging.info("Feedback thread: %s", feedback_thread)
    assert feedback_thread is not None, "Feedback thread should not be None"
    assert len(feedback_thread) == 2
