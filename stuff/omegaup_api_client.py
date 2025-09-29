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

    def __init__(
        self,
        auth_token: Optional[str] = None,
        credentials: Optional[Tuple[str, str]] = None,
        base_url: str = 'https://omegaup.com',
        user_agent: str = 'omegaUp-API-Client/1.0'):
        """
        Initialize API client with user's auth token or username/password.

        Args:
            auth_token: User's session token from PHP (preferred)
            credentials: Tuple of (username, password) if auth_token not used
            base_url: omegaUp base URL (default: https://omegaup.com)
            user_agent: Custom user agent for requests
        """
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

        # Determine authentication method
        if auth_token:
            self.auth_token = auth_token
            logging.info(
                "Initialized omegaUp API client for %s (auth_token)",
                base_url)
        elif credentials:
            username, password = credentials
            self.auth_token = self._login_and_get_token(username, password)
            logging.info(
                "Initialized omegaUp API client for %s (username/password)",
                base_url)
        else:
            raise ValueError("auth_token or credentials required")

    def _login_and_get_token(self, username: str, password: str) -> str:
        """
        Log in to omegaUp and retrieve the session auth token (ouat).
        """
        login_url = f"{self.api_url}/user/login"
        login_data = {
            'usernameOrEmail': username,
            'password': password
        }
        headers = {'Content-Type': 'application/x-www-form-urlencoded'}
        try:
            resp = self.session.post(
                login_url,
                data=login_data,
                headers=headers,
                timeout=30)
            if resp.status_code != 200:
                raise ValueError(f"Login failed: HTTP {resp.status_code}")
            result = resp.json()
            if result.get('status') != 'ok':
                raise ValueError(
                    f"Login failed: {result.get('error', 'Unknown error')}")

            # Check if auth_token is in response
            if 'auth_token' in result:
                return str(result['auth_token'])

            # Otherwise, look for ouat cookie
            for cookie in self.session.cookies:
                if cookie.name == 'ouat' and cookie.value:
                    logging.info("Found ouat cookie: %s",
                                 cookie.value[:20] + "...")
                    return cookie.value

            # If no ouat cookie, try to get from session currentSession API
            current_session_url = f"{self.api_url}/session/currentSession"
            session_resp = self.session.get(current_session_url, timeout=30)
            if session_resp.status_code == 200:
                session_data = session_resp.json()
                if ('session' in session_data and
                        'ouat' in session_data['session']):
                    return str(session_data['session']['ouat'])

            raise ValueError(
                "Could not obtain auth token from login response or cookies")
        except Exception as e:
            logging.error("Failed to login and get auth token: %s", e)
            raise

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

            solution_data = response.get('solution', {}) or {}
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

    def get_user_problems(self,
                          page: int = 1,
                          rowcount: int = 100,
                          query: str = '') -> Dict[str, Any]:
        """
        Get problems created by the authenticated user.

        Args:
            page: Page number (1-based indexing)
            rowcount: Maximum number of problems to return
            query: Optional search query to filter problems

        Returns:
            API response containing problem list and metadata

        Raises:
            requests.RequestException: On API/network errors
        """
        logging.debug(
            "Fetching user problems: page=%d, rowcount=%d, query='%s'",
            page, rowcount, query)

        request_data = {
            'page': str(page),
            'rowcount': str(rowcount)
        }

        if query:
            request_data['query'] = query

        response = self._make_request('/problem/myList/', request_data)

        logging.debug(
            "User problems API response keys: %s",
            list(response.keys()))

        return response

    def get_admin_problems(self,
                           page: int = 1,
                           page_size: int = 100,
                           query: str = '') -> Dict[str, Any]:
        """
        Get problems accessible to admin (includes private problems).

        Args:
            page: Page number (1-based indexing)
            page_size: Maximum number of problems to return
            query: Optional search query to filter problems

        Returns:
            API response containing problem list and metadata

        Raises:
            requests.RequestException: On API/network errors
        """
        logging.debug(
            "Fetching admin problems: page=%d, page_size=%d, query='%s'",
            page, page_size, query)

        request_data = {
            'page': str(page),
            'page_size': str(page_size)
        }

        if query:
            request_data['query'] = query

        response = self._make_request('/problem/adminList/', request_data)

        logging.debug(
            "Admin problems API response keys: %s",
            list(response.keys()))

        return response

    def get_public_problems(self,
                            page: int = 1,
                            rowcount: int = 100,
                            query: str = '') -> Dict[str, Any]:
        """
        Get public problems (excludes private problems).

        Args:
            page: Page number (1-based indexing)
            rowcount: Maximum number of problems to return
            query: Optional search query to filter problems

        Returns:
            API response containing problem list and metadata

        Raises:
            requests.RequestException: On API/network errors
        """
        logging.debug(
            "Fetching public problems: page=%d, rowcount=%d, query='%s'",
            page, rowcount, query)

        request_data = {
            'page': str(page),
            'rowcount': str(rowcount),
            'min_visibility': '1'  # 1 = public, 0 = private
        }

        if query:
            request_data['query'] = query

        response = self._make_request('/problem/list/', request_data)

        logging.debug(
            "Public problems API response keys: %s",
            list(response.keys()))

        return response

    def get_all_public_problems(self,
                                query: str = '',
                                batch_size: int = 100) -> List[Dict[str, Any]]:
        """
        Get all public problems using pagination.

        Args:
            query: Optional search query to filter problems
            batch_size: Number of problems to fetch per API call

        Returns:
            List of all public problem objects

        Raises:
            requests.RequestException: On API/network errors
        """
        logging.info("Fetching all public problems with query: '%s'", query)

        all_problems = []
        page = 1
        total_processed = 0

        while True:
            logging.debug(
                "Fetching page %d (rowcount=%d)",
                page, batch_size)

            try:
                response = self.get_public_problems(
                    page=page,
                    rowcount=batch_size,
                    query=query
                )

                # Extract problems list from response
                problems = response.get('results', [])
                if not problems:
                    logging.debug(
                        "No more problems found, stopping pagination"
                    )
                    break

                all_problems.extend(problems)
                batch_count = len(problems)
                total_processed += batch_count

                logging.debug(
                    "Page %d: Found %d problems (total: %d)",
                    page, batch_count, total_processed)

                # Check if we've reached the end
                if batch_count < batch_size:
                    logging.debug(
                        "Received fewer problems than requested, "
                        "assuming end of results")
                    break

                page += 1

                # Safety check to prevent infinite loops
                if total_processed > 10000:
                    logging.warning(
                        "Processed over 10,000 problems, stopping pagination")
                    break

            except requests.RequestException as e:
                logging.error(
                    "Error fetching public problems at page %d: %s",
                    page, e)
                raise

        logging.info(
            "Successfully fetched %d total public problems", total_processed)
        return all_problems

    def extract_problem_statistics(self,
                                   problems: List[Dict[str,
                                                       Any]]) -> Dict[str,
                                                                      Any]:
        """
        Extract statistical information from problem list.

        Args:
            problems: List of problem objects from API

        Returns:
            Dictionary with statistical breakdown
        """
        stats: Dict[str, Any] = {
            'total_problems': len(problems),
            'public_problems': 0,
            'private_problems': 0,
            'visibility_breakdown': {},
            'difficulty_breakdown': {},
            'problems_with_solutions': 0,
            'problems_without_solutions': 0,
            'sample_problems': problems[:5] if problems else []
        }

        # Analyze each problem
        for problem in problems:
            # Visibility analysis
            visibility = problem.get('visibility', 'unknown')
            if visibility in ['public', 'public_banned']:
                stats['public_problems'] = int(stats['public_problems']) + 1
            elif visibility in ['private', 'promoted']:
                stats['private_problems'] = int(stats['private_problems']) + 1

            # Count visibility types
            visibility_breakdown = stats['visibility_breakdown']
            if visibility in visibility_breakdown:
                visibility_breakdown[visibility] += 1
            else:
                visibility_breakdown[visibility] = 1

            # Difficulty analysis
            difficulty = problem.get('difficulty', 'unknown')
            difficulty_breakdown = stats['difficulty_breakdown']
            if difficulty in difficulty_breakdown:
                difficulty_breakdown[difficulty] += 1
            else:
                difficulty_breakdown[difficulty] = 1

            # Solution analysis (placeholder - would need additional API call)
            # This is a simplified check based on available data
            if 'solution' in problem and problem['solution']:
                stats['problems_with_solutions'] = int(
                    stats['problems_with_solutions']) + 1
            else:
                stats['problems_without_solutions'] = int(
                    stats['problems_without_solutions']) + 1

        return stats
