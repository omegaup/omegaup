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
@pytest.fixture
def get_first_run():
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

    assert len(runs) == 1, "No runs found for the course and assignment."
    return runs[0]["guid"]

def test_verify_feedback(get_first_run):
    """Test to verify that the feedback was posted."""
    global COOKIES, BASE_URL

    run_guid = get_first_run
    feedback_endpoint = get_runs_submission_feedback_endpoint(run_guid)
    feedback_url = f"{BASE_URL}/{feedback_endpoint}"

    response = requests.get(feedback_url, cookies=COOKIES)
    response.raise_for_status()

    feedbacks = response.json()
    assert len(feedbacks) > 1, "Feedback is empty."
    

    



    
