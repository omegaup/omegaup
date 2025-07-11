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

logging.basicConfig(level=logging.INFO)
logging.getLogger("httpx").setLevel(logging.WARNING)
LOG = logging.getLogger(__name__)

KEY: str | None = None
USERNAME: str | None = None
PASSWORD: str | None = None
LANGUAGE: str | None = None
COURSE_ALIAS: str | None = None
ASSIGNMENT_ALIAS: str | None = None
TA_FEEDBACK_INDICATOR: str | None = None
SKIP_CONFIRM = False
LLM_PROVIDER: str | None = None
SUBMISSION_ID_MODE: str | None = None
SUBMISSION_ID = None
STUDENT_NAME = None


BASE_URL = "https://omegaup.com"
COOKIES = None
CLIENT: LLMWrapper | None = None


def get_login_endpoint(username: str, password: str) -> str:
    """endpoint for logging in"""
    return f"api/user/login?usernameOrEmail={username}&password={password}"


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


def get_contents_from_url(
    get_endpoint_fn: Callable[..., str],
    args: dict[str, Any] | None = None
) -> Any:
    """hit the endpoint with GET request"""
    global COOKIES  # pylint: disable=W0603

    if args is None:
        args = {}
    endpoint = get_endpoint_fn(**args)
    url = f"{BASE_URL}/{endpoint}"

    if get_endpoint_fn == get_login_endpoint:  # pylint: disable=W0143
        COOKIES = None

    try:
        if COOKIES is None:
            response = requests.get(url, timeout=10)
            response.raise_for_status()
            COOKIES = response.cookies
        else:
            response = requests.get(url, COOKIES, timeout=10)
            response.raise_for_status()
        data = response.json()
        return data
    except requests.exceptions.RequestException as e:
        LOG.error("An error occurred during the request: %s", e)
        raise
    except json.JSONDecodeError as e:
        LOG.error("JSON decoding failed: %s", e)
        raise


def extract_show_run_ids() -> list[tuple[str, str, str]]:
    """
    Extracts show-run IDs, usernames, and assignment aliases from the course.

    Returns:
        list: List of tuples containing (run_id, username, assignment_alias)
              for all the latest (at most 30 days old) runs from the course
    """
    if SUBMISSION_ID_MODE:
        if isinstance(SUBMISSION_ID, str) and isinstance(STUDENT_NAME, str):
            # In submission ID mode, we still need assignment alias
            return [(SUBMISSION_ID, STUDENT_NAME, ASSIGNMENT_ALIAS)]

    # Get all assignments if no specific assignment alias is provided
    assignments_to_process = []
    if ASSIGNMENT_ALIAS:
        assignments_to_process = [ASSIGNMENT_ALIAS]
    else:
        assignments = get_contents_from_url(
            get_course_assignments_endpoint,
            {"course_alias": COURSE_ALIAS}
        )
        assignments_to_process = [
            assignment["alias"] for assignment in assignments["assignments"]
        ]

    current_time = int(time.time())
    a_month_ago = current_time - (30 * 24 * 60 * 60)

    run_ids_and_usernames = []

    for assignment_alias in assignments_to_process:
        runs = get_contents_from_url(
            get_runs_from_course_endpoint,
            {
                "course_alias": COURSE_ALIAS,
                "assignment_alias": assignment_alias
            },
        )["runs"]

        assignment_runs = [
            (item["guid"], item["username"], assignment_alias)
            for item in runs
            if item["time"] >= a_month_ago
        ]
        run_ids_and_usernames.extend(assignment_runs)

    return run_ids_and_usernames


def extract_feedback_thread(run_alias: str) -> list[list[dict[str, Any]]]:
    """
    Extracts feedback thread from a run.

    Returns:
    list: List of feedback threads
    """
    submission_feedback_requests = get_contents_from_url(
        get_runs_submission_feedback_endpoint, {"run_alias": run_alias}
    )

    conversations = []
    for feedback_request in submission_feedback_requests:
        conversation = []
        conversation.append({
            "line_number": feedback_request["range_bytes_start"]
        })
        conversation.append({
            "feedback_id": feedback_request["submission_feedback_id"]
        })
        conversation.append({
            feedback_request["author"]: feedback_request["feedback"]
        })

        if "feedback_thread" in feedback_request:
            for feedback in feedback_request["feedback_thread"]:
                conversation.append({feedback["author"]: feedback["text"]})

        conversations.append(conversation)

    return conversations


def conjure_query(  # pylint: disable=R0913
    problem_statement: str,
    solution_statement: str,
    source_code: str,
    feedback: str,
    user_name: str,
    line_number: int,
    is_conversation: bool,
) -> str:
    """
    Conjures a string that can be used as a prompt to the LLM.

    Returns:
    string: Conjured query
    """
    conjured_query = ""
    if is_conversation:
        conjured_query = (
            f"The problem statement is: {problem_statement}\n"
            f"The solution is: {solution_statement}\n"
            f"The Source code is: {source_code}\n\n"
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
            f"The Source code is: {source_code}\n\n"
            f"Please give feedback on the source code "
            f"using the above chain of thoughts.\n"
            f"Just return the json, don't use markdown to include ```.\n"
        )
    return conjured_query


def get_prompt(query_content: str) -> str:
    """Get the prompt from the .\teaching_assistant_prompt.txt file"""
    with open(
        "./teaching_assistant_prompt.txt", "r", encoding='utf-8'
    ) as file:
        prompt = file.read()
    return prompt.format(LANGUAGE=LANGUAGE, query_content=query_content)


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

    prompt = get_prompt(query_content=query_content)

    if CLIENT is None:
        raise ValueError("CLIENT is not initialized")

    response_text = CLIENT.generate_response(prompt, temperature)

    if not is_initial_feedback and len(response_text) > 1000:
        LOG.warning(
            "The response is too long. Trying to make it concise."
        )
        concise_request = (
            f"Can you make the following response concise and try to limit it "
            f"within 1000 characters? {response_text}"
        )

        response_text = CLIENT.generate_response(concise_request, temperature)

    return response_text


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
    for line, feedback in ta_feedback.items():
        targeted_line = "0" if line == "general advices" else line
        feedback_list = (
            '[{"lineNumber": ' + targeted_line + ', "feedback": "'
            + (str(TA_FEEDBACK_INDICATOR) + " " + feedback)[:1000] + '"}]'
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
        )


def print_horizontal_line() -> None:
    """Prints a horizontal line"""
    print("-" * 80)


def print_horizontal_double_line() -> None:
    """Prints a horizontal double line"""
    print("=" * 80)


def handle_feedbacks(  # pylint: disable=R0913
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
) -> None:
    """
    Handles feedbacks for a single run

    Returns:
    None
    """
    if len(feedbacks) == 0:
        return

    is_initial_feedback = len(feedbacks) == 1

    for feedback in feedbacks:
        if user_name not in feedback[-1]:
            continue
        line_number = feedback[0]["line_number"]
        feedback_id = feedback[1]["feedback_id"]
        conjured_query = conjure_query(
            problem_content,
            problem_solution,
            source_content,
            str(feedback[2:]),
            user_name,
            line_number,
            line_number is not None,
        )
        if line_number is not None:
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
                }
            )
            LOG.info(
                "Request %s out of %s from user %s on %s: DONE",
                index + 1,
                total_runs,
                user_name,
                problem_alias,
            )
        else:
            if is_initial_feedback:
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
                oracle_feedback = json.loads(oracle_feedback)
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


def process_single_run(
    index: int,
    run_id: str,
    username: str,
    assignment_alias: str,
    total_runs: int
) -> None:
    """
    Processes a single feedback

    Returns:
    None
    """
    run_details = get_contents_from_url(
        get_runs_endpoint, {"run_alias": run_id}
    )

    problem_alias = run_details["alias"]

    source_content = run_details["source"]

    problem_content = get_contents_from_url(
        get_problem_details_endpoint, {"problem_alias": problem_alias}
    )["statement"]["markdown"]

    try:
        problem_solution = get_contents_from_url(
            get_problem_solution_endpoint, {"problem_alias": problem_alias}
        )["solution"]["markdown"]
    except requests.exceptions.HTTPError:
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
        feedbacks
    )


def process_feedbacks() -> None:
    """
    Processes feedback requests from students using LLM oracle.

    Returns:
    None
    """
    get_contents_from_url(
        get_login_endpoint, {"username": USERNAME, "password": PASSWORD}
    )
    run_ids_and_usernames = extract_show_run_ids()
    total_runs = len(run_ids_and_usernames)
    with logging_redirect_tqdm():
        for index, (run_id, user_name, assignment_alias) in enumerate(
            tqdm(run_ids_and_usernames)
        ):
            process_single_run(
                index, run_id, user_name, assignment_alias, total_runs
            )


def handle_input() -> None:
    """
    Handles input from the user
    """
    global USERNAME, PASSWORD  # pylint: disable=W0603
    global COURSE_ALIAS, ASSIGNMENT_ALIAS, LANGUAGE  # pylint: disable=W0603
    global KEY, TA_FEEDBACK_INDICATOR, SKIP_CONFIRM  # pylint: disable=W0603
    global LLM_PROVIDER  # pylint: disable=W0603
    global SUBMISSION_ID_MODE, SUBMISSION_ID  # pylint: disable=W0603
    global STUDENT_NAME  # pylint: disable=W0603
    parser = argparse.ArgumentParser(
        description="Process feedbacks from students"
    )
    parser.add_argument("--username", type=str, help="Your username")
    parser.add_argument("--password", type=str, help="Your password")
    parser.add_argument(
        "--test_mode",
        action="store_true",
        help="Run in local server."
    )
    if args.test_mode:
        global BASE_URL  # pylint: disable=W0603
        BASE_URL = "http://localhost:8001"
    parser.add_argument(
        "--submission_id_mode",
        type=str,
        help="true if you want to process a single submission."
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
    parser.add_argument("--key", type=str, help="API key for the LLM provider")
    parser.add_argument(
        "--llm",
        type=str,
        default="deepseek",
        choices=["claude", "gpt", "deepseek", "gemini"],
        help="LLM provider to use (default: deepseek)"
    )
    parser.add_argument(
        "--skip-confirm",
        action="store_true",
        help="Skip confirmation prompts"
    )
    args = parser.parse_args()
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
        STUDENT_NAME = args.student_name or input("Enter the student name: ")
    COURSE_ALIAS = args.course_alias or input("Enter the course alias: ")
    ASSIGNMENT_ALIAS = args.assignment_alias or input(
        "Enter the assignment alias (leave empty to process all assignments): "
    )
    LANGUAGE = args.language or input(
        'Enter the language (e.g. "Spanish", "English", "Portuguese"): '
    )
    TA_FEEDBACK_INDICATOR = args.ta_feedback_indicator or input(
        "As these feedbacks are AI generated, the input string will be"
        " added to the feedback. \n(Default: Ese mensaje fue generado por un"
        " modelo de inteligencia artificial.)\nPlease enter the string: "
    ) or "Ese mensaje fue generado por un modelo de inteligencia artificial."
    LLM_PROVIDER = args.llm
    provider_name = LLM_PROVIDER.upper() if LLM_PROVIDER else "LLM"
    KEY = args.key or getpass(f"Enter your {provider_name} API key: ")
    SKIP_CONFIRM = args.skip_confirm


def main() -> None:
    """
    Takes input and process the feedbacks
    """
    global CLIENT  # pylint: disable=W0603

    handle_input()

    if LLM_PROVIDER is None or KEY is None:
        raise ValueError("LLM_PROVIDER and KEY must be set")

    CLIENT = LLMWrapper(LLM_PROVIDER, KEY)

    process_feedbacks()


if __name__ == "__main__":
    main()
