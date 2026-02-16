#!/usr/bin/env python3
"""
Solution Handler for AC Solution Management

This module handles discovering existing AC solutions, generating
new solutions when needed, and verifying them through the grader.
"""

import logging
import time
from typing import Dict, Any, Optional, Tuple

import requests
from openai import OpenAI  # type: ignore

# Import with fallback for relative imports
try:
    from .ai_editorial_generator import (  # type: ignore
        EditorialGeneratorConfig
    )
except ImportError:
    from ai_editorial_generator import EditorialGeneratorConfig

logger = logging.getLogger(__name__)


class SolutionHandler:
    """Handles AC solution discovery, generation, and verification."""

    def __init__(
        self, config: EditorialGeneratorConfig, session: requests.Session
    ) -> None:
        """Initialize solution handler."""
        self.config = config
        self.session = session
        self.openai_client = OpenAI(api_key=config.openai_api_key)

    def get_first_ac_run(self, problem_alias: str) -> Optional[Dict[str, Any]]:
        """Get the oldest AC run for the problem."""
        try:
            logger.info("[%s] Searching for oldest AC solution...",
                        problem_alias)

            url = f"{self.config.api_url}/problem/runs"
            params = {
                'problem_alias': problem_alias,
                'show_all': 'true',
                'verdict': 'AC',
                'offset': 0,
                'rowcount': 100
            }

            response = self.session.get(
                url, params=params, timeout=(10, 30)  # type: ignore
            )

            if response.status_code != 200:
                logger.warning("[%s] HTTP %d when fetching AC runs",
                               problem_alias, response.status_code)
                return None

            result = response.json()
            if result.get("status") != "ok":
                logger.warning("[%s] Error fetching AC runs: %s",
                               problem_alias,
                               result.get('error', 'Unknown error'))
                return None

            runs = result.get("runs", [])
            if not runs:
                logger.info("[%s] No AC runs found", problem_alias)
                return None

            # Find the oldest AC run by looking at timestamps
            oldest_run = None
            oldest_time = None

            for run in runs:
                run_time = run.get('time')
                if run_time:
                    if oldest_time is None or run_time < oldest_time:
                        oldest_time = run_time
                        oldest_run = run

            if oldest_run:
                logger.info("[%s] Found oldest AC run by %s in %s at %s",
                            problem_alias,
                            oldest_run.get('username', 'unknown'),
                            oldest_run.get('language', 'unknown'),
                            oldest_run.get('time', 'unknown time'))
                return oldest_run  # type: ignore

            # Fallback to first run if no timestamp comparison worked
            first_ac = runs[0]
            logger.info("[%s] Using first AC run by %s in %s",
                        problem_alias, first_ac.get('username', 'unknown'),
                        first_ac.get('language', 'unknown'))
            return first_ac  # type: ignore

        except requests.RequestException as e:
            logger.warning("[%s] Request error fetching AC runs: %s",
                           problem_alias, str(e))
            return None

    def get_run_source(self, run_guid: str) -> Optional[str]:
        """Get source code of a specific run."""
        try:
            url = f"{self.config.api_url}/run/source"
            params = {'run_alias': run_guid}

            response = self.session.get(
                url, params=params, timeout=(10, 30)  # type: ignore
            )

            if response.status_code != 200:
                logger.warning("HTTP %d when fetching run source",
                               response.status_code)
                return None

            result = response.json()
            if result.get("status") != "ok":
                logger.warning("Error fetching run source: %s",
                               result.get('error', 'Unknown error'))
                return None

            source_code = result.get('source', '')
            if source_code:
                logger.info("Successfully retrieved source code (%d chars)",
                            len(source_code))
                return source_code  # type: ignore

            logger.warning("Source code is empty")
            return None

        except requests.RequestException as e:
            logger.warning("Request error fetching run source: %s", str(e))
            return None

    def generate_solution_code(
        self,
        problem_details: Dict[str, Any],
        language: str,
        retry_config: Optional[Dict[str, Any]] = None
    ) -> Optional[str]:
        """Generate solution code using OpenAI GPT-4.

        Args:
            problem_details: Problem details from API
            language: Programming language
            retry_config: Optional dict with 'previous_error', 'attempt',
                         'previous_code'
        """
        if retry_config and retry_config.get('attempt', 1) > 1:
            prompt = self._create_retry_prompt(
                problem_details, language, retry_config
            )
        else:
            prompt = self._create_initial_prompt(problem_details, language)

        try:
            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {
                        "role": "system",
                        "content": (
                            "You are an expert competitive programmer. "
                            "Generate only clean, working source code without "
                            "any explanations or markdown formatting."
                        )
                    },
                    {"role": "user", "content": prompt}
                ],
                max_tokens=2000,
                temperature=0.3
            )

            code = response.choices[0].message.content.strip()

            # Clean up code (remove markdown formatting if present)
            if code.startswith('```'):
                lines = code.split('\n')
                if len(lines) > 2:
                    code = '\n'.join(lines[1:-1])

            logger.info("Generated %s solution (%d chars)", language,
                        len(code))
            return code  # type: ignore

        except requests.RequestException as e:
            logger.error("Request error generating solution code: %s", str(e))
            return None
        except ValueError as e:
            logger.error("Failed to generate solution code: %s", str(e))
            return None

    def _create_initial_prompt(
        self, problem_details: Dict[str, Any], language: str
    ) -> str:
        """Create initial solution generation prompt."""
        problem_statement = problem_details.get(
            'statement', {}
        ).get('markdown', '')
        problem_title = problem_details.get('title', 'Unknown Problem')

        # Trim problem statement if too long
        max_statement_length = 5000
        if len(problem_statement) > max_statement_length:
            problem_statement = problem_statement[:max_statement_length]

        return (
            f"You are an expert competitive programming assistant. "
            f"Generate a solution for this omegaUp problem.\n\n"
            f"Problem Title: {problem_title}\n\n"
            f"Problem Statement:\n{problem_statement}\n\n"
            f"Requirements:\n"
            f"- Language: {language}\n"
            f"- Write efficient, correct code that will get AC\n"
            f"- Use appropriate algorithms and data structures\n"
            f"- Handle edge cases properly\n"
            f"- Follow competitive programming best practices\n"
            f"- Provide only the complete source code without explanations\n\n"
            f"Generate the complete {language} solution:"
        )

    def _create_retry_prompt(
        self,
        problem_details: Dict[str, Any],
        language: str,
        retry_config: Dict[str, Any]
    ) -> str:
        """Create retry prompt with error feedback."""
        problem_statement = problem_details.get(
            'statement', {}
        ).get('markdown', '')
        problem_title = problem_details.get('title', 'Unknown Problem')
        previous_error = retry_config.get('previous_error', '')
        previous_code = retry_config.get('previous_code', '')

        # Trim problem statement if too long
        max_statement_length = 5000
        if len(problem_statement) > max_statement_length:
            problem_statement = problem_statement[:max_statement_length]

        return f"""The previous solution failed.

Generate an improved solution.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

Previous Error Information:
{previous_error}

Previous Code:
```{language}
{previous_code}
```

Requirements:
- Language: {language}
- Fix the issues from the previous attempt
- Write efficient, correct code that will get AC
- Handle edge cases properly
- Provide only the complete source code without explanations

Generate the improved {language} solution:"""

    def submit_solution(
        self,
        problem_alias: str,
        language: str,
        source_code: str,
        wait_before_submit: bool = False
    ) -> Optional[Tuple[str, str]]:
        """Submit solution to omegaUp grader and return
        (run_guid, actual_language_used)."""
        try:
            if wait_before_submit:
                logger.info("Waiting 60 seconds before retry submission...")
                time.sleep(60)

            url = f"{self.config.api_url}/run/create"
            data = {
                'problem_alias': problem_alias,
                'language': language,
                'source': source_code
            }

            logger.info("Submitting solution to grader for verification...")
            response = self.session.post(url, data=data, timeout=(10, 30))

            if response.status_code != 200:
                logger.error("Submission failed with status %d",
                             response.status_code)
                return None

            result = response.json()
            if result.get("status") != "ok":
                error_msg = result.get('error', 'Unknown error')

                # Check for Karel-specific errors
                if 'karel' in error_msg.lower() or 'kj' in error_msg.lower():
                    logger.info("KAREL SKIP: Problem requires Karel language")
                    return ("KAREL_SKIP", "kj")

                logger.error("Submission error: %s", error_msg)
                return None

            run_guid = result.get('guid', '')
            if not run_guid:
                logger.error("No run GUID returned")
                return None

            logger.info("Submission successful. Run GUID: %s", run_guid)
            return (run_guid, language)

        except requests.RequestException as e:
            logger.error("Request failed to submit solution: %s", str(e))
            return None

    def check_run_status(
        self, run_guid: str, max_wait_time: int = 60
    ) -> Tuple[str, str, str]:
        """Check run status and return (verdict, score, feedback)."""
        try:
            url = f"{self.config.api_url}/run/status"
            start_time = time.time()
            last_status = ""

            while time.time() - start_time < max_wait_time:
                params = {'run_alias': run_guid}
                response = self.session.get(url, params=params,
                                            timeout=(10, 30))  # type: ignore

                if response.status_code != 200:
                    logger.error("Status check failed with status %d",
                                 response.status_code)
                    time.sleep(2)
                    continue

                result = response.json()

                # Check if this is a valid response for our run
                if 'guid' in result and result['guid'] == run_guid:
                    status = result.get('status', 'unknown')
                    verdict = result.get('verdict', 'unknown').upper()
                    score = result.get('score', 0)

                    # Log status changes
                    if status != last_status:
                        logger.info("Status: %s | Verdict: %s", status,
                                    verdict)
                        last_status = status

                    # Check if grading is complete
                    if status in ['ready', 'done']:
                        logger.info(
                            "Grading completed with verdict: %s, score: %s",
                            verdict, score
                        )

                        execution = result.get('execution', '')
                        output = result.get('output', '')
                        compile_error = result.get('compile_error', '')
                        feedback = execution or output or compile_error or ""

                        return verdict, str(score), feedback

                    # Check for errors
                    if status in ['error', 'compile_error']:
                        logger.error("Grading error - Status: %s, "
                                     "Verdict: %s", status, verdict)

                        execution = result.get('execution', '')
                        output = result.get('output', '')
                        compile_error = result.get('compile_error', '')
                        feedback = execution or output or compile_error or ""

                        return verdict, str(score), feedback
                else:
                    logger.warning(
                        "Status response doesn't match GUID: %s", result
                    )

                time.sleep(3)

            logger.warning("Run status check timed out")
            return "TIMEOUT", "0", "Status check timed out"

        except requests.RequestException as e:
            logger.error("Request failed to check run status: %s", str(e))
            return "ERROR", "0", str(e)

    def generate_and_verify_solution(
        self,
        problem_details: Dict[str, Any],
        problem_alias: str,
        language: str = "cpp17-gcc"
    ) -> Optional[str]:
        # pylint: disable=too-many-return-statements
        """Generate and verify AC solution for editorial use."""
        logger.info("[%s] Generating and verifying AC solution...",
                    problem_alias)

        # First attempt
        logger.info("=== GENERATING SOLUTION (ATTEMPT 1) ===")
        code1 = self.generate_solution_code(problem_details, language)
        if not code1:
            logger.error("Failed to generate solution code")
            return None

        # Submit first attempt
        submission_result1 = self.submit_solution(
            problem_alias, language, code1, wait_before_submit=False
        )
        if not submission_result1:
            logger.error("Failed to submit first solution")
            return None

        run_guid1, actual_language1 = submission_result1

        # Check if this is a Karel skip
        if run_guid1 == "KAREL_SKIP":
            logger.info("KAREL SKIP: Problem requires Karel language")
            return None

        # Check first attempt result
        verdict1, score1, feedback1 = self.check_run_status(run_guid1)
        if verdict1 == "ERROR":
            logger.error("Failed to check first run status")
            return None

        if verdict1 == "AC":
            logger.info("SUCCESS: AC on first try!")
            return code1

        logger.info("FAILED: First attempt failed: %s (score: %s)",
                    verdict1, score1)

        # Second attempt with error feedback
        logger.info("=== GENERATING SOLUTION (ATTEMPT 2) ===")
        error_info = f"Previous verdict: {verdict1}, Score: {score1}"
        if feedback1:
            error_info += f"\nFeedback: {feedback1}"

        retry_config = {
            'previous_error': error_info,
            'attempt': 2,
            'previous_code': code1
        }

        code2 = self.generate_solution_code(
            problem_details, actual_language1, retry_config
        )
        if not code2:
            logger.error("Failed to generate second solution")
            return None

        # Submit second attempt
        submission_result2 = self.submit_solution(
            problem_alias, actual_language1, code2,
            wait_before_submit=True
        )
        if not submission_result2:
            logger.error("Failed to submit second solution")
            return None

        run_guid2, _ = submission_result2

        # Check second attempt result
        verdict2, _, _ = self.check_run_status(run_guid2)
        if verdict2 == "ERROR":
            logger.error("Failed to check second run status")
            return None

        if verdict2 == "AC":
            logger.info("SUCCESS: AC on second try!")
            return code2

        logger.error("FAILED: Second try failed: %s", verdict2)
        logger.error("Cannot generate editorial - no AC solution")
        return None
