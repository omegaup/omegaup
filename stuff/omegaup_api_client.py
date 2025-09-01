"""
omegaUp API Client - General-purpose API client for omegaUp platform

This client uses auth_token from user sessions (passed as 'ouat' parameter)
instead of username/password authentication. This integrates seamlessly with
omegaUp's session management system.

Can be used by any omegaUp tool/script that needs API access.
"""

import json
import logging
import random
import time

from typing import Dict, Any, List, Optional, Tuple

import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry


class OmegaUpAPIClient:
    """
    General-purpose omegaUp API client with the following features:
    - Uses session auth_token (no username/password needed)
    - Redis session caching for efficiency
    - Automatic retry with exponential backoff
    - Rate limiting and error handling
    - Complete omegaUp API coverage for any tool
    - Exponential backoff for verdict polling
    """

    def __init__(self, auth_token: str, base_url: str = 'https://omegaup.com',
                 user_agent: str = 'omegaUp-API-Client/1.0'):
        """
        Initialize API client with user's auth token.

        Args:
            auth_token: User's session token from PHP
                        Session::getCurrentSession()
            base_url: omegaUp base URL (default: https://omegaup.com)
            user_agent: Custom user agent for requests
        """
        if not auth_token:
            raise ValueError("auth_token is required")

        self.auth_token = auth_token
        self.base_url = base_url.rstrip('/')
        self.api_url = f"{self.base_url}/api"

        # Configure session with retries
        self.session = requests.Session()
        retry_strategy = Retry(
            total=3,
            backoff_factor=1,
            status_forcelist=[429, 500, 502, 503, 504],
            allowed_methods=["HEAD", "GET", "POST"]
        )
        adapter = HTTPAdapter(max_retries=retry_strategy)
        self.session.mount("http://", adapter)
        self.session.mount("https://", adapter)

        # Set default headers
        self.session.headers.update({
            'User-Agent': user_agent,
            'Accept': 'application/json'
        })

        logging.info("Initialized omegaUp API client for %s", base_url)

    def _make_request(self,
                      endpoint: str,
                      data: Optional[Dict[str,
                                          Any]] = None,
                      timeout: int = 30) -> Dict[str,
                                                 Any]:
        """
        Make authenticated API request to omegaUp.

        Args:
            endpoint: API endpoint (e.g., '/problem/details/')
            data: Request payload data
            timeout: Request timeout in seconds

        Returns:
            API response as dictionary

        Raises:
            requests.RequestException: On network/HTTP errors
            ValueError: On invalid response format
        """
        url = f"{self.api_url}{endpoint}"

        # Prepare request data with auth token
        request_data = data.copy() if data else {}
        request_data['ouat'] = self.auth_token  # omegaUp auth token parameter

        try:
            logging.debug("API Request: POST %s", url)
            logging.debug("Request data keys: %s", list(request_data.keys()))

            response = self.session.post(
                url=url,
                data=request_data,
                timeout=timeout
            )

            # Handle HTTP errors
            if response.status_code == 401:
                raise requests.RequestException(
                    "Authentication failed - invalid auth_token")
            if response.status_code == 403:
                raise requests.RequestException(
                    "Access forbidden - insufficient permissions")
            if response.status_code == 429:
                raise requests.RequestException("Rate limit exceeded")
            if response.status_code >= 400:
                raise requests.RequestException(
                    f"HTTP {response.status_code}: {response.text}")

            # Parse JSON response
            try:
                response_data = response.json()
            except json.JSONDecodeError as e:
                raise ValueError(f"Invalid JSON response: {e}") from e

            # Check for API-level errors
            if response_data.get('status') == 'error':
                error_msg = response_data.get('error', 'Unknown API error')
                raise requests.RequestException(f"API Error: {error_msg}")

            logging.debug("API Response: %d OK", response.status_code)
            return response_data  # type: ignore

        except requests.Timeout:
            raise requests.RequestException(
                f"Request timeout after {timeout}s") from None
        except requests.ConnectionError:
            raise requests.RequestException(
                "Connection error - check network/URL") from None
        except Exception as e:
            logging.exception("API request failed: %s", e)
            raise

    def get_problem_details(self, problem_alias: str) -> Dict[str, Any]:
        """
        Get detailed problem information.

        Args:
            problem_alias: Problem identifier (e.g., 'hello-world')

        Returns:
            Problem details including statement, settings, etc.
        """
        logging.info("Fetching problem details for: %s", problem_alias)

        response = self._make_request('/problem/details/', {
            'problem_alias': problem_alias
        })

        # API returns problem data directly, not nested under 'problem'
        if not response or 'statement' not in response:
            raise ValueError(
                f"Problem '{problem_alias}' not found or inaccessible")

        problem_data = response

        logging.info(
            "Retrieved problem: %s", problem_data.get('title', 'Unknown'))
        return problem_data  # type: ignore

    def get_problem_runs(self, problem_alias: str, status: str = 'AC',
                         limit: int = 50) -> List[Dict[str, Any]]:
        """
        Get problem runs (submissions).

        Args:
            problem_alias: Problem identifier
            status: Run status filter ('AC', 'WA', etc.)
            limit: Maximum number of runs to retrieve

        Returns:
            List of run objects with submission details
        """
        logging.info("Fetching %s runs for problem: %s", status, problem_alias)

        response = self._make_request('/problem/runs/', {
            'problem_alias': problem_alias,
            'status': status,
            'rowcount': str(limit)
        })

        runs = response.get('runs', [])
        logging.info("Found %d %s runs", len(runs), status)
        return runs  # type: ignore

    def get_run_source(self, run_guid: str) -> Dict[str, Any]:
        """
        Get source code for a specific run.

        Args:
            run_guid: Unique run identifier

        Returns:
            Run details including source code
        """
        logging.debug("Fetching source for run: %s", run_guid)

        response = self._make_request('/run/source/', {
            'run_alias': run_guid
        })

        return response.get('source', {})

    def create_run(self, problem_alias: str, language: str,
                   source: str) -> Dict[str, Any]:
        """
        Submit a run (solution) to a problem.

        Args:
            problem_alias: Problem identifier
            language: Programming language (e.g., 'py3', 'cpp17-gcc')
            source: Source code to submit

        Returns:
            Run creation response with run GUID
        """
        logging.info("Submitting %s solution to: %s", language, problem_alias)

        response = self._make_request('/run/create/', {
            'problem_alias': problem_alias,
            'language': language,
            'source': source
        })

        run_guid = response.get('guid')
        if not run_guid:
            raise ValueError("Run creation failed - no GUID returned")

        logging.info("Created run: %s", run_guid)
        return response

    def get_run_status(self, run_guid: str) -> Dict[str, Any]:
        """
        Get current status of a run.

        Args:
            run_guid: Run identifier

        Returns:
            Run status information
        """
        logging.debug("Checking status for run: %s", run_guid)

        response = self._make_request('/run/status/', {
            'run_alias': run_guid
        })

        return response

    def update_problem_solution(
        self,
        problem_alias: str,
        solution: str,
        language: str,
        message: str = "Updated solution") -> bool:
        """
        Update problem's editorial/solution.

        Args:
            problem_alias: Problem identifier
            solution: Editorial content (Markdown)
            language: Language code ('en', 'es', 'pt', 'markdown')
            message: Commit message

        Returns:
            True if update successful, False otherwise
        """
        logging.info("Updating %s solution for: %s", language, problem_alias)

        try:
            response = self._make_request('/problem/updateSolution/', {
                'problem_alias': problem_alias,
                'solution': solution,
                'lang': language,
                'message': message
            })

            # Check for successful update
            status = response.get('status')
            if status == 'ok':
                logging.info("Successfully updated %s solution", language)
                return True

            logging.warning("Solution update failed: %s", response)
            return False

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error("Error updating solution: %s", e)
            return False

    def get_problem_solution(
        self,
        problem_alias: str,
        language: str = 'markdown') -> Optional[str]:
        """
        Get existing problem solution/editorial.

        Args:
            problem_alias: Problem identifier
            language: Language code ('en', 'es', 'pt', 'markdown')

        Returns:
            Existing solution content or None if not found
        """
        logging.debug("Fetching %s solution for: %s", language, problem_alias)

        try:
            response = self._make_request('/problem/solution/', {
                'problem_alias': problem_alias,
                'lang': language
            })

            solution_data = response.get('solution', {})
            return solution_data.get('markdown', '')  # type: ignore

        except (ConnectionError, TypeError, ValueError) as e:
            logging.debug("No existing solution found: %s", e)
            return None

    def verify_problem_access(self, problem_alias: str) -> bool:
        """
        Verify that the current user has access to the problem.

        Args:
            problem_alias: Problem identifier

        Returns:
            True if user has access, False otherwise
        """
        try:
            self.get_problem_details(problem_alias)
            return True
        except (ConnectionError, TypeError, ValueError) as e:
            logging.warning("Problem access verification failed: %s", e)
            return False

    def get_supported_languages(self, problem_alias: str) -> List[str]:
        """
        Get list of supported programming languages for a problem.

        Args:
            problem_alias: Problem identifier

        Returns:
            List of supported language codes
        """
        try:
            problem_data = self.get_problem_details(problem_alias)
            return problem_data.get('languages', [])
        except (ConnectionError, TypeError, ValueError) as e:
            logging.warning("Could not get supported languages: %s", e)
            return ['py3', 'cpp17-gcc', 'java']  # Fallback defaults

    def submit_solution(self, problem_alias: str, language: str,
                        source_code: str) -> Optional[Tuple[str, str]]:
        """
        Submit a solution and return run GUID and submission token.

        Args:
            problem_alias: Problem identifier
            language: Programming language
            source_code: Source code to submit

        Returns:
            Tuple of (run_guid, submission_token) or None if failed
        """
        try:
            result = self.create_run(problem_alias, language, source_code)
            run_guid = result.get('guid')
            submission_token = result.get('submission_token', '')

            if run_guid:
                return (run_guid, submission_token)
            return None

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error("Error submitting solution: %s", e)
            return None

    def check_admin_access(self, problem_alias: str) -> bool:
        """
        Check if current user has admin access to the problem.

        Args:
            problem_alias: Problem identifier

        Returns:
            True if user has admin access, False otherwise
        """
        try:
            # Try to get problem details which requires admin access for
            # private problems
            _ = self.get_problem_details(problem_alias)

            # Additional check: try to get admin-only information
            response = self._make_request('/problem/admins/', {
                'problem_alias': problem_alias
            })

            # If we can get admin list, we have admin access
            return response.get('status') == 'ok' or 'admins' in response

        except (ConnectionError, TypeError, ValueError) as e:
            logging.debug("Admin access check failed: %s", e)
            return False

    def get_run_status_detailed(self, run_guid: str) -> Tuple[str, float, str]:
        """
        Get detailed run status information.

        Args:
            run_guid: Run identifier

        Returns:
            Tuple of (verdict, score, memory)
        """
        try:
            response = self.get_run_status(run_guid)

            verdict = 'JE'
            score = 0.0
            memory = '0'

            # Extract from direct fields or nested details
            if 'verdict' in response and response['verdict']:
                verdict = str(response['verdict'])
                score = float(response.get('score', 0.0))
                memory = str(response.get('memory', '0'))
            elif ('details' in response and
                  isinstance(response['details'], dict)):
                details = response['details']
                verdict = str(details.get('verdict', 'JE'))
                score = float(details.get('score', 0.0))
                memory = str(details.get('memory', '0'))

            return (verdict, score, memory)

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error("Error getting detailed run status: %s", e)
            return ('JE', 0.0, '0')

    def wait_for_verdict(self, run_guid: str, max_attempts: int = 20,
                         initial_delay: float = 1.0) -> Dict[str, Any]:
        """
        Wait for run verdict with exponential backoff polling strategy.

        Args:
            run_guid: Run identifier to poll
            max_attempts: Maximum number of polling attempts
            initial_delay: Initial delay between attempts
                          (exponentially increased)

        Returns:
            Dict with verdict information: {
                'verdict': str,
                'success': bool,
                'score': float,
                'memory': str,
                'runtime': str
            }
        """

        logging.info("Waiting for verdict for run: %s", run_guid)

        delay_seconds = initial_delay

        for attempt in range(1, max_attempts + 1):
            try:
                time.sleep(delay_seconds)

                response = self.get_run_status(run_guid)

                # Extract verdict and status from response
                # Handle both /run/status/ and /run/details/ response formats
                verdict = ''
                status = ''
                score = 0.0
                memory = '0'
                runtime = '0'

                # Check for direct fields first
                if 'verdict' in response and response['verdict']:
                    verdict = str(response['verdict']).upper()
                    score = float(response.get('score', 0.0))
                    memory = str(response.get('memory', '0'))
                    runtime = str(response.get('time', '0'))

                # Fall back to nested details field if present
                elif ('details' in response and
                      isinstance(response['details'], dict)):
                    details = response['details']
                    verdict = str(details.get('verdict', '')).upper()
                    score = float(details.get('score', 0.0))
                    memory = str(details.get('memory', '0'))
                    runtime = str(details.get('time', '0'))

                if 'status' in response:
                    status = str(response.get('status', '')).lower()
                elif ('details' in response and
                      isinstance(response['details'], dict)):
                    status = str(response['details'].get('status', '')).lower()

                logging.info(
                    "Attempt %d: Run %s status='%s' verdict='%s' "
                    "response_keys=%s",
                    attempt,
                    run_guid,
                    status,
                    verdict,
                    list(
                        response.keys()))

                if attempt <= 3:
                    logging.debug("Full response structure: %s", response)

                # Check if run is finished
                if verdict and verdict.strip() and verdict not in ['', 'JE']:
                    result = {
                        'verdict': verdict,
                        'success': verdict == 'AC',
                        'score': score,
                        'memory': memory,
                        'runtime': runtime
                    }
                    logging.info(
                        "Final verdict for run %s: %s (attempt %d)",
                        run_guid, verdict, attempt)
                    return result

                # Exponential backoff with jitter
                if attempt < max_attempts:
                    base_delay = initial_delay
                    max_delay = 16.0
                    jitter_factor = 0.1

                    # Exponential: 2^(attempt//3) for grouping
                    exponential_delay = min(
                        base_delay * (2 ** (attempt // 3)), max_delay)

                    # Add jitter to avoid thundering herd
                    jitter = (exponential_delay * jitter_factor *
                              (2 * random.random() - 1))
                    delay_seconds = max(0.5, exponential_delay + jitter)

            except (ConnectionError, TypeError, ValueError) as e:
                logging.warning(
                    "Error polling run %s (attempt %d): %s",
                    run_guid, attempt, e)
                continue

        # Timeout reached
        logging.warning(
            "Verdict polling timeout for run %s after %d attempts",
            run_guid, max_attempts)
        return {
            'verdict': 'TIMEOUT',
            'success': False,
            'score': 0.0,
            'memory': '0',
            'runtime': '0'
        }

    def update_job_status(
        self,
        job_id: str,
        status: str,
        **kwargs: Any
    ) -> bool:
        """
        Update AI editorial job status and content in database.

        This method is called by the worker to synchronize job status
        and generated content from Redis to the omegaUp database.

        Args:
            job_id: Unique job identifier (UUID)
            status: Job status ('processing', 'completed', 'failed')
            **kwargs: Optional parameters:
                - editorials: Dict with generated editorials
                - error_message: Error description if status is 'failed'
                - validation_verdict: Solution validation result

        Returns:
            True if update successful, False otherwise
        """
        logging.info("Updating job status: %s -> %s", job_id, status)

        try:
            # Extract optional parameters from kwargs
            editorials = kwargs.get('editorials')
            error_message = kwargs.get('error_message')
            validation_verdict = kwargs.get('validation_verdict')

            request_data = {
                'job_id': job_id,
                'status': status
            }

            # Add optional parameters if provided
            if error_message:
                request_data['error_message'] = error_message

            if editorials:
                if 'en' in editorials and editorials['en']:
                    request_data['md_en'] = editorials['en']
                if 'es' in editorials and editorials['es']:
                    request_data['md_es'] = editorials['es']
                if 'pt' in editorials and editorials['pt']:
                    request_data['md_pt'] = editorials['pt']

            if validation_verdict:
                request_data['validation_verdict'] = validation_verdict

            response = self._make_request(
                '/aiEditorial/updateJob/', request_data)

            # Check for successful update
            if response.get('status') == 'ok':
                logging.info(
                    "Successfully updated job %s status to %s",
                    job_id,
                    status)
                return True

            logging.warning("Job status update failed: %s", response)
            return False

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error("Error updating job status for %s: %s", job_id, e)
            return False
