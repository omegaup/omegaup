"""
This script adds a teaching assistant to the omegaup platform.
"""

import json
import time
import argparse
from getpass import getpass
import urllib.parse
import logging
from typing import Callable, Any
import requests
from tqdm import tqdm  # type: ignore
from tqdm.contrib.logging import logging_redirect_tqdm  # type: ignore
from openai import OpenAI  # type: ignore

logging.basicConfig(level=logging.INFO)
logging.getLogger("httpx").setLevel(logging.WARNING)
LOG = logging.getLogger(__name__)

KEY = None
USERNAME = None
PASSWORD = None
LANGUAGE = None
COURSE_ALIAS = None
ASSIGNMENT_ALIAS = None
TA_FEEDBACK_INDICATOR = None
SKIP_CONFIRM = False

BASE_URL = "https://omegaup.com"
COOKIES = None
CLIENT = None


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


def extract_show_run_ids() -> list[tuple[str, str]]:
    """
    Extracts show-run IDs and usernames from the course.

    Returns:
        list: List of all the latest (at most 30 days old) run IDs and the
        usernames from the course
    """
    runs = get_contents_from_url(
        get_runs_from_course_endpoint,
        {"course_alias": COURSE_ALIAS, "assignment_alias": ASSIGNMENT_ALIAS},
    )["runs"]

    current_time = int(time.time())
    a_month_ago = current_time - (30 * 24 * 60 * 60)

    run_ids_and_usernames = [
        (item["guid"], item["username"])
        for item in runs
        if item["time"] >= a_month_ago
    ]

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

    response = CLIENT.chat.completions.create(  # type: ignore[attr-defined]
        model="gpt-4o",
        messages=[{"role": "user", "content": prompt}],
        temperature=temperature,
        max_tokens=500,
    )
    response_text = response.choices[0].message.content

    if not is_initial_feedback and len(response_text) > 1000:
        LOG.warning(
            "The response is too long. Trying to make it concise."
        )
        concise_request = (
            f"Can you make the following response concise and try to limit it "
            f"within 1000 characters? {response_text}"
        )

        response = (
            CLIENT.chat.completions.create(  # type: ignore[attr-defined]
                model="gpt-4o",
                messages=[{"role": "user", "content": concise_request}],
                temperature=temperature,
                max_tokens=500,
            )
        )
        response_text = response.choices[0].message.content

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
        if line == "general advices":
            continue
        feedback_list = (
            '[{"lineNumber": ' + str(line) + ', "feedback": "'
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
                    "assignment_alias": ASSIGNMENT_ALIAS,
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
                oracle_feedback = json.loads(oracle_feedback)
                process_initial_feedback(
                    oracle_feedback,
                    run_id,
                    COURSE_ALIAS,
                    ASSIGNMENT_ALIAS
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
        for index, (run_id, user_name) in enumerate(
            tqdm(run_ids_and_usernames)
        ):
            process_single_run(index, run_id, user_name, total_runs)


def handle_input() -> None:
    """
    Handles input from the user
    """
    global USERNAME, PASSWORD  # pylint: disable=W0603
    global COURSE_ALIAS, ASSIGNMENT_ALIAS, LANGUAGE  # pylint: disable=W0603
    global KEY, TA_FEEDBACK_INDICATOR, SKIP_CONFIRM  # pylint: disable=W0603
    parser = argparse.ArgumentParser(
        description="Process feedbacks from students"
    )
    parser.add_argument("--username", type=str, help="Your username")
    parser.add_argument("--password", type=str, help="Your password")
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
    parser.add_argument("--key", type=str, help="API key for OpenAI")
    parser.add_argument(
        "--skip-confirm",
        action="store_true",
        help="Skip confirmation prompts"
    )
    args = parser.parse_args()

    USERNAME = args.username or input("Enter your username: ")
    PASSWORD = args.password or getpass("Enter your password: ")
    COURSE_ALIAS = args.course_alias or input("Enter the course alias: ")
    ASSIGNMENT_ALIAS = (
        args.assignment_alias or input("Enter the assignment alias: ")
    )
    LANGUAGE = args.language or input(
        'Enter the language (e.g. "Spanish", "English", "Portuguese"): '
    )
    TA_FEEDBACK_INDICATOR = args.ta_feedback_indicator or input(
        "As these feedbacks are AI generated, the input string will be"
        " added to the feedback. \n(Default: Ese mensaje fue generado por un"
        " modelo de inteligencia artificial.)\nPlease enter the string: "
    ) or "Ese mensaje fue generado por un modelo de inteligencia artificial."
    KEY = args.key or getpass("Enter your OpenAI API key: ")
    SKIP_CONFIRM = args.skip_confirm


def main() -> None:
    """
    Takes input and process the feedbacks
    """
    global CLIENT  # pylint: disable=W0603

    handle_input()

    CLIENT = OpenAI(api_key=KEY)

    process_feedbacks()


if __name__ == "__main__":
    main()
