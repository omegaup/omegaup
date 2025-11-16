"""Tests if the teaching assistant redis queue integration works.
The pytest assumes the course setup is already done (from previous tests),
adds a student feedback, queues it to redis queue, starts the worker,
waits for processing, and then checks if the feedback is posted by the AI.
"""
import json
import logging
import os
import subprocess
import sys
import time
import uuid
from typing import Any

import pytest
import requests

sys.path.append(os.path.join(os.path.dirname(__file__), '..'))
from teaching_assistant_worker.redis_client import RedisJobClient

from teaching_assistant import (
    get_login_endpoint,
    get_runs_from_course_endpoint,
    get_runs_submission_feedback_endpoint,
    set_submission_feedback_endpoint
)
from test_ta_submission_mode import (
    TEACHER_USERNAME,
    TEACHER_PASSWORD,
    STUDENT_USERNAME,
    STUDENT_PASSWORD,
    BASE_URL,
    COURSE_ALIAS,
    ASSIGNMENT_ALIAS,
)

logging.basicConfig(level=logging.INFO)
COOKIES = None


@pytest.fixture
def redis_client() -> RedisJobClient:
    """Fixture to create a Redis client for testing."""
    redis_config = {
        'host': os.getenv('REDIS_HOST', 'localhost'),
        'port': int(os.getenv('REDIS_PORT', '6379')),
        'password': os.getenv('REDIS_PASSWORD', 'redis'),
        'db': int(os.getenv('REDIS_DB', '0')),
        'timeout': int(os.getenv('REDIS_TIMEOUT', '30'))
    }
    
    try:
        redis_client = RedisJobClient(redis_config)
        if redis_client.client:
            redis_client.client.ping()
            logging.info("Redis connection established successfully")
        return redis_client
    except Exception as e:
        pytest.skip(f"Couldn't connect to Redis server: {e}")


@pytest.fixture
def extract_submission_id() -> Any:
    """Fixture to extract the submission ID from the course (assumes setup already done)."""
    global COOKIES  # pylint: disable=W0603

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
    assert len(runs) >= 1

    guid = runs[0]['guid']
    yield guid


@pytest.fixture
def extract_feedback_id(
    extract_submission_id: Any  # pylint: disable=W0621, W0613
) -> Any:
    """Fixture to extract the feedback ID from the submission."""
    global COOKIES  # pylint: disable=W0603

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
    """Fixture to add student feedback that will be processed via Redis queue."""
    global COOKIES  # pylint: disable=W0603
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
        feedback="May you check again using redis queue?",
        line_number=1,
        submission_feedback_id=feedback_id
    )
    submission_feedback_url = f"{BASE_URL}/{submission_feedback_endpoint}"

    response = requests.get(
        submission_feedback_url,
        timeout=30,
        cookies=COOKIES
    )
    response.raise_for_status()
    yield guid


@pytest.fixture
def queue_redis_job(
    redis_client: RedisJobClient,  # pylint: disable=W0621, W0613
    add_student_feedback: Any  # pylint: disable=W0621, W0613
) -> Any:
    """Fixture to queue a teaching assistant job to Redis."""
    guid = add_student_feedback
    job_id = str(uuid.uuid4())
    
    job_data = {
        'job_id': job_id,
        'username': TEACHER_USERNAME,
        'password': TEACHER_PASSWORD,
        'course_alias': COURSE_ALIAS,
        'assignment_alias': ASSIGNMENT_ALIAS,
        'language': 'English',
        'ta_feedback_indicator': 'AI generated ',
        'run_id': guid,  # This is the submission ID
        'student_name': STUDENT_USERNAME,
        'created_at': time.time()
    }
    
    queue_name = 'teaching_assistant_jobs'
    job_json = json.dumps(job_data)
    
    if not redis_client.client:
        raise RuntimeError("Redis client not initialized")

    queue_length_before = redis_client.client.llen(queue_name)
    assert queue_length_before == 0
    
    redis_client.client.lpush(queue_name, job_json)
    
    queue_length_after = redis_client.client.llen(queue_name)
    assert queue_length_after == 1
    
    queued_jobs = redis_client.client.lrange(queue_name, 0, -1)
    assert len(queued_jobs) == 1
    
    redis_client.set_job_status(job_id, {
        'status': 'queued',
        'created_at': time.time()
    })
    
    logging.info("Queued test job %s for submission %s", job_id, guid)
    yield job_id


@pytest.fixture
def run_redis_worker(
    queue_redis_job: Any  # pylint: disable=W0621, W0613
) -> Any:
    """Fixture to run the Redis worker and process the job."""
    job_id = queue_redis_job
    
    env = os.environ.copy()
    env.update({
        'OMEGAUP_USERNAME': TEACHER_USERNAME,
        'OMEGAUP_PASSWORD': TEACHER_PASSWORD,
        'REDIS_HOST': os.getenv('REDIS_HOST', 'localhost'),
        'REDIS_PORT': os.getenv('REDIS_PORT', '6379'),
        'REDIS_PASSWORD': os.getenv('REDIS_PASSWORD', 'redis'),
        'REDIS_DB': os.getenv('REDIS_DB', '0'),
        'OMEGAUP_API_KEY': 'omegaup',
    })
    
    command = [
        "python", "worker.py",
        "--worker-id", "0"
    ]
    
    worker_process = None
    try:
        worker_cwd = os.path.join(os.path.dirname(__file__), "..", "teaching_assistant_worker")
        
        worker_process = subprocess.Popen(
            command,
            cwd=worker_cwd,
            env=env,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True
        )
        
        logging.info("Started Redis worker with PID %s", worker_process.pid)

        start_time = time.time()
        timeout = 30
        
        while time.time() - start_time < timeout:
            if worker_process.poll() is not None:
                stdout, stderr = worker_process.communicate()
                print("=== WORKER OUTPUT ===")
                print("STDOUT:")
                print(stdout)
                print("STDERR:")
                print(stderr)
                print("=== END WORKER OUTPUT ===")
                logging.info("Worker finished with return code: %s", worker_process.returncode)
                break
            time.sleep(1)
        else:
            worker_process.terminate()
            try:
                stdout, stderr = worker_process.communicate(timeout=5)
                print("=== WORKER OUTPUT (TIMEOUT) ===")
                print("STDOUT:")
                print(stdout)
                print("STDERR:")
                print(stderr)
                print("=== END WORKER OUTPUT ===")
            except subprocess.TimeoutExpired:
                worker_process.kill()
                worker_process.wait()
                logging.warning("Worker killed after timeout")
        
        yield job_id
        
    except Exception as e:
        logging.error("Failed to start Redis worker: %s", e)
        raise
    finally:
        # Clean up the worker process
        if worker_process and worker_process.poll() is None:
            worker_process.terminate()
            try:
                worker_process.wait(timeout=5)
            except subprocess.TimeoutExpired:
                worker_process.kill()
                worker_process.wait()
            logging.info("Redis worker process terminated")


def test_redis_queue_integration(
    redis_client: RedisJobClient,  # pylint: disable=W0621, W0613
    extract_submission_id: Any,  # pylint: disable=W0621, W0613
    run_redis_worker: Any  # pylint: disable=W0621, W0613
) -> None:
    """Test the complete Redis queue integration for teaching assistant."""
    global COOKIES  # pylint: disable=W0603
    
    guid = extract_submission_id
    job_id = run_redis_worker
    
    job_status = redis_client.get_job_status(job_id)
    logging.info("Job status: %s", job_status)
    
    login_endpoint = get_login_endpoint(TEACHER_USERNAME, TEACHER_PASSWORD)
    login_url = f"{BASE_URL}/{login_endpoint}"

    response = requests.get(login_url, timeout=30)
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
    assert len(feedback_thread) == 4
