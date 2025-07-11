from teaching_assistant import (
    get_login_endpoint,
    get_runs_submission_feedback_endpoint,
    get_runs_from_course_endpoint
)
from test_ta_submission_mode_pre import (
    TEACHER_USERNAME,
    TEACHER_PASSWORD,
    BASE_URL,
    COOKIES,
)
from test_ta_course_mode_pre import (
    COURSE_ALIAS,
    ASSIGNMENT_ALIAS,
)
import requests
import pytest
import logging

logging.basicConfig(level=logging.INFO)

@pytest.fixture
def get_runs():
    """Get the guid of the first run for the course and assignment."""
    global COOKIES, BASE_URL

    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url)
    response.raise_for_status()
    COOKIES = response.cookies

    runs_endpoint = get_runs_from_course_endpoint(
        course_alias=COURSE_ALIAS,
        assignment_alias=ASSIGNMENT_ALIAS,
    )

    runs_url = f"{BASE_URL}/{runs_endpoint}"
    runs = requests.get(runs_url, cookies=COOKIES).json()["runs"]

    yield runs

def test_verify_feedback(get_runs):
    """Test to verify that the feedback was posted."""
    global COOKIES, BASE_URL

    runs = get_runs

    assert len(runs) == 1, "No runs found for the course and assignment."
    run_guid = runs[0]["guid"]

    feedback_endpoint = get_runs_submission_feedback_endpoint(run_guid)
    feedback_url = f"{BASE_URL}/{feedback_endpoint}"

    response = requests.get(feedback_url, cookies=COOKIES)
    response.raise_for_status()

    feedbacks = response.json()
    logging.info(f"Feedbacks: {feedbacks}")
    assert len(feedbacks) > 1, "Feedback is empty."
    

    



    
