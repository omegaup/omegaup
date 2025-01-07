import requests
import json
import re
import argparse
from getpass import getpass
import urllib.parse
from tqdm import tqdm
from openai import OpenAI


KEY = None
USERNAME = None
PASSWORD = None
LANGUAGE = None
COURSE_ALIAS = None

BASE_URL = "https://omegaup.com"
COOKIES = None
client = None


def get_login_endpoint(username, password):
    """endpoint for logging in"""
    return f"api/user/login?usernameOrEmail={username}&password={password}"


def get_problems_endpoint():
    """endpoint for getting problems"""
    return "api/problem/list/"


def get_problem_details_endpoint(problem_alias):
    """endpoint for getting problem details"""
    return f"api/problem/details?problem_alias={problem_alias}"


def get_problem_clarifications_endpoint(problem_alias):
    """endpoint for getting problem clarifications"""
    return f"api/problem/clarifications?problem_alias={problem_alias}"


def get_problem_solution_endpoint(problem_alias):
    """endpoint for getting problem solution"""
    return f"api/problem/solution?problem_alias={problem_alias}"


def get_notifications_endpoint():
    """endpoint for fetching notifications"""
    return "api/notification/myList/"


def read_notification_endpoint(notification_ids):
    """endpoint for reading notification"""
    return f"api/notification/readNotifications?notifications={notification_ids}"


def get_runs_endpoint(run_alias):
    """endpoint for getting runs"""
    return f"api/run/details?run_alias={run_alias}"


def get_runs_submission_feedback_endpoint(run_alias):
    """endpoint for getting runs submission feedback"""
    return f"api/run/getSubmissionFeedback?run_alias={run_alias}"


def set_submission_feedback_endpoint(
    run_alias,
    course_alias,
    assignment_alias,
    feedback,
    line_number,
    submission_feedback_id,
):
    """endpoint for setting submission feedback"""
    return f"api/submission/setFeedback?guid={run_alias}&course_alias={course_alias}&assignment_alias={assignment_alias}&feedback={feedback}&range_bytes_start={line_number}&submission_feedback_id={submission_feedback_id}"


def set_submission_feedback_list_endpoint(
    run_alias, course_alias, assignment_alias, feedback_list
):
    """endpoint for setting submission feedback list"""
    return f"api/submission/setFeedbackList?guid={run_alias}&course_alias={course_alias}&assignment_alias={assignment_alias}&feedback_list={feedback_list}"


def get_contents_from_url(get_endpoint_fn, args=None):
    """hit the endpoint with GET request"""
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client
    global COOKIES
    global BASE_URL

    if args is None:
        args = {}
    endpoint = get_endpoint_fn(**args)
    url = f"{BASE_URL}/{endpoint}"

    if get_endpoint_fn == get_login_endpoint:
        COOKIES = None

    try:
        if COOKIES is None:
            response = requests.get(url)
            response.raise_for_status()
            COOKIES = response.cookies
        else:
            response = requests.get(url, COOKIES)
            response.raise_for_status()
        data = response.json()
        return data
    except requests.exceptions.RequestException as e:
        raise
    except json.JSONDecodeError as e:
        raise


def extract_show_run_ids():
    """
    Extracts show-run IDs from thread notifications.

    Returns:
        list: List of show-run IDs that need feedback
    """
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client
    notifications = get_contents_from_url(get_notifications_endpoint)["notifications"]
    submission_feedback_requests = []

    for notification in notifications:
        if notification.get("contents", {}).get("type") in [
            "course-request-feedback",
            "course-submission-feedback-thread",
        ]:
            url = notification.get("contents", {}).get("body", {}).get("url", "")
            submission_feedback_request = {}
            if "show-run:" in url:
                show_run_id = url.split("show-run:")[-1]
                submission_feedback_request["show_run_id"] = show_run_id
            pattern = r"/course/(?P<course_alias>[^/]+)/assignment/(?P<assignment_alias>[^/]+)/(?:#problems/(?P<problem_alias>[^/]+))?"
            match = re.search(pattern, url)
            if match:
                submission_feedback_request["course_alias"] = match.group(
                    "course_alias"
                )
                submission_feedback_request["assignment_alias"] = match.group(
                    "assignment_alias"
                )
                submission_feedback_request["problem_alias"] = match.group(
                    "problem_alias"
                )

            submission_feedback_request["userName"] = (
                notification.get("contents", {})
                .get("body", {})
                .get("localizationParams", {})
                .get("username", "")
            )

            if submission_feedback_request["course_alias"] == COURSE_ALIAS:
                get_contents_from_url(
                    read_notification_endpoint,
                    {"notification_ids": str(notification["notification_id"])},
                )

            if (
                submission_feedback_request["show_run_id"]
                not in [
                    feedback_request["show_run_id"]
                    for feedback_request in submission_feedback_requests
                ]
                and submission_feedback_request["course_alias"] == COURSE_ALIAS
            ):
                submission_feedback_requests.append(submission_feedback_request)

    return submission_feedback_requests


def extract_feedback_thread(run_alias):
    """
    Extracts feedback thread from a run.

    Returns:
    list: List of feedback threads
    """
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client
    submission_feedback_requests = get_contents_from_url(
        get_runs_submission_feedback_endpoint, {"run_alias": run_alias}
    )

    conversations = []
    for feedback_request in submission_feedback_requests:
        conversation = []
        conversation.append({"line_number": feedback_request["range_bytes_start"]})
        conversation.append({"feedback_id": feedback_request["submission_feedback_id"]})
        conversation.append({feedback_request["author"]: feedback_request["feedback"]})

        if "feedback_thread" in feedback_request:
            for feedback in feedback_request["feedback_thread"]:
                conversation.append({feedback["author"]: feedback["text"]})

        conversations.append(conversation)

    return conversations


def conjure_query(
    problem_statement,
    solution_statement,
    source_code,
    feedback,
    user_name,
    line_number,
    is_conversation,
):
    """
    Conjures a string that can be used as a prompt to the LLM.

    Returns:
    string: Conjured query
    """
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client
    conjured_query = ""
    if is_conversation:
        conjured_query = (
            f"The problem statement is: {problem_statement}\n"
            f"The solution is: {solution_statement}\n"
            f"The Source code is: {source_code}\n\n"
            f"Note the line number: {line_number}\n"
            f"Remember that you are {USERNAME} and the student is {user_name}\n"
            f"The conversation is: {str(feedback)}"
            f"Please just return text that continues the conversation, return no json in this case."
        )
    else:
        conjured_query = (
            f"The problem statement is: {problem_statement}\n"
            f"The solution is: {solution_statement}\n"
            f"The Source code is: {source_code}\n\n"
            f"Please give feedback on the source code using the above chain of thoughts.\n"
            f"Just return the json, don't use markdown to include ```.\n"
        )
    return conjured_query


def query_LLM(query_content, is_initial_feedback=True, temperature=0):
    """
    Queries the LLM and returns the response.

    Returns:
    string: Response from the LLM
    """
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client

    prompt = f"You are a teaching assistant, and your goal is to help students with specific programming-related queries without directly providing full solutions. Follow these steps to guide users based on their query type: \
1) When a student asks for a topic explanation (for example, \"Binary Search\"), provide a detailed breakdown of the concept without solving any specific problems. \
2) If a student asks for a question explanation, ensure that you describe the problem's details, clarifying requirements, constraints, and logic without offering any code. \
3) If the student requests hints for a problem, give guidance on approaching the problem (example, breaking it down, algorithms to consider) without revealing the final code. \
4) When asked why a solution is wrong, do the following: First, analyze the student's solution and determine if it is on the right track or completely off. If it's off-track, gently point out that the approach needs reconsideration. If the solution is on the right track, identify the approach the student has taken (example, brute force, two-pointers, hash table, etc.). If the approach is inefficient or incorrect (example, brute force for large inputs), suggest that the student consider more optimal techniques. \
5) Carefully examine the code for syntax errors and provide specific feedback on those issues. \
6) If you find any logical error in the code, gently point out the mistake but do not give the solution for the mistake \
7) When asked if a solution is correct or not, do not answer. \
8) If a student is getting wrong answer for some general mistake (for example, not leaving space between two numbers, asking for input by typing a message instead of a standard input etc.), do point that out. \
9) If you see any irrelevant print statement, ask the student to comment out that particular statement. \
10) Before giving any remarks or responses, solve the problem on your own and then compare your solution to the student's solution. \
11) If asked to explain the solution, give one or two hints or explain the logic that can help students arrive at the correct solution. \
12) Remember, your goal is to facilitate the teaching process and not to provide the solution directly. \
13) Keep your message clear and concise in less than 150 to 200 words. \
14) If a code snippet is submitted, return the answer in the json format only.The line number (0-indexed) of the feedback should be the key, general advices should be under 'general advices' key. \
15) If only a general question is asked and no code snippet is submitted, return the output in normal text format (not in json format). \
16) Please return the response in {LANGUAGE}.\
17) Keeping all those in mind, please answer the following query: {query_content}."

    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[{"role": "user", "content": prompt}],
        temperature=temperature,
        max_tokens=500,
    )
    response_text = response.choices[0].message.content

    if not is_initial_feedback and len(response_text) > 1000:
        concise_request = f"Can you make the following response concise and try to limit it within 1000 characters? {response_text}"
        response = client.chat.completions.create(
            model="gpt-4o",
            messages=[{"role": "user", "content": concise_request}],
            temperature=temperature,
            max_tokens=500,
        )
        response_text = response.choices[0].message.content

    return response_text


def process_initial_feedback(TA_feedback, show_run_id, course_alias, assignment_alias):
    """
    Gives initial feedback when a students asks for help to correct a submission

    Returns:
    None
    """
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client
    for line, feedback in TA_feedback.items():
        if line == "general advices":
            continue
        feedback_list = (
            '[{"lineNumber": ' + str(line) + ', "feedback": "' + feedback[:1000] + '"}]'
        )
        get_contents_from_url(
            set_submission_feedback_list_endpoint,
            {
                "run_alias": show_run_id,
                "course_alias": course_alias,
                "assignment_alias": assignment_alias,
                "feedback_list": feedback_list,
            },
        )


def process_feedbacks():
    """
    Processes feedback requests from students using LLM oracle.

    Returns:
    None
    """
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client
    get_contents_from_url(
        get_login_endpoint, {"username": USERNAME, "password": PASSWORD}
    )
    feedbacks_required = extract_show_run_ids()
    for feedback_required in tqdm(feedbacks_required):
        show_run_id = feedback_required["show_run_id"]
        course_alias = feedback_required["course_alias"]
        assignment_alias = feedback_required["assignment_alias"]
        problem_alias = feedback_required["problem_alias"]
        user_name = feedback_required["userName"]

        source_content = get_contents_from_url(
            get_runs_endpoint, {"run_alias": show_run_id}
        )["source"]

        if problem_alias is None:
            problem_alias = get_contents_from_url(
                get_runs_endpoint, {"run_alias": show_run_id}
            )["alias"]

        problem_content = get_contents_from_url(
            get_problem_details_endpoint, {"problem_alias": problem_alias}
        )["statement"]["markdown"]
        problem_solution = get_contents_from_url(
            get_problem_solution_endpoint, {"problem_alias": problem_alias}
        )["solution"]["markdown"]

        feedbacks = extract_feedback_thread(show_run_id)

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
                feedback[2:],
                user_name,
                line_number,
                line_number != None,
            )
            if line_number is not None:
                oracle_feedback = query_LLM(conjured_query, is_initial_feedback=False)
                get_contents_from_url(
                    set_submission_feedback_endpoint,
                    {
                        "run_alias": show_run_id,
                        "course_alias": course_alias,
                        "assignment_alias": assignment_alias,
                        "feedback": urllib.parse.quote(oracle_feedback[:1000]),
                        "line_number": line_number,
                        "submission_feedback_id": feedback_id,
                    },
                )
            else:
                if is_initial_feedback:
                    oracle_feedback = query_LLM(
                        conjured_query,
                    )
                    oracle_feedback = json.loads(oracle_feedback)
                    process_initial_feedback(
                        oracle_feedback, show_run_id, course_alias, assignment_alias
                    )


def main():
    global USERNAME, PASSWORD, COURSE_ALIAS, LANGUAGE, KEY, client
    parser = argparse.ArgumentParser(description="Process feedbacks from students")
    parser.add_argument("--username", type=str, help="Your username")
    parser.add_argument("--password", type=str, help="Your password")
    parser.add_argument(
        "--course_alias", type=str, help="Course alias to process feedbacks for"
    )
    parser.add_argument("--language", type=str, help="Language to use for feedbacks")
    parser.add_argument("--key", type=str, help="API key for OpenAI")
    args = parser.parse_args()

    USERNAME = args.username or input("Enter your username: ")
    PASSWORD = args.password or getpass("Enter your password: ")
    COURSE_ALIAS = args.course_alias or input("Enter the course alias: ")
    LANGUAGE = args.language or input("Enter the language: ")
    KEY = args.key or getpass("Enter your OpenAI API key: ")

    client = OpenAI(api_key=KEY)

    process_feedbacks()


if __name__ == "__main__":
    main()
