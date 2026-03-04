"""Checks if the teaching assistant course worked correctly.
This pytest tests if the teaching assistant was successful
writing feedback for the submissions in a course.
"""
import logging
from typing import Any

import pytest
import requests

from teaching_assistant import (
    get_login_endpoint,
    get_runs_submission_feedback_endpoint,
    get_runs_from_course_endpoint
)
from test_ta_submission_mode import (
    TEACHER_USERNAME,
    TEACHER_PASSWORD,
    BASE_URL,
    COOKIES,
)
from test_ta_submission_mode import (
    COURSE_ALIAS,
    ASSIGNMENT_ALIAS,
)

logging.basicConfig(level=logging.INFO)


@pytest.fixture
def get_runs() -> Any:
    """Get the guid of the first run for the course and assignment."""
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
        assignment_alias=ASSIGNMENT_ALIAS,
    )

    runs_url = f"{BASE_URL}/{runs_endpoint}"
    runs = requests.get(runs_url, cookies=COOKIES, timeout=30).json()["runs"]

    yield runs


def test_verify_feedback(
    get_runs: Any  # pylint: disable=W0621, W0613
) -> None:
    """Test to verify that the feedback was posted."""

    runs = get_runs

    assert len(runs) == 1, "No runs found for the course and assignment."
    run_guid = runs[0]["guid"]

    feedback_endpoint = get_runs_submission_feedback_endpoint(run_guid)
    feedback_url = f"{BASE_URL}/{feedback_endpoint}"

    response = requests.get(feedback_url, cookies=COOKIES, timeout=30)
    response.raise_for_status()

    feedbacks = response.json()
    logging.info("Feedbacks: %s", feedbacks)
    assert len(feedbacks) > 1, "Feedback is empty."
