"""Solution handler for AC solution detection and verification."""

import logging
import time
from typing import Any, Dict, Optional, Tuple

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
from omegaup_api_client import OmegaUpAPIClient  # type: ignore


class KarelProblemException(Exception):
    """Exception raised when Karel problems are detected."""

    def __init__(self, problem_alias: str):
        """Initialize Karel problem exception."""
        self.problem_alias = problem_alias
        super().__init__(f"Karel problem detected: {problem_alias}")


class SolutionHandler:
    """Handles solution detection, submission, and verification."""

    def __init__(self, config_manager: Any,
                 api_client: OmegaUpAPIClient) -> None:
        """Initialize solution handler."""
        self.config_manager = config_manager
        self.api_client = api_client

    def get_first_ac_run(self, problem_alias: str) -> Optional[Dict[str, Any]]:
        """Get first AC run for a problem to use as reference."""
        try:
            runs = self.api_client.get_problem_runs(problem_alias)
            if runs and isinstance(runs, list):
                for run in runs:
                    if run.get('verdict') == 'AC':
                        return run  # type: ignore
            return None
        except (ConnectionError, TypeError, ValueError) as e:
            logging.warning(
                "Failed to get AC runs for %s: %s", problem_alias, e)
            return None

    def get_run_source(self, run_guid: str) -> Optional[str]:
        """Get source code for a specific run."""
        try:
            source_code = self.api_client.get_run_source(run_guid)
            if source_code:
                return source_code  # type: ignore
            return None
        except (ConnectionError, TypeError, ValueError) as e:
            logging.warning("Failed to get source for run %s: %s", run_guid, e)
            return None

    def submit_solution(self,
                        problem_alias: str,
                        source_code: str,
                        language: str = 'py3') -> Optional[str]:
        """Submit solution and return run GUID."""
        try:
            # Check for Karel problems (special handling)
            if 'karel' in problem_alias.lower():
                logging.info("Karel problem detected, skipping submission")
                raise KarelProblemException(problem_alias)

            # Submit normal solution
            logging.info(
                "Submitting %s solution for %s", language, problem_alias)
            result = self.api_client.create_run(
                problem_alias, language, source_code)
            run_guid = result.get('guid') if result else None

            if run_guid:
                logging.info("Submission successful: %s", run_guid)
                return run_guid  # type: ignore

            logging.error("Submission failed - no run GUID returned")
            return None

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error("Submission error for %s: %s", problem_alias, e)
            return None

    def check_run_status(self, run_guid: str,
                         max_wait_time: int = 120) -> Tuple[str, float, str]:
        """Check run status with polling until completion."""
        try:
            start_time = time.time()

            while time.time() - start_time < max_wait_time:
                verdict, score, memory = (
                    self.api_client.get_run_status_detailed(run_guid))

                # Check if run is complete (not still judging)
                if verdict != 'JE':
                    logging.info(
                        "Run %s completed with verdict: %s", run_guid, verdict)
                    return verdict, score, memory

                # Wait before next check
                logging.info("Run %s still running, waiting...", run_guid)
                time.sleep(5)

            # Timeout
            logging.warning("Timeout waiting for run %s", run_guid)
            return "TO", 0.0, "0KB"

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error("Error checking run status %s: %s", run_guid, e)
            return "ERROR", 0.0, "0KB"

    def verify_solution_with_retry(self,
                                   problem_alias: str,
                                   source_code: str,
                                   language: str = 'py3',
                                   max_attempts: int = 3) -> Tuple[bool, str]:
        """Verify solution with retry logic."""
        logging.info(
            "Starting verification for %s (max %d attempts)",
            problem_alias,
            max_attempts)

        for attempt in range(1, max_attempts + 1):
            logging.info(
                "Verification attempt %d/%d for %s",
                attempt,
                max_attempts,
                problem_alias)

            try:
                # Submit solution
                run_guid = self.submit_solution(
                    problem_alias, source_code, language)

                if not run_guid:
                    if attempt < max_attempts:
                        logging.warning(
                            "Submission failed on attempt %d, retrying...",
                            attempt)
                        time.sleep(30)
                        continue
                    return False, (f"Submission failed after "
                                   f"{max_attempts} attempts")

                # Check run status
                verdict, score, _ = self.check_run_status(run_guid)

                if verdict == "AC":
                    logging.info(
                        "SUCCESS: Solution verified on attempt %d", attempt)
                    return True, f"AC on attempt {attempt}"

                logging.warning("FAILED: Attempt %d got %s (score: %.2f)",
                                attempt, verdict, score)

                # Wait before retry (omegaUp rate limiting)
                if attempt < max_attempts:
                    logging.info("Waiting 60 seconds before retry...")
                    time.sleep(60)

            except KarelProblemException as e:
                logging.info("Karel problem detected: %s", e.problem_alias)
                return True, "Karel problem - verification skipped"
            except (ConnectionError, TypeError, ValueError) as e:
                logging.error(
                    "Error in verification attempt %d: %s", attempt, e)
                if attempt < max_attempts:
                    logging.info("Waiting 30 seconds before retry...")
                    time.sleep(30)

        return False, f"Verification failed after {max_attempts} attempts"

    def find_working_solution(self, problem_alias: str) -> Optional[str]:
        """Find a working AC solution for the problem."""
        try:
            # Try to get first AC run
            first_ac = self.get_first_ac_run(problem_alias)
            if first_ac and first_ac.get('guid'):
                source_code = self.get_run_source(first_ac['guid'])
                if source_code:
                    logging.info(
                        "Found AC solution for %s (run: %s)",
                        problem_alias,
                        first_ac['guid'])
                    return source_code

            logging.warning("No AC solution found for %s", problem_alias)
            return None

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error(
                "Error finding solution for %s: %s", problem_alias, e)
            return None

    def get_solution_stats(self) -> Dict[str, Any]:
        """Get statistics about solution handling."""
        return {
            'total_verifications': getattr(
                self, '_total_verifications', 0),
            'successful_verifications': getattr(
                self, '_successful_verifications', 0),
            'failed_verifications': getattr(
                self, '_failed_verifications', 0)
        }
