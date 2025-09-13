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
from typing import Callable, Any, Dict, List, Optional

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

DEFAULT_BASE_URL = "http://localhost:8001"


class TeachingAssistantClient:
    """Client for interacting with omegaUp API and generating feedback."""

    def __init__(self, username: str, password: str, base_url: str = None):
        """Initialize the teaching assistant client."""
        self.username = username
        self.password = password
        self.base_url = base_url or DEFAULT_BASE_URL
        self.session = requests.Session()
        self.cookies = None
        self.llm_client = None

    def login(self) -> bool:
        """Login to omegaUp and get cookies."""
        try:
            login_url = f"{self.base_url}/api/user/login?usernameOrEmail={self.username}&password={self.password}"
            
            response = self.session.get(login_url)
            response.raise_for_status()
            
            result = response.json()
            if result.get('status') == 'ok':
                self.cookies = response.cookies
                LOG.info("Successfully logged in to omegaUp")
                return True
            else:
                LOG.error("Login failed: %s", result.get('error'))
                return False
                
        except Exception as e:
            LOG.error("Login error: %s", e)
            return False

    def initialize_llm(self, llm_provider: str, api_key: str) -> None:
        """Initialize the LLM client."""
        try:
            self.llm_client = LLMWrapper(llm_provider, api_key)
            LOG.info("LLM client initialized")
        except Exception as e:
            LOG.error("Failed to initialize LLM client: %s", e)
            raise LLMError(f"LLM client initialization failed: {e}")

    def get_contents_from_url(self, get_endpoint_fn: Callable[..., str], args: dict[str, Any] = None) -> Any:
        """Get contents from URL with proper cookie handling, exactly like v1."""
        if args is None:
            args = {}

        try:
            endpoint = get_endpoint_fn(**args)
            url = f"{self.base_url}/{endpoint}"

            if get_endpoint_fn == self.get_login_endpoint:
                self.cookies = None

            if self.cookies is None:
                response = self.session.get(url, timeout=10)
                response.raise_for_status()
                self.cookies = response.cookies
            else:
                response = self.session.get(url, cookies=self.cookies, timeout=10)
                response.raise_for_status()

            try:
                data = response.json()
            except json.JSONDecodeError as e:
                LOG.error("Failed to decode JSON response from %s: %s", url, e)
                raise APIError(f"Invalid JSON response from {url}") from e

            return data
        except requests.exceptions.RequestException as e:
            LOG.error("Request error for %s: %s", url, e)
            raise APIError(f"Request failed for {url}") from e

    def get_login_endpoint(self, username: str, password: str) -> str:
        """endpoint for logging in"""
        return f"api/user/login?usernameOrEmail={username}&password={password}"

    def get_problem_details_endpoint(self, problem_alias: str) -> str:
        """endpoint for getting problem details"""
        return f"api/problem/details?problem_alias={problem_alias}"

    def get_problem_solution_endpoint(self, problem_alias: str) -> str:
        """endpoint for getting problem solution"""
        return f"api/problem/solution?problem_alias={problem_alias}"

    def get_runs_endpoint(self, run_alias: str) -> str:
        """endpoint for getting runs"""
        return f"api/run/details?run_alias={run_alias}"

    def get_runs_submission_feedback_endpoint(self, run_alias: str) -> str:
        """endpoint for getting runs submission feedback"""
        return f"api/run/getSubmissionFeedback?run_alias={run_alias}"

    def set_submission_feedback_endpoint(self, run_alias: str, course_alias: str, assignment_alias: str, feedback: str, line_number: int, submission_feedback_id: str) -> str:
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

    def set_submission_feedback_list_endpoint(self, run_alias: str, course_alias: str, assignment_alias: str, feedback_list: str) -> str:
        """endpoint for setting submission feedback list"""
        return (
            f"api/submission/setFeedbackList?"
            f"guid={run_alias}&"
            f"course_alias={course_alias}&"
            f"assignment_alias={assignment_alias}&"
            f"feedback_list={feedback_list}"
        )

    def get_runs_from_course_endpoint(self, course_alias: str, assignment_alias: str, rowcount: int = None, offset: int = None) -> str:
        """returns the list of run_ids and corresponding_users from last 30 days."""
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

    def get_course_assignments_endpoint(self, course_alias: str) -> str:
        """endpoint for getting course assignments"""
        return f"api/course/listAssignments?course_alias={course_alias}"

    def get_submission_details(self, run_id: str) -> Dict[str, Any]:
        """Get details of a specific submission."""
        endpoint = f"api/run/details?run_alias={run_id}"
        return self.get_contents_from_url(endpoint)

    def get_problem_details(self, problem_alias: str) -> Dict[str, Any]:
        """Get details of a problem."""
        endpoint = f"api/problem/details?problem_alias={problem_alias}"
        return self.get_contents_from_url(endpoint)

    def get_problem_solution(self, problem_alias: str) -> Dict[str, Any]:
        """Get solution of a problem."""
        endpoint = f"api/problem/solution?problem_alias={problem_alias}"
        return self.get_contents_from_url(endpoint)

    def get_course_runs(
        self,
        course_alias: str,
        assignment_alias: str = None,
        student_name: str = None
    ) -> List[Dict[str, Any]]:
        """Get runs from a course assignment with month filtering."""
        endpoint = f"api/course/runs?course_alias={course_alias}"
        if assignment_alias:
            endpoint += f"&assignment_alias={assignment_alias}"
            
        runs_data = self.get_contents_from_url(endpoint)
        
        if "runs" not in runs_data:
            LOG.warning("No runs found for course %s", course_alias)
            return []

        runs = runs_data["runs"]
        
        current_time = int(time.time())
        a_month_ago = current_time - (30 * 24 * 60 * 60)
        
        filtered_runs = [
            run for run in runs
            if (
                "time" in run and
                "guid" in run and
                "username" in run and
                "suggestions" in run and
                run["time"] >= a_month_ago and
                run["suggestions"] > 0 and
                (not student_name or run.get('username') == student_name)
            )
        ]
        
        return filtered_runs

    def get_course_assignments(self, course_alias: str) -> List[Dict[str, Any]]:
        """Get assignments for a course."""
        endpoint = f"api/course/listAssignments?course_alias={course_alias}"
        assignments_data = self.get_contents_from_url(endpoint)
        return assignments_data.get("assignments", [])

    def extract_show_run_ids(
        self,
        course_alias: str,
        assignment_alias: str = None,
        submission_id: str = None,
        student_name: str = None
    ) -> List[tuple[str, str, str]]:
        """Extract run IDs, usernames, and assignment aliases from the course, exactly like v1."""
        try:
            if submission_id and student_name:
                return [(submission_id, student_name, assignment_alias or "")]

            assignments_to_process = []
            if assignment_alias:
                assignments_to_process = [assignment_alias]
            else:
                assignments_data = self.get_contents_from_url(
                    self.get_course_assignments_endpoint,
                    {"course_alias": course_alias}
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
                runs_data = self.get_contents_from_url(
                    self.get_runs_from_course_endpoint,
                    {
                        "course_alias": course_alias,
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
                    (item["guid"], item["username"], assignment_alias)
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
        except Exception as e:
            LOG.error("Error extracting run IDs: %s", e)
            raise DataError("Failed to extract run IDs from course.") from e

    def extract_feedback_thread(self, run_alias: str) -> list[list[dict[str, Any]]]:
        """Extracts feedback thread from a run, exactly like v1."""
        try:
            submission_feedback_requests = self.get_contents_from_url(
                self.get_runs_submission_feedback_endpoint, {"run_alias": run_alias}
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
        except Exception as e:
            LOG.error(
                "Error extracting feedback thread for run %s: %s", run_alias, e
            )
            raise DataError(
                f"Failed to extract feedback thread for run {run_alias}."
            ) from e

    def get_prompt(self, query_content: str, language: str) -> str:
        """Get the prompt from the teaching_assistant_prompt.txt file, like v1."""
        try:
            # Get the path relative to this file's location
            # We need this as we will call this function from worker
            prompt_file_path = os.path.join(
                os.path.dirname(__file__), 
                "teaching_assistant_prompt.txt"
            )
            with open(prompt_file_path, "r", encoding='utf-8') as file:
                prompt = file.read()
            return prompt.format(LANGUAGE=language, query_content=query_content)
        except FileNotFoundError as e:
            LOG.error("Prompt file not found: %s", e)
            raise ConfigurationError(
                "Teaching assistant prompt file not found. "
                "Please ensure 'teaching_assistant_prompt.txt' exists "
                "in the teaching_assistant directory."
            ) from e
        except Exception as e:
            LOG.error("Unexpected error formatting prompt: %s", e)
            raise ConfigurationError(
                "Failed to format teaching assistant prompt."
            ) from e

    def query_llm(
        self,
        query_content: str,
        language: str,
        is_initial_feedback: bool = True,
        temperature: float = 0.0
    ) -> str:
        """Query the LLM and return the response, exactly like v1."""
        try:
            prompt = self.get_prompt(query_content=query_content, language=language)

            if self.llm_client is None:
                raise LLMError("LLM client is not initialized")

            response_text = self.llm_client.generate_response(prompt, temperature)

            if not is_initial_feedback and len(response_text) > 1000:
                LOG.warning(
                    "The response is too long. Trying to make it concise."
                )
                concise_request = (
                    "Can you make the following response concise and try to "
                    "limit it within 1000 characters? " + response_text
                )

                response_text = self.llm_client.generate_response(
                    concise_request, temperature
                )

            return response_text
        except Exception as e:
            LOG.error("Error querying LLM: %s", e)
            raise LLMError("Failed to get response from LLM.") from e

    def conjure_query(
        self,
        problem_statement: str,
        solution_statement: str,
        source_code: str,
        feedback: str,
        user_name: str,
        line_number: int,
        is_conversation: bool,
    ) -> str:
        """Conjures a string that can be used as a prompt to the LLM, exactly like v1."""
        conjured_query = ""
        if is_conversation:
            conjured_query = (
                f"The problem statement is: {problem_statement}\n"
                f"The solution is: {solution_statement}\n"
                f"The Source code is: {source_code}\n\n"
                f"Note the line number: {line_number}\n"
                f"Remember that you are {self.username} "
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

    def set_feedback_list(
        self,
        run_id: str,
        course_alias: str,
        assignment_alias: str,
        feedback_list: str
    ) -> bool:
        """Set feedback list for a submission using GET request like v1."""
        try:
            feedback_encoded = urllib.parse.quote(feedback_list)
            endpoint = (
                f"api/submission/setFeedbackList?"
                f"guid={run_id}&"
                f"course_alias={course_alias}&"
                f"assignment_alias={assignment_alias}&"
                f"feedback_list={feedback_encoded}"
            )
            
            url = f"{self.base_url}/{endpoint}"
            response = self.session.get(url, cookies=self.cookies, timeout=10)
            response.raise_for_status()
            
            result = response.json()
            return result.get('status') == 'ok'
            
        except Exception as e:
            LOG.error("Failed to set feedback list: %s", e)
            return False

    def process_initial_feedback(
        self,
        ta_feedback: Dict[str, Any],
        show_run_id: str,
        course_alias: str,
        assignment_alias: str,
        ta_indicator: str,
        skip_confirm: bool = False
    ) -> None:
        """Gives initial feedback when a students asks for help, exactly like v1."""
        if ta_feedback is None:
            return

        try:
            for line, feedback in ta_feedback.items():
                try:
                    targeted_line = "0" if line == "general advices" else line
                    feedback_text = (
                        str(ta_indicator) + " " + feedback
                    )[:1000]
                    feedback_list = (
                        '[{"lineNumber": ' + targeted_line + ', "feedback": "'
                        + feedback_text + '"}]'
                    )
                    if not skip_confirm:
                        print("It is an initial feedback.")
                        print(f"The response is:\n {feedback_list}")
                        user_response = input(
                            "Do you want to post this response? (yes/no): "
                        ).strip().lower()
                        self.print_horizontal_line()
                        if user_response != "yes":
                            return
                    self.get_contents_from_url(
                        self.set_submission_feedback_list_endpoint,
                        {
                            "run_alias": show_run_id,
                            "course_alias": course_alias,
                            "assignment_alias": assignment_alias,
                            "feedback_list": feedback_list,
                        },
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
        except Exception as e:
            LOG.error("Error processing initial feedback: %s", e)
            raise DataError("Failed to process initial feedback.") from e

    def print_horizontal_line(self) -> None:
        """Prints a horizontal line"""
        print("-" * 80)

    def print_horizontal_double_line(self) -> None:
        """Prints a horizontal double line"""
        print("=" * 80)

    def handle_feedbacks(
        self,
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
        course_alias: str,
        language: str,
        ta_indicator: str,
        skip_confirm: bool = False
    ) -> None:
        """Handles feedbacks for a single run, exactly like v1."""
        if len(feedbacks) == 0:
            return

        is_initial_feedback = len(feedbacks) == 1

        for feedback in feedbacks:
            try:
                if user_name not in feedback[-1]:
                    continue

                try:
                    line_number = feedback[0].get("line_number")
                    feedback_id = feedback[1].get("feedback_id")
                except (IndexError, KeyError, TypeError) as e:
                    LOG.warning("Malformed feedback structure, skipping: %s", e)
                    continue

                conjured_query = self.conjure_query(
                    problem_content,
                    problem_solution,
                    source_content,
                    str(feedback[2:]),
                    user_name,
                    line_number if line_number is not None else 0,
                    line_number is not None,
                )

                if line_number is not None:
                    try:
                        if not skip_confirm:
                            self.print_horizontal_double_line()
                            print(f"The question is:\n {problem_content}")
                            self.print_horizontal_line()
                            print(f"The solution is:\n {source_content}")
                            self.print_horizontal_line()
                        oracle_feedback = self.query_llm(
                            conjured_query, language, is_initial_feedback=False
                        )
                        if len(oracle_feedback) >= 1000:
                            LOG.error(
                                "The response is still too long. "
                                "Trimming it to the first 1000 characters."
                            )
                        if not skip_confirm:
                            print(
                                f"The last question asked was:\n {feedback[-1]}"
                            )
                            self.print_horizontal_line()
                            print(
                                "The response is:\n "
                                + str(ta_indicator)
                                + " "
                                + oracle_feedback[:1000]
                            )
                            self.print_horizontal_line()

                            user_response = input(
                                "Do you want to post this response? (yes/no): "
                            ).strip().lower()
                            self.print_horizontal_line()
                            if user_response != "yes":
                                continue
                        self.get_contents_from_url(
                            self.set_submission_feedback_endpoint,
                            {
                                "run_alias": run_id,
                                "course_alias": course_alias,
                                "assignment_alias": assignment_alias,
                                "feedback": urllib.parse.quote(
                                    (
                                        str(ta_indicator)
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
                    except Exception as e:
                        LOG.error("Error processing feedback with "
                                  "line number: %s", e)
                        continue
                else:
                    if is_initial_feedback:
                        try:
                            if not skip_confirm:
                                self.print_horizontal_double_line()
                                print(f"The question is:\n {problem_content}")
                                self.print_horizontal_line()
                                print(f"The solution is:\n {source_content}")
                                self.print_horizontal_line()
                            oracle_feedback = self.query_llm(
                                conjured_query, language
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

                            self.process_initial_feedback(
                                oracle_feedback,
                                run_id,
                                course_alias,
                                assignment_alias,
                                ta_indicator,
                                skip_confirm
                            )
                            LOG.info(
                                "Request %s out of %s from user %s on %s: DONE",
                                index + 1,
                                total_runs,
                                user_name,
                                problem_alias,
                            )
                        except Exception as e:
                            LOG.error("Error processing initial feedback: %s", e)
                            continue
            except Exception as e:
                LOG.error("Error processing feedback for user %s: %s",
                          user_name, e)
                continue

    def process_single_run(
        self,
        index: int,
        run_id: str,
        username: str,
        assignment_alias: str,
        total_runs: int,
        course_alias: str,
        language: str,
        ta_indicator: str,
        skip_confirm: bool = False
    ) -> None:
        """Processes a single feedback, exactly like v1."""
        try:
            run_details = self.get_contents_from_url(
                self.get_runs_endpoint, {"run_alias": run_id}
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

            problem_details = self.get_contents_from_url(
                self.get_problem_details_endpoint, {"problem_alias": problem_alias}
            )

            if ("statement" not in problem_details or
                    "markdown" not in problem_details["statement"]):
                LOG.error("No problem statement found for problem %s",
                          problem_alias)
                return

            problem_content = problem_details["statement"]["markdown"]

            try:
                problem_solution_data = self.get_contents_from_url(
                    self.get_problem_solution_endpoint, {"problem_alias": problem_alias}
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
            except Exception as e:
                LOG.warning("Error fetching problem solution for %s: %s",
                            problem_alias, e)
                problem_solution = ""

            feedbacks = self.extract_feedback_thread(run_id)

            self.handle_feedbacks(
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
                course_alias,
                language,
                ta_indicator,
                skip_confirm
            )
        except Exception as e:
            LOG.error("Error processing run %s: %s", run_id, e)
            raise DataError(f"Failed to process run {run_id}.") from e

    def process_submissions(
        self,
        course_alias: str,
        assignment_alias: str,
        language: str,
        ta_indicator: str,
        skip_confirm: bool = False,
        submission_id: str = None,
        student_name: str = None
    ) -> None:
        """Process feedback for submissions, exactly like v1."""
        try:
            login_response = self.get_contents_from_url(
                self.get_login_endpoint, {"username": self.username, "password": self.password}
            )
            if login_response.get("status") != "ok":
                raise APIError("Login failed. Please check your credentials.")

            run_ids_and_usernames = self.extract_show_run_ids(
                course_alias, assignment_alias, submission_id, student_name
            )
            
            if not run_ids_and_usernames:
                LOG.warning("No runs found to process")
                return

            total_runs = len(run_ids_and_usernames)
            LOG.info("Processing %d runs", total_runs)

            successful_runs = 0
            failed_runs = 0

            with logging_redirect_tqdm():
                for index, (run_id, user_name, assignment_alias) in enumerate(
                    tqdm(run_ids_and_usernames)
                ):
                    try:
                        self.process_single_run(
                            index, run_id, user_name, assignment_alias, total_runs,
                            course_alias, language, ta_indicator, skip_confirm
                        )
                        successful_runs += 1
                    except Exception as e:
                        LOG.error("Error processing submission %s: %s", run_id, e)
                        failed_runs += 1
                        continue

            LOG.info("Processing completed: %d successful, %d failed out of "
                     "%d total runs", successful_runs, failed_runs, total_runs)

            if failed_runs > 0:
                raise DataError(f"Processing completed with {failed_runs} "
                                f"failed runs out of {total_runs} total")

        except Exception as e:
            LOG.error("Error processing course submissions: %s", e)
            raise

def main() -> None:
    """Main entry point for the Teaching Assistant, exactly like v1."""
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
            "--submission_id",
            type=str,
            help="Submission ID to process (required in submission mode)"
        )
        parser.add_argument(
            "--student_name",
            type=str,
            help="Student name (required in submission mode)"
        )
        
        parser.add_argument(
            "--course_alias",
            type=str,
            help="Course alias"
        )
        parser.add_argument(
            "--assignment_alias",
            type=str,
            help="Assignment alias (optional for course mode)"
        )
        
        parser.add_argument(
            "--llm",
            type=str,
            default="deepseek",
            choices=["claude", "gpt", "deepseek", "gemini", "omegaup"],
            help="LLM provider to use (default: deepseek)"
        )
        parser.add_argument(
            "--key",
            type=str,
            help="API key for the LLM provider"
        )
        parser.add_argument(
            "--language", 
            type=str, 
            help="Language to use for feedbacks"
        )

        parser.add_argument(
            "--ta_feedback_indicator",
            type=str,
            help="AI feedback indicator message"
        )
        parser.add_argument(
            "--skip-confirm",
            action="store_true",
            help="Skip confirmation prompts"
        )
        parser.add_argument(
            "--test_mode",
            action="store_true",
            help="Run against local server"
        )
        
        args = parser.parse_args()
        
        base_url = DEFAULT_BASE_URL
        if args.test_mode:
            base_url = "http://localhost:8001"
            
        try:
            username = args.username or input("Enter your username: ")
            password = args.password or getpass("Enter your password: ")
            
            submission_id_mode = args.submission_id_mode
            if submission_id_mode not in ["true", "false"]:
                submission_id_mode = input("Single submission mode? (true/false): ")
                
            if submission_id_mode == "true":
                submission_id = args.submission_id or input("Enter submission ID: ")
                student_name = args.student_name or input("Enter student name: ")
            else:
                submission_id = None
                student_name = None
                
            course_alias = args.course_alias or input("Enter the course alias: ")
            assignment_alias = args.assignment_alias or input(
                "Enter the assignment alias (leave empty to process "
                "all assignments): ") or None
            language = args.language or input(
                'Enter the language (e.g. "Spanish", "English", '
                '"Portuguese"): ')
            ta_feedback_indicator = args.ta_feedback_indicator or input(
                "As these feedbacks are AI generated, the input string "
                "will be added to the feedback. \n(Default: Ese mensaje "
                "fue generado por un modelo de inteligencia artificial.)"
                "\nPlease enter the string: "
            ) or "Ese mensaje fue generado por un modelo de " \
                 "inteligencia artificial."
            llm_provider = args.llm
            provider_name = llm_provider.upper() if llm_provider else "LLM"
            api_key = args.key or getpass(f"Enter your {provider_name} API key: ")
            
        except KeyboardInterrupt:
            LOG.info("User interrupted input")
            sys.exit(1)
        except Exception as e:
            LOG.error("Error during input collection: %s", e)
            raise ConfigurationError("Failed to collect required input") from e

        if not username or not password:
            raise ConfigurationError("Username and password are required")
        if not course_alias:
            raise ConfigurationError("Course alias is required")
        if not language:
            raise ConfigurationError("Language is required")
        if not api_key:
            raise ConfigurationError("API key is required")
        if (submission_id_mode == "true" and
                (not submission_id or not student_name)):
            raise ConfigurationError(
                "Submission ID and student name are required in "
                "submission ID mode"
            )

        client = TeachingAssistantClient(username, password, base_url)
        
        if not client.login():
            raise APIError("Failed to login to omegaUp")
            
        client.initialize_llm(llm_provider, api_key)
        
        client.process_submissions(
            course_alias, assignment_alias, language,
            ta_feedback_indicator, args.skip_confirm,
            submission_id if submission_id_mode == "true" else None,
            student_name if submission_id_mode == "true" else None
        )
            
        LOG.info("Teaching Assistant completed successfully")
        
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
    except TeachingAssistantError as e:
        LOG.error("Teaching assistant error: %s", e)
        sys.exit(1)
    except Exception as e:
        LOG.error("Unexpected error: %s", e)
        sys.exit(1)


if __name__ == "__main__":
    main()
