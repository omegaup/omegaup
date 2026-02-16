# pylint: disable=C0302
"""
This script adds a teaching assistant to the omegaup platform.
"""

import argparse
import json
import logging
import os
import sys
import time
import urllib.parse
from getpass import getpass
from typing import Callable, Any

import requests
from tqdm import tqdm  # type: ignore
from tqdm.contrib.logging import logging_redirect_tqdm  # type: ignore

sys.path.append(os.path.join(os.path.dirname(__file__), '..'))
from llm_wrapper import LLMWrapper


class TeachingAssistantError(Exception):
    """Base exception for teaching assistant errors"""


class APIError(TeachingAssistantError):
    """Exception for API-related errors"""


class LLMError(TeachingAssistantError):
    """Exception for LLM-related errors"""


class ConfigurationError(TeachingAssistantError):
    """Exception for configuration errors"""


class DataError(TeachingAssistantError):
    """Exception for data processing errors"""


logging.basicConfig(level=logging.INFO)
logging.getLogger("httpx").setLevel(logging.WARNING)
LOG = logging.getLogger(__name__)

TA_FEEDBACK_INDICATOR: str | None = None
KEY: str | None = None
USERNAME: str | None = None
PASSWORD: str | None = None
LANGUAGE: str | None = None
COURSE_ALIAS: str | None = None
ASSIGNMENT_ALIAS: str | None = None
SKIP_CONFIRM = False
LLM_PROVIDER: str | None = None
SUBMISSION_ID_MODE: str | None = None
SUBMISSION_ID = None
STUDENT_NAME = None

BASE_URL = "https://omegaup.com"
COOKIES = None
CLIENT: LLMWrapper | None = None


def get_login_endpoint(  # pylint: disable=unused-argument
    username: str,
    password: str,
) -> str:
    """endpoint for logging in (use POST with usernameOrEmail and password)"""
    return "api/user/login/"


def get_problem_details_endpoint(problem_alias: str) -> str:
    """endpoint for getting problem details"""
    return f"api/problem/details?problem_alias={problem_alias}"


def get_problem_solution_endpoint(problem_alias: str) -> str:
    """endpoint for getting problem solution"""
    return f"api/problem/solution?problem_alias={problem_alias}"


def get_runs_endpoint(run_alias: str) -> str:
    """endpoint for getting runs"""
    return f"api/run/details?run_alias={run_alias}"


def get_runs_submission_feedback_endpoint(run_alias: str) -> str:
    """endpoint for getting runs submission feedback"""
    return f"api/run/getSubmissionFeedback?run_alias={run_alias}"


def set_submission_feedback_endpoint(  # pylint: disable=R0913
    run_alias: str,
    course_alias: str,
    assignment_alias: str,
    feedback: str,
    line_number: int,
    submission_feedback_id: str,
) -> str:
    """endpoint for setting submission feedback"""
    return (
        f"api/submission/setFeedback?"
        f"guid={run_alias}&"
        f"course_alias={course_alias}&"
        f"assignment_alias={assignment_alias}&"
        f"feedback={feedback}&"
        f"range_bytes_start={line_number}&"
        f"submission_feedback_id={submission_feedback_id}"
    )


def set_submission_feedback_list_endpoint(
    run_alias: str,
    course_alias: str,
    assignment_alias: str,
    feedback_list: str,
) -> str:
    """endpoint for setting submission feedback list"""
    return (
        f"api/submission/setFeedbackList?"
        f"guid={run_alias}&"
        f"course_alias={course_alias}&"
        f"assignment_alias={assignment_alias}&"
        f"feedback_list={feedback_list}"
    )


def get_runs_from_course_endpoint(
    course_alias: str,
    assignment_alias: str,
    rowcount: int | None = None,
    offset: int | None = None
) -> str:
    """
    returns the list of run_ids and corresponding_users from last 30 days.
    """
    endpoint = (
        f"/api/course/runs?"
        f"course_alias={course_alias}&"
        f"assignment_alias={assignment_alias}"
    )

    if rowcount is not None:
        endpoint += f"&rowcount={rowcount}"
    if offset is not None:
        endpoint += f"&offset={offset}"
    return endpoint


def get_course_assignments_endpoint(course_alias: str) -> str:
    """endpoint for getting course assignments"""
    return f"api/course/listAssignments?course_alias={course_alias}"


def get_contents_from_url(  # pylint: disable=R0912,R0915
    get_endpoint_fn: Callable[..., str],
    args: dict[str, Any] | None = None,
    use_post: bool = False,
) -> Any:
    """hit the endpoint with a request (POST for mutating, GET otherwise)"""
    global COOKIES  # pylint: disable=W0603

    if args is None:
        args = {}

    try:
        endpoint = get_endpoint_fn(**args)
        url = f"{BASE_URL}/{endpoint}"

        if get_endpoint_fn == get_login_endpoint:  # pylint: disable=W0143
            COOKIES = None
            response = requests.post(
                url,
                data={
                    "usernameOrEmail": args["username"],
                    "password": args["password"],
                },
                timeout=10,
            )
            response.raise_for_status()
            COOKIES = response.cookies
        elif COOKIES is None:
            if use_post:
                response = requests.post(url, timeout=10)
            else:
                response = requests.get(url, timeout=10)
            response.raise_for_status()
            COOKIES = response.cookies
        else:
            if use_post:
                response = requests.post(
                    url, cookies=COOKIES, timeout=10
                )
            else:
                response = requests.get(
                    url, cookies=COOKIES, timeout=10
                )
            response.raise_for_status()

        try:
            data = response.json()
        except json.JSONDecodeError as e:
            LOG.error("Failed to decode JSON response from %s: %s", url, e)
            LOG.error("Response content: %s", response.text[:500])
            raise APIError(f"Invalid JSON response from {url}") from e

        return data
    except requests.exceptions.Timeout as e:
        LOG.error("Request timeout for %s: %s", url, e)
        raise APIError(f"Request timeout for {url}") from e
    except requests.exceptions.ConnectionError as e:
        LOG.error("Connection error for %s: %s", url, e)
        raise APIError(
            f"Connection error for {url}. "
            "Please check if the server is running."
        ) from e
    except requests.exceptions.HTTPError as e:
        LOG.error("HTTP error for %s: %s", url, e)
        if e.response.status_code == 401:
            raise APIError(
                "Authentication failed. Please check your credentials."
            ) from e
        if e.response.status_code == 403:
            raise APIError(
                "Access denied. Please check your permissions."
            ) from e
        if e.response.status_code == 404:
            raise APIError(f"Resource not found: {url}") from e
        raise APIError(
            f"HTTP error {e.response.status_code} for {url}"
        ) from e
    except requests.exceptions.RequestException as e:
        LOG.error("Request error for %s: %s", url, e)
        raise APIError(f"Request failed for {url}") from e
    except Exception as e:
        LOG.error("Unexpected error in get_contents_from_url: %s", e)
        raise APIError("Unexpected error during API request") from e


def extract_show_run_ids() -> list[tuple[str, str, str, str]]:
    # pylint: disable=R1702
    """
    Extracts show-run IDs, usernames, assignment aliases,
    and verdicts from the course.

    Returns:
        list: List of tuples containing
        (run_id, username, assignment_alias, verdict)
        for all the latest (at most 30 days old) runs from the course
    """
    try:
        if SUBMISSION_ID_MODE == "true":
            if (isinstance(SUBMISSION_ID, str) and
                    isinstance(STUDENT_NAME, str)):
                try:
                    run_details = get_contents_from_url(
                        get_runs_endpoint, {"run_alias": SUBMISSION_ID}
                    )
                    verdict = ""
                    if (
                        "details" in run_details
                        and "verdict" in run_details["details"]
                    ):
                        verdict = run_details["details"]["verdict"]
                    return [
                        (
                            SUBMISSION_ID,
                            STUDENT_NAME,
                            ASSIGNMENT_ALIAS, verdict
                        )
                    ]
                except Exception as e:  # pylint: disable=broad-except
                    LOG.warning(
                        "Could not fetch verdict for submission %s: %s",
                        SUBMISSION_ID,
                        e
                    )
                    return [
                        (SUBMISSION_ID, STUDENT_NAME, ASSIGNMENT_ALIAS, "")
                    ]

        assignments_to_process = []
        if ASSIGNMENT_ALIAS:
            assignments_to_process = [ASSIGNMENT_ALIAS]
        else:
            assignments_data = get_contents_from_url(
                get_course_assignments_endpoint,
                {"course_alias": COURSE_ALIAS}
            )
            if "assignments" not in assignments_data:
                raise KeyError("No assignments found in course response")
            assignments_to_process = [
                assignment["alias"]
                for assignment in assignments_data["assignments"]
            ]

        current_time = int(time.time())
        a_month_ago = current_time - (30 * 24 * 60 * 60)

        run_ids_and_usernames = []

        for assignment_alias in assignments_to_process:
            runs_data = get_contents_from_url(
                get_runs_from_course_endpoint,
                {
                    "course_alias": COURSE_ALIAS,
                    "assignment_alias": assignment_alias
                },
            )

            if "runs" not in runs_data:
                LOG.warning(
                    "No runs found for assignment %s", assignment_alias
                )
                continue

            runs = runs_data["runs"]

            assignment_runs = [
                (
                    item["guid"],
                    item["username"],
                    assignment_alias,
                    item.get("verdict", "")
                )
                for item in runs
                if (
                    "time" in item and
                    "guid" in item and
                    "username" in item and
                    "suggestions" in item and
                    item["time"] >= a_month_ago and
                    item["suggestions"] > 0
                )
            ]
            run_ids_and_usernames.extend(assignment_runs)

        return run_ids_and_usernames
    except KeyError as e:
        LOG.error("Missing required data in API response: %s", e)
        raise DataError(
            "Invalid API response format when extracting run IDs."
        ) from e
    except (APIError, LLMError) as e:
        LOG.error("Critical error extracting run IDs: %s", e)
        raise
    except Exception as e:
        LOG.error("Error extracting run IDs: %s", e)
        raise DataError("Failed to extract run IDs from course.") from e


def extract_feedback_thread(run_alias: str) -> list[list[dict[str, Any]]]:
    # pylint: disable=R1702
    """
    Extracts feedback thread from a run.

    Returns:
    list: List of feedback threads
    """
    try:
        submission_feedback_requests = get_contents_from_url(
            get_runs_submission_feedback_endpoint, {"run_alias": run_alias}
        )

        conversations = []
        for feedback_request in submission_feedback_requests:
            try:
                conversation = []
                conversation.append({
                    "line_number": feedback_request.get("range_bytes_start")
                })
                conversation.append({
                    "feedback_id":
                        feedback_request.get("submission_feedback_id")
                })

                author = feedback_request.get("author")
                feedback_text = feedback_request.get("feedback")
                if author and feedback_text:
                    conversation.append({author: feedback_text})

                if "feedback_thread" in feedback_request:
                    for feedback in feedback_request["feedback_thread"]:
                        thread_author = feedback.get("author")
                        thread_text = feedback.get("text")
                        if thread_author and thread_text:
                            conversation.append({thread_author: thread_text})

                conversations.append(conversation)
            except (KeyError, TypeError) as e:
                LOG.warning("Skipping malformed feedback request: %s", e)
                continue

        return conversations
    except (APIError, LLMError) as e:
        LOG.error(
            "Critical error extracting feedback thread for run %s: %s",
            run_alias, e
        )
        raise
    except Exception as e:
        LOG.error(
            "Error extracting feedback thread for run %s: %s", run_alias, e
        )
        raise DataError(
            f"Failed to extract feedback thread for run {run_alias}."
        ) from e


def conjure_query(  # pylint: disable=R0913
    problem_statement: str,
    solution_statement: str,
    source_code: str,
    feedback: str,
    user_name: str,
    line_number: int,
    is_conversation: bool,
    verdict: str = "",
) -> str:
    """
    Conjures a string that can be used as a prompt to the LLM.

    Returns:
    string: Conjured query
    """
    verdict_guidance = {
        "WA": (
            "The submission got Wrong Answer (WA). Extract the logic "
            "from the code and see what's wrong. "
            "Focus on algorithmic correctness and edge cases."
        ),
        "PA": (
            "The submission got Partial Acceptance (PA). "
            "The algorithm might be partially correct. What's the issue? "
            "What optimizations can be done to pass all the test cases? "
            "Look for efficiency improvements."
        ),
        "AC": (
            "The submission was Accepted (AC). "
            "Congratulate the student! Comment on "
            "alternative approaches if relevant."
        ),
        "TLE": (
            "The submission got Time Limit Exceeded (TLE). "
            "What's causing the time limit to exceed? "
            "Focus on algorithmic complexity and optimization."
        ),
        "MLE": (
            "The submission got Memory Limit Exceeded (MLE). "
            "Where can the memory "
            "be optimized? Look for unnecessary memory usage."
        ),
        "OLE": (
            "The submission got Output Limit Exceeded (OLE). "
            "Is there an infinite "
            "loop or extra print statements? Check output format."
        ),
        "RTE": (
            "The submission got Runtime Error (RTE). "
            "Is there a division by zero or "
            "an array out of bounds? Look for runtime exceptions."
        ),
        "CE": (
            "The submission got Compilation Error (CE). "
            "Where is the compilation "
            "error? Check syntax and language-specific issues."
        ),
        "JE": (
            "The submission got Judge Error (JE). "
            "This is a system issue, not a student error."
        ),
        "VE": (
            "The submission got Validator Error (VE). "
            "This is a system issue, not a student error."
        ),
    }

    verdict_context = verdict_guidance.get(verdict, "")
    if verdict and verdict_context:
        verdict_info = f"\nSubmission Verdict: {verdict}\n"
        verdict_info += f"Guidance: {verdict_context}\n"
    else:
        verdict_info = ""

    conjured_query = ""
    if is_conversation:
        conjured_query = (
            f"The problem statement is: {problem_statement}\n"
            f"The solution is: {solution_statement}\n"
            f"The Source code is: {source_code}\n"
            f"{verdict_info}"
            f"Note the line number: {line_number}\n"
            f"Remember that you are {USERNAME} "
            f"and the student is {user_name}\n"
            f"The conversation is: {feedback}"
            f"Please just return text that continues the conversation, "
            f"return no json in this case."
        )
    else:
        conjured_query = (
            f"The problem statement is: {problem_statement}\n"
            f"The solution is: {solution_statement}\n"
            f"The Source code is: {source_code}\n"
            f"{verdict_info}"
            f"Please give feedback on the source code "
            f"using the above chain of thoughts.\n"
            f"Just return the json, don't use markdown to include ```.\n"
        )
    return conjured_query


def get_prompt(query_content: str) -> str:
    """Get the prompt from the .\teaching_assistant_prompt.txt file"""
    try:
        with open(
            "./teaching_assistant_prompt.txt", "r", encoding='utf-8'
        ) as file:
            prompt = file.read()
        return prompt.format(LANGUAGE=LANGUAGE, query_content=query_content)
    except FileNotFoundError as e:
        LOG.error("Prompt file not found: %s", e)
        raise ConfigurationError(
            "Teaching assistant prompt file not found. "
            "Please ensure 'teaching_assistant_prompt.txt' exists "
            "in the current directory."
        ) from e
    except IOError as e:
        LOG.error("Error reading prompt file: %s", e)
        raise ConfigurationError(
            "Failed to read teaching assistant prompt file."
        ) from e
    except Exception as e:
        LOG.error("Unexpected error formatting prompt: %s", e)
        raise ConfigurationError(
            "Failed to format teaching assistant prompt."
        ) from e


def query_llm(
    query_content: str,
    is_initial_feedback: bool = True,
    temperature: float = 0.0
) -> Any:
    """
    Queries the LLM and returns the response.

    Returns:
    string: Response from the LLM
    """
    try:
        prompt = get_prompt(query_content=query_content)

        if CLIENT is None:
            raise LLMError("CLIENT is not initialized")

        response_text = CLIENT.generate_response(prompt, temperature)

        if not is_initial_feedback and len(response_text) > 1000:
            LOG.warning(
                "The response is too long. Trying to make it concise."
            )
            concise_request = (
                "Can you make the following response concise and try to "
                "limit it within 1000 characters? " + response_text
            )

            response_text = CLIENT.generate_response(
                concise_request, temperature
            )

        return response_text
    except LLMError:
        raise
    except Exception as e:
        LOG.error("Error querying LLM: %s", e)
        raise LLMError("Failed to get response from LLM.") from e


def process_initial_feedback(
    ta_feedback: dict[str, Any] | None,
    show_run_id: str | None,
    course_alias: str | None,
    assignment_alias: str | None
) -> None:
    """
    Gives initial feedback when a students asks for help to correct a
    submission

    Returns:
    None
    """
    if ta_feedback is None:
        return

    try:
        for line, feedback in ta_feedback.items():
            try:
                targeted_line = "0" if line == "general advices" else line
                feedback_text = (
                    str(TA_FEEDBACK_INDICATOR) + " " + feedback
                )[:1000]
                feedback_list = (
                    '[{"lineNumber": ' + targeted_line + ', "feedback": "'
                    + feedback_text + '"}]'
                )
                if not SKIP_CONFIRM:
                    print("It is an initial feedback.")
                    print(f"The response is:\n {feedback_list}")
                    user_response = input(
                        "Do you want to post this response? (yes/no): "
                    ).strip().lower()
                    print_horizontal_line()
                    if user_response != "yes":
                        return
                get_contents_from_url(
                    set_submission_feedback_list_endpoint,
                    {
                        "run_alias": show_run_id,
                        "course_alias": course_alias,
                        "assignment_alias": assignment_alias,
                        "feedback_list": feedback_list,
                    },
                    use_post=True,
                )
            except (KeyError, TypeError) as e:
                LOG.warning("Skipping malformed feedback item: %s", e)
                continue
            except KeyboardInterrupt:
                LOG.info("User interrupted feedback processing")
                raise
            except requests.exceptions.RequestException as e:
                LOG.error("Error posting feedback: %s", e)
                continue
    except (LLMError, APIError) as e:
        LOG.error("Critical error processing initial feedback: %s", e)
        raise
    except Exception as e:
        LOG.error("Error processing initial feedback: %s", e)
        raise DataError("Failed to process initial feedback.") from e


def print_horizontal_line() -> None:
    """Prints a horizontal line"""
    print("-" * 80)


def print_horizontal_double_line() -> None:
    """Prints a horizontal double line"""
    print("=" * 80)


def handle_feedbacks(  # pylint: disable=R0913,R0912,R0915
        user_name: str,
        index: int,
        total_runs: int,
        run_id: str,
        assignment_alias: str,
        problem_alias: str,
        source_content: str,
        problem_content: str,
        problem_solution: str,
        feedbacks: list[list[dict[str, Any]]],
        verdict: str = "",
) -> None:
    """
    Handles feedbacks for a single run

    Returns:
    None
    """
    if len(feedbacks) == 0:
        return

    is_initial_feedback = len(feedbacks) == 1

    for feedback in feedbacks:  # pylint: disable=R1702
        try:
            if user_name not in feedback[-1]:
                continue

            try:
                line_number = feedback[0].get("line_number")
                feedback_id = feedback[1].get("feedback_id")
            except (IndexError, KeyError, TypeError) as e:
                LOG.warning("Malformed feedback structure, skipping: %s", e)
                continue

            conjured_query = conjure_query(
                problem_content,
                problem_solution,
                source_content,
                str(feedback[2:]),
                user_name,
                line_number if line_number is not None else 0,
                line_number is not None,
                verdict,
            )

            if line_number is not None:
                try:
                    if not SKIP_CONFIRM:
                        print_horizontal_double_line()
                        print(f"The question is:\n {problem_content}")
                        print_horizontal_line()
                        print(f"The solution is:\n {source_content}")
                        print_horizontal_line()
                    oracle_feedback = query_llm(
                        conjured_query, is_initial_feedback=False
                    )
                    if len(oracle_feedback) >= 1000:
                        LOG.error(
                            "The response is still too long. "
                            "Trimming it to the first 1000 characters."
                        )
                    if not SKIP_CONFIRM:
                        print(
                            f"The last question asked was:\n {feedback[-1]}"
                        )
                        print_horizontal_line()
                        print(
                            "The response is:\n "
                            + str(TA_FEEDBACK_INDICATOR)
                            + " "
                            + oracle_feedback[:1000]
                        )
                        print_horizontal_line()

                        user_response = input(
                            "Do you want to post this response? (yes/no): "
                        ).strip().lower()
                        print_horizontal_line()
                        if user_response != "yes":
                            continue
                    get_contents_from_url(
                        set_submission_feedback_endpoint,
                        {
                            "run_alias": run_id,
                            "course_alias": COURSE_ALIAS,
                            "assignment_alias": assignment_alias,
                            "feedback": urllib.parse.quote(
                                (
                                    str(TA_FEEDBACK_INDICATOR)
                                    +
                                    " "
                                    +
                                    oracle_feedback
                                )[:1000]
                            ),
                            "line_number": line_number,
                            "submission_feedback_id": feedback_id,
                        },
                        use_post=True,
                    )
                    LOG.info(
                        "Request %s out of %s from user %s on %s: DONE",
                        index + 1,
                        total_runs,
                        user_name,
                        problem_alias,
                    )
                except KeyboardInterrupt:
                    LOG.info("User interrupted feedback processing")
                    raise
                except LLMError as e:
                    LOG.error("LLM error processing feedback with "
                              "line number: %s", e)
                    raise
                except APIError as e:
                    LOG.error("API error processing feedback with "
                              "line number: %s", e)
                    raise
                except Exception as e:  # pylint: disable=broad-except
                    LOG.error("Error processing feedback with "
                              "line number: %s", e)
                    continue
            else:
                if is_initial_feedback:
                    try:
                        if not SKIP_CONFIRM:
                            print_horizontal_double_line()
                            print(f"The question is:\n {problem_content}")
                            print_horizontal_line()
                            print(f"The solution is:\n {source_content}")
                            print_horizontal_line()
                        oracle_feedback = query_llm(
                            conjured_query,
                        )
                        oracle_feedback = oracle_feedback.strip()
                        if oracle_feedback.startswith("```json"):
                            oracle_feedback = oracle_feedback.removeprefix(
                                "```json"
                            ).strip()
                        if oracle_feedback.endswith("```"):
                            oracle_feedback = oracle_feedback.removesuffix(
                                "```"
                            ).strip()

                        try:
                            oracle_feedback = json.loads(oracle_feedback)
                        except json.JSONDecodeError as e:
                            LOG.error("Failed to parse JSON response "
                                      "from LLM: %s", e)
                            LOG.error("Raw response: %s", oracle_feedback)
                            continue

                        process_initial_feedback(
                            oracle_feedback,
                            run_id,
                            COURSE_ALIAS,
                            assignment_alias
                        )
                        LOG.info(
                            "Request %s out of %s from user %s on %s: DONE",
                            index + 1,
                            total_runs,
                            user_name,
                            problem_alias,
                        )
                    except KeyboardInterrupt:
                        LOG.info("User interrupted feedback processing")
                        raise
                    except LLMError as e:
                        LOG.error("LLM error processing initial "
                                  "feedback: %s", e)
                        raise
                    except APIError as e:
                        LOG.error("API error processing initial "
                                  "feedback: %s", e)
                        raise
                    except Exception as e:  # pylint: disable=broad-except
                        LOG.error("Error processing initial feedback: %s", e)
                        continue
        except KeyboardInterrupt:
            LOG.info("User interrupted feedback processing")
            raise
        except LLMError as e:
            LOG.error("LLM error processing feedback for user %s: %s",
                      user_name, e)
            raise
        except APIError as e:
            LOG.error("API error processing feedback for user %s: %s",
                      user_name, e)
            raise
        except Exception as e:  # pylint: disable=broad-except
            LOG.error("Error processing feedback for user %s: %s",
                      user_name, e)
            continue


def process_single_run(  # pylint: disable=R0913
    index: int,
    run_id: str,
    username: str,
    assignment_alias: str,
    total_runs: int,
    verdict: str = ""
) -> None:
    """
    Processes a single feedback

    Returns:
    None
    """
    try:
        run_details = get_contents_from_url(
            get_runs_endpoint, {"run_alias": run_id}
        )

        if "alias" not in run_details:
            LOG.error("No problem alias found in run details for run %s",
                      run_id)
            return
        if "source" not in run_details:
            LOG.error("No source code found in run details for run %s", run_id)
            return

        problem_alias = run_details["alias"]
        source_content = run_details["source"]

        problem_details = get_contents_from_url(
            get_problem_details_endpoint, {"problem_alias": problem_alias}
        )

        if ("statement" not in problem_details or
                "markdown" not in problem_details["statement"]):
            LOG.error("No problem statement found for problem %s",
                      problem_alias)
            return

        problem_content = problem_details["statement"]["markdown"]

        try:
            problem_solution_data = get_contents_from_url(
                get_problem_solution_endpoint, {"problem_alias": problem_alias}
            )
            if ("solution" in problem_solution_data and
                    "markdown" in problem_solution_data["solution"]):
                problem_solution = (
                    problem_solution_data["solution"]["markdown"]
                )
            else:
                problem_solution = ""
        except requests.exceptions.HTTPError:
            problem_solution = ""
        except Exception as e:  # pylint: disable=broad-except
            LOG.warning("Error fetching problem solution for %s: %s",
                        problem_alias, e)
            problem_solution = ""

        feedbacks = extract_feedback_thread(run_id)

        handle_feedbacks(
            username,
            index,
            total_runs,
            run_id,
            assignment_alias,
            problem_alias,
            source_content,
            problem_content,
            problem_solution,
            feedbacks,
            verdict
        )
    except KeyError as e:
        LOG.error("Missing required data in run details for %s: %s", run_id, e)
        raise DataError(f"Invalid run details format for run {run_id}.") from e
    except (LLMError, APIError) as e:
        LOG.error("Critical error processing run %s: %s", run_id, e)
        raise
    except Exception as e:
        LOG.error("Error processing run %s: %s", run_id, e)
        raise DataError(f"Failed to process run {run_id}.") from e


def process_feedbacks() -> None:
    """
    Processes feedback requests from students using LLM oracle.

    Returns:
    None
    """
    try:
        login_response = get_contents_from_url(
            get_login_endpoint, {"username": USERNAME, "password": PASSWORD}
        )
        if login_response.get("status") != "ok":
            raise APIError("Login failed. Please check your credentials.")

        run_ids_and_usernames = extract_show_run_ids()
        if not run_ids_and_usernames:
            LOG.warning("No runs found to process")
            return

        total_runs = len(run_ids_and_usernames)
        LOG.info("Processing %d runs", total_runs)

        successful_runs = 0
        failed_runs = 0

        with logging_redirect_tqdm():
            for index, (run_id,
                        user_name,
                        assignment_alias,
                        verdict
                        ) in enumerate(
                tqdm(run_ids_and_usernames)
            ):
                try:
                    process_single_run(
                        index,
                        run_id,
                        user_name,
                        assignment_alias,
                        total_runs,
                        verdict
                    )
                    successful_runs += 1
                except KeyboardInterrupt:
                    LOG.info("User interrupted processing")
                    raise
                except (LLMError, APIError) as e:
                    LOG.error("Critical error processing run %s for "
                              "user %s: %s", run_id, user_name, e)
                    failed_runs += 1
                    if isinstance(e, LLMError):
                        LOG.error("LLM connection issues detected. "
                                  "Stopping processing.")
                        raise
                    if (isinstance(e, APIError) and
                            "Authentication failed" in str(e)):
                        LOG.error("Authentication failed. "
                                  "Stopping processing.")
                        raise
                    continue
                except Exception as e:  # pylint: disable=broad-except
                    LOG.error("Error processing run %s for user %s: %s",
                              run_id, user_name, e)
                    failed_runs += 1
                    continue

        LOG.info("Processing completed: %d successful, %d failed out of "
                 "%d total runs", successful_runs, failed_runs, total_runs)

        if failed_runs > 0:
            raise DataError(f"Processing completed with {failed_runs} "
                            f"failed runs out of {total_runs} total")
    except KeyboardInterrupt:
        LOG.info("Processing interrupted by user")
        raise
    except (LLMError, APIError, DataError) as e:
        LOG.error("Critical error processing feedbacks: %s", e)
        raise
    except Exception as e:
        LOG.error("Unexpected error processing feedbacks: %s", e)
        raise DataError("Failed to process feedbacks due to "
                        "unexpected error.") from e


def handle_input() -> None:  # pylint: disable=R0915, R0912
    """
    Handles input from the user
    """
    global USERNAME, PASSWORD  # pylint: disable=W0603
    global COURSE_ALIAS, ASSIGNMENT_ALIAS, LANGUAGE  # pylint: disable=W0603
    global KEY, TA_FEEDBACK_INDICATOR, SKIP_CONFIRM  # pylint: disable=W0603
    global LLM_PROVIDER  # pylint: disable=W0603
    global SUBMISSION_ID_MODE, SUBMISSION_ID  # pylint: disable=W0603
    global STUDENT_NAME  # pylint: disable=W0603
    try:
        parser = argparse.ArgumentParser(
            description="Process feedbacks from students"
        )
        parser.add_argument("--username", type=str, help="Your username")
        parser.add_argument("--password", type=str, help="Your password")
        parser.add_argument(
            "--submission_id_mode",
            type=str,
            help="true if you want to process a single submission."
        )
        parser.add_argument(
            "--test_mode",
            action="store_true",
            help="Run in local server."
        )
        parser.add_argument(
            "--submission_id",
            type=str,
            help="Submission ID to process feedbacks for"
        )
        parser.add_argument(
            "--student_name",
            type=str,
            help="Student name to process feedbacks for"
        )
        parser.add_argument(
            "--course_alias",
            type=str,
            help="Course alias to process feedbacks for"
        )
        parser.add_argument(
            "--assignment_alias",
            type=str,
            help="Assignment alias to process feedbacks for"
        )
        parser.add_argument(
            "--language", type=str, help="Language to use for feedbacks"
        )
        parser.add_argument(
            "--ta_feedback_indicator",
            type=str,
            help="Indicates that it's a TA feedback"
        )
        parser.add_argument("--key", type=str,
                            help="API key for the LLM provider")
        parser.add_argument(
            "--llm",
            type=str,
            default="deepseek",
            choices=["claude", "gpt", "deepseek", "gemini", "omegaup"],
            help="LLM provider to use (default: deepseek)"
        )
        parser.add_argument(
            "--skip-confirm",
            action="store_true",
            help="Skip confirmation prompts"
        )
        args = parser.parse_args()
        if args.test_mode:
            global BASE_URL  # pylint: disable=W0603
            BASE_URL = "http://localhost:8001"

        try:
            USERNAME = args.username or input("Enter your username: ")
            PASSWORD = args.password or getpass("Enter your password: ")
            SUBMISSION_ID_MODE = args.submission_id_mode
            if SUBMISSION_ID_MODE not in ["true", "false"]:
                SUBMISSION_ID_MODE = input(
                    "Are you working in submission id mode: "
                )
            if SUBMISSION_ID_MODE == "true":
                SUBMISSION_ID = args.submission_id or input(
                    "Enter the submission id: "
                )
                STUDENT_NAME = (args.student_name or
                                input("Enter the student name: "))
            COURSE_ALIAS = (args.course_alias or
                            input("Enter the course alias: "))
            ASSIGNMENT_ALIAS = (args.assignment_alias or input(
                "Enter the assignment alias (leave empty to process "
                "all assignments): ") or None)
            LANGUAGE = (args.language or input(
                'Enter the language (e.g. "Spanish", "English", '
                '"Portuguese"): '))
            TA_FEEDBACK_INDICATOR = (args.ta_feedback_indicator or input(
                "As these feedbacks are AI generated, the input string "
                "will be added to the feedback. \n(Default: Ese mensaje "
                "fue generado por un modelo de inteligencia artificial.)"
                "\nPlease enter the string: "
            ) or "Ese mensaje fue generado por un modelo de "
                 "inteligencia artificial.")
            LLM_PROVIDER = args.llm
            provider_name = LLM_PROVIDER.upper() if LLM_PROVIDER else "LLM"
            KEY = args.key or getpass(f"Enter your {provider_name} API key: ")
            SKIP_CONFIRM = args.skip_confirm
        except KeyboardInterrupt:
            LOG.info("User interrupted input")
            raise
        except EOFError as exc:
            LOG.error("Unexpected end of input")
            raise ConfigurationError("Input terminated unexpectedly") from exc
        except Exception as e:
            LOG.error("Error during input collection: %s", e)
            raise ConfigurationError("Failed to collect required input") from e

        if not USERNAME or not PASSWORD:
            raise ConfigurationError("Username and password are required")
        if not COURSE_ALIAS:
            raise ConfigurationError("Course alias is required")
        if not LANGUAGE:
            raise ConfigurationError("Language is required")
        if not KEY:
            raise ConfigurationError("API key is required")
        if (SUBMISSION_ID_MODE == "true" and
                (not SUBMISSION_ID or not STUDENT_NAME)):
            raise ConfigurationError(
                "Submission ID and student name are required in "
                "submission ID mode"
            )

    except ConfigurationError:
        raise
    except Exception as e:
        LOG.error("Error handling input: %s", e)
        raise ConfigurationError("Failed to handle input parameters") from e


def main() -> None:
    """
    Takes input and process the feedbacks
    """
    global CLIENT  # pylint: disable=W0603

    try:
        handle_input()

        if LLM_PROVIDER is None or KEY is None:
            raise ConfigurationError("LLM_PROVIDER and KEY must be set")

        try:
            CLIENT = LLMWrapper(LLM_PROVIDER, KEY)
        except Exception as e:
            LOG.error("Failed to initialize LLM client: %s", e)
            raise LLMError(
                "Failed to initialize LLM client. Please check "
                "your API key and provider."
            ) from e

        process_feedbacks()
        LOG.info("Successfully completed processing all feedbacks")

    except KeyboardInterrupt:
        LOG.info("Program interrupted by user")
        sys.exit(1)
    except ConfigurationError as e:
        LOG.error("Configuration error: %s", e)
        sys.exit(1)
    except LLMError as e:
        LOG.error("LLM error: %s", e)
        sys.exit(1)
    except APIError as e:
        LOG.error("API error: %s", e)
        sys.exit(1)
    except DataError as e:
        LOG.error("Data processing error: %s", e)
        sys.exit(1)
    except TeachingAssistantError as e:
        LOG.error("Teaching assistant error: %s", e)
        sys.exit(1)
    except Exception as e:  # pylint: disable=broad-except
        LOG.error("Unexpected error: %s", e)
        sys.exit(1)


if __name__ == "__main__":
    main()
