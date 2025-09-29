#!/usr/bin/env python3
# pylint: disable=too-many-lines
# This module is intentionally comprehensive for batch processing
"""
batch_generate_editorials.py - Batch editorial generation using AI Editorial
API

This script processes a list of problems through the new asynchronous AI
Editorial API. It manages authentication, status polling, and comprehensive
logging for large-scale editorial generation tasks with validation verdict
tracking.

INTEGRATION WORKFLOW:
    1. get_user_problems.py writes problems → {PROBLEMS_LIST_FILE}
    2. This script reads from same file → batch generation
    3. Both scripts use the same environment variable: PROBLEMS_LIST_FILE

Features:
- Dual authentication: OUAT token or username/password
- Integrated with get_user_problems.py via shared environment file
- Automatic problem file discovery and creation
- Asynchronous API integration with status polling
- Validation verdict tracking and analysis (AC, WA, TLE, etc.)
- API-side rate limit detection and error handling
- Comprehensive logging with performance metrics
- Enhanced reporting with multiple output files
- Resume functionality for interrupted sessions
- Error categorization and retry logic

Usage:
    cd stuff/editorial_generator/batch_scripts/

    # Integrated workflow (recommended):
    python get_user_problems.py          # Creates problems list file
    python batch_generate_editorials.py  # Processes the same file

    # Alternative usage:
    python batch_generate_editorials.py --problems-file custom_problems.txt
    python batch_generate_editorials.py --resume
    python batch_generate_editorials.py --test-mode --problem-alias example

Prerequisites:
    - .env file with authentication (OMEGAUP_OUAT_TOKEN or
      OMEGAUP_USERNAME/OMEGAUP_PASSWORD)
    - PROBLEMS_LIST_FILE environment variable (optional, defaults to
      my_problems.txt)
    - Active internet connection to omegaUp

Outputs:
    - logs/batch_editorials_YYYYMMDD_HHMMSS.log: Single comprehensive log file
    - results/batch_results_YYYYMMDD_HHMMSS.txt: Human-readable summary report
    - session_state.json: Resume state for interrupted sessions (optional)
"""

import sys
import os
import json
import logging
import time
import argparse
from datetime import datetime, timedelta
from pathlib import Path
from typing import List, Dict, Any, Optional, Tuple
from dataclasses import dataclass, asdict
from enum import Enum
import requests
try:
    from dotenv import load_dotenv  # type: ignore
except ImportError:
    def load_dotenv() -> None:  # type: ignore
        """Fallback if python-dotenv is not installed."""
        return

# Add parent directory to path for .env file
sys.path.insert(0, str(Path(__file__).parent.parent))


class JobStatus(Enum):
    """Possible job statuses from the AI Editorial API."""
    PENDING = "pending"
    RUNNING = "running"
    COMPLETED = "completed"
    FAILED = "failed"
    UNKNOWN = "unknown"


class ErrorCategory(Enum):
    """Error categorization for analysis."""
    AUTHENTICATION = "authentication"
    RATE_LIMIT = "rate_limit"
    API_ERROR = "api_error"
    NETWORK = "network"
    INVALID_PROBLEM = "invalid_problem"
    TIMEOUT = "timeout"
    UNKNOWN = "unknown"


@dataclass
class EditorialJob:  # pylint: disable=too-many-instance-attributes
    """Represents a single editorial generation job."""
    problem_alias: str
    job_id: Optional[str] = None
    status: JobStatus = JobStatus.UNKNOWN
    submitted_at: Optional[datetime] = None
    completed_at: Optional[datetime] = None
    error_message: Optional[str] = None
    error_category: Optional[ErrorCategory] = None
    retry_count: int = 0
    editorial_content: Optional[str] = None
    # New field for validation results
    validation_verdict: Optional[str] = None


@dataclass
class SessionStats:  # pylint: disable=too-many-instance-attributes
    """Statistics for the current session."""
    total_problems: int = 0
    jobs_submitted: int = 0
    jobs_completed: int = 0
    jobs_failed: int = 0
    jobs_pending: int = 0
    api_calls_made: int = 0
    api_errors: int = 0
    rate_limit_hits: int = 0
    start_time: Optional[datetime] = None
    end_time: Optional[datetime] = None


def setup_logging() -> logging.Logger:
    """Setup single consolidated logging."""
    # Create both logs and results directories
    log_dir = Path(__file__).parent / "logs"
    results_dir = Path(__file__).parent / "results"
    log_dir.mkdir(exist_ok=True)
    results_dir.mkdir(exist_ok=True)

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")

    # Single main log file
    main_log_file = log_dir / ("batch_editorials_%s.log" % timestamp)

    # Main logger configuration
    main_logger = logging.getLogger("batch_editorials")
    main_logger.setLevel(logging.INFO)

    # Formatter
    detailed_formatter = logging.Formatter(
        '%(asctime)s - %(levelname)s - %(message)s'
    )

    # File handler
    main_handler = logging.FileHandler(main_log_file, encoding='utf-8')
    main_handler.setFormatter(detailed_formatter)

    # Console handler for main logger
    console_handler = logging.StreamHandler()
    console_handler.setFormatter(detailed_formatter)

    # Add handlers
    main_logger.addHandler(main_handler)
    main_logger.addHandler(console_handler)

    # Initial log messages
    main_logger.info("=" * 80)
    main_logger.info("🚀 BATCH EDITORIAL GENERATION STARTED")
    main_logger.info("=" * 80)
    main_logger.info("📝 Log file: %s", main_log_file)
    main_logger.info("📁 Results dir: %s", results_dir)

    return main_logger


class BatchEditorialGenerator:  # pylint: disable=too-many-instance-attributes
    """Main class for batch editorial generation using the AI Editorial API."""

    def __init__(self, test_mode: bool = False, resume: bool = False):
        """Initialize the batch generator."""
        # Load environment variables from parent directory
        env_path = Path(__file__).parent.parent / ".env"
        load_dotenv(env_path)

        # Configuration
        self.test_mode = test_mode
        self.resume_mode = resume

        # Problems file configuration
        self.problems_filename = os.getenv(
            "PROBLEMS_LIST_FILE", "my_problems.txt")
        self.problems_file_path = Path(
            __file__).parent / self.problems_filename

        # API Configuration
        self.api_url = os.getenv("OMEGAUP_API_URL", "https://omegaup.com/api")
        self.base_url = os.getenv("OMEGAUP_BASE_URL", "https://omegaup.com")

        # Authentication - support both username/password and ouat token
        self.username = os.getenv("OMEGAUP_USERNAME")
        self.password = os.getenv("OMEGAUP_PASSWORD")
        self.ouat_token = os.getenv("OMEGAUP_OUAT_TOKEN")

        # Check authentication method
        if self.ouat_token:
            self.auth_method = "ouat"
        elif self.username and self.password:
            self.auth_method = "credentials"
        else:
            raise ValueError(
                "Either OMEGAUP_OUAT_TOKEN or both OMEGAUP_USERNAME and "
                "OMEGAUP_PASSWORD must be set in .env file")

        # API polling configuration
        self.status_poll_interval = 30  # seconds
        self.max_poll_duration = 3600   # 1 hour max per job

        # Session management
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'omegaUp-BatchEditorial-Generator/2.0',
            'Accept': 'application/json',
            'Accept-Language': 'en-US,en;q=0.9,es;q=0.8,pt;q=0.7'
        })

        # Job tracking
        self.jobs: Dict[str, EditorialJob] = {}

        # Statistics
        self.stats = SessionStats(start_time=datetime.now())

        # State file for resume functionality
        self.state_file = Path(__file__).parent / "session_state.json"

        # Results directory with timestamp
        self.results_dir = Path(__file__).parent / "results"
        self.results_dir.mkdir(exist_ok=True)
        self.run_timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")

        # Setup logging
        self.main_logger = setup_logging()

        # Login for authenticated access
        self._authenticate()

        # Load previous session if resuming
        if self.resume_mode:
            self._load_session_state()

    def _authenticate(self) -> None:
        """Authenticate using either ouat token or username/password."""
        try:
            if self.auth_method == "ouat":
                self.main_logger.info("🔐 Authenticating with ouat token...")

                # Set ouat cookie for session
                self.session.cookies.set(  # type: ignore
                    'ouat', self.ouat_token, domain='.omegaup.com')

                # Test authentication by making a simple API call
                test_result = self._make_api_call("user/profile")
                if test_result.get("status") == "ok":
                    username = test_result.get("username", "unknown")
                    self.username = username  # Set username for logging
                    self.main_logger.info(
                        "✅ Successfully authenticated with ouat token")
                    self.main_logger.info("   User: %s", username)
                    self.main_logger.info("   API URL: %s", self.api_url)
                else:
                    error_msg = test_result.get('error', 'Unknown error')
                    raise Exception(
                        "ouat token authentication failed: %s" % error_msg)

            else:  # credentials method
                self.main_logger.info(
                    "🔐 Authenticating as %s...", self.username)

                # Login endpoint
                login_url = "%s/user/login" % self.api_url
                login_data = {
                    'usernameOrEmail': self.username,
                    'password': self.password
                }

                # Headers for form data
                headers = {'Content-Type': 'application/x-www-form-urlencoded'}

                # Perform login
                response = self.session.post(
                    login_url,
                    data=login_data,
                    headers=headers,
                    timeout=(10, 30)
                )

                self.stats.api_calls_made += 1

                if response.status_code == 200:
                    result = response.json()
                    if result.get("status") == "ok":
                        self.main_logger.info(
                            "✅ Successfully authenticated with "
                            "username/password")
                        self.main_logger.info("   User: %s", self.username)
                        self.main_logger.info("   API URL: %s", self.api_url)
                    else:
                        raise Exception(
                            "Login failed: %s" %
                            result.get('error', 'Unknown error'))
                else:
                    raise Exception(
                        "HTTP %d: Login request failed" % response.status_code)

        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed to catch any authentication failures
            self.main_logger.error("❌ Authentication failed: %s", str(e))
            self._log_error(None, str(e), ErrorCategory.AUTHENTICATION)
            raise

    def _make_api_call(self,
                       endpoint: str,
                       params: Optional[Dict[str, Any]] = None,
                       method: str = 'POST') -> Dict[str, Any]:
        """Make an API call with comprehensive error handling and logging."""
        try:
            url = "%s/%s" % (self.api_url, endpoint)
            self.stats.api_calls_made += 1

            self.main_logger.debug("🌐 API Call: %s %s", method, endpoint)
            if params:
                self.main_logger.debug(
                    "   Parameters: %s", json.dumps(params, indent=2))

            start_time = time.time()

            if method == 'GET':
                response = self.session.get(
                    url, params=params, timeout=(10, 60))
            else:
                headers = {'Content-Type': 'application/x-www-form-urlencoded'}
                response = self.session.post(
                    url, data=params, headers=headers, timeout=(10, 60))

            duration = time.time() - start_time
            self.main_logger.debug("   Response time: %.2fs", duration)

            if response.status_code == 429:
                self.main_logger.warning("⚠️  Rate limit hit for %s", endpoint)
                self.stats.rate_limit_hits += 1
                self._log_error(
                    None,
                    "Rate limit hit: %s" % endpoint,
                    ErrorCategory.RATE_LIMIT)
                return {
                    'status': 'error',
                    'error': 'rate_limit',
                    'retry_after': 300}

            if response.status_code != 200:
                self.main_logger.warning(
                    "⚠️  HTTP %s for %s", response.status_code, endpoint)
                self.stats.api_errors += 1
                self._log_error(
                    None,
                    "HTTP %d: %s" % (response.status_code, endpoint),
                    ErrorCategory.API_ERROR)
                return {
                    'status': 'error',
                    'error': 'HTTP %d' % response.status_code}

            result = response.json()

            if result.get("status") != "ok":
                error_msg = result.get('error', 'Unknown API error')
                self.main_logger.warning(
                    "⚠️  API error for %s: %s", endpoint, error_msg)
                self.stats.api_errors += 1
                self._log_error(
                    None,
                    "API error %s: %s" % (endpoint, error_msg),
                    ErrorCategory.API_ERROR)

            return dict(result)  # Explicit conversion to satisfy mypy

        except requests.exceptions.Timeout:
            self.main_logger.error("⏰ Timeout for %s", endpoint)
            self.stats.api_errors += 1
            self._log_error(
                None,
                "Timeout: %s" % endpoint,
                ErrorCategory.TIMEOUT)
            return {'status': 'error', 'error': 'timeout'}
        except requests.exceptions.ConnectionError:
            self.main_logger.error("🌐 Connection error for %s", endpoint)
            self.stats.api_errors += 1
            self._log_error(
                None,
                "Connection error: %s" % endpoint,
                ErrorCategory.NETWORK)
            return {'status': 'error', 'error': 'connection_error'}
        except Exception as e:  # pylint: disable=broad-except
            # Catch-all for any unexpected API errors to ensure graceful
            # handling
            self.main_logger.error(
                "❌ Unexpected API error for %s: %s", endpoint, str(e))
            self.stats.api_errors += 1
            self._log_error(
                None, "Unexpected error %s: %s" % (endpoint, str(e)),
                ErrorCategory.UNKNOWN)
            return {'status': 'error', 'error': str(e)}

    def _log_error(
        self,
        problem_alias: Optional[str],  # pylint: disable=unused-argument
        error_message: str,
        category: ErrorCategory) -> None:  # pylint: disable=unused-argument
        """Log detailed error information for analysis.

        Args:
            problem_alias: Problem alias (reserved for future analytics)
            error_message: Error message to log
            category: Error category (reserved for future analytics)
        """
        # Convert stats with datetime handling
        stats_dict = asdict(self.stats)
        if 'start_time' in stats_dict and stats_dict['start_time']:
            stats_dict['start_time'] = stats_dict['start_time'].isoformat()
        if 'end_time' in stats_dict and stats_dict['end_time']:
            stats_dict['end_time'] = stats_dict['end_time'].isoformat()

        # Log error details
        self.main_logger.error("Error logged: %s", error_message)
        # Note: problem_alias and category are logged for future use



    def submit_editorial_job(self, problem_alias: str) -> bool:
        """Submit a single editorial generation job."""
        self.main_logger.info(
            "📝 Submitting editorial job for: %s", problem_alias)

        try:
            # Submit job via AI Editorial API
            result = self._make_api_call("aiEditorial/generate", {
                'problem_alias': problem_alias,
                'language': 'en'  # Default to English
            })

            if result.get("status") == "ok":
                job_id = result.get("job_id")
                if job_id:
                    # Create job record
                    job = EditorialJob(
                        problem_alias=problem_alias,
                        job_id=job_id,
                        status=JobStatus.PENDING,
                        submitted_at=datetime.now()
                    )

                    self.jobs[job_id] = job
                    self.stats.jobs_submitted += 1

                    self.main_logger.info("✅ Job submitted successfully")
                    self.main_logger.info("   Problem: %s", problem_alias)
                    self.main_logger.info("   Job ID: %s", job_id)

                    return True

                self.main_logger.error(
                    "❌ No job ID returned for %s", problem_alias)
                self._log_error(
                    problem_alias,
                    "No job ID in response",
                    ErrorCategory.API_ERROR)
                return False

            error_msg = result.get('error', 'Unknown error')
            self.main_logger.error(
                "❌ Job submission failed for %s: %s",
                problem_alias, error_msg)
            self._log_error(
                problem_alias,
                error_msg,
                ErrorCategory.API_ERROR)
            return False

        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed to handle any job submission failures
            # gracefully
            self.main_logger.error(
                "❌ Exception during job submission for %s: %s",
                problem_alias, str(e))
            self._log_error(problem_alias, str(e), ErrorCategory.UNKNOWN)
            return False

    def check_job_status(self, job_id: str) -> JobStatus:
        """Check the status of a specific job."""
        if job_id not in self.jobs:
            self.main_logger.warning("Job %s not found in tracking", job_id)
            return JobStatus.UNKNOWN

        job = self.jobs[job_id]

        try:
            result = self._make_api_call("aiEditorial/status", {
                'job_id': job_id
            })

            if result.get("status") == "ok":
                job_data = result.get("job", {})
                api_status = job_data.get("status", "unknown").lower()

                # Map API status to our enum
                status_mapping = {
                    'queued': JobStatus.PENDING,
                    'processing': JobStatus.RUNNING,
                    'completed': JobStatus.COMPLETED,
                    'failed': JobStatus.FAILED
                }

                new_status = status_mapping.get(api_status, JobStatus.UNKNOWN)

                # Update job record
                if new_status != job.status:
                    old_status = job.status
                    job.status = new_status

                    self.main_logger.info(
                        "📊 Status change: %s (%s)", job.problem_alias, job_id)
                    self.main_logger.info(
                        "   %s → %s", old_status.value, new_status.value)

                    if new_status == JobStatus.COMPLETED:
                        job.completed_at = datetime.now()
                        # Extract editorial content and validation verdict
                        job.editorial_content = (
                            "EN: %s..." % job_data.get('md_en', '')[:100]
                            if job_data.get('md_en') else None)
                        job.validation_verdict = job_data.get(
                            'validation_verdict')
                        self.stats.jobs_completed += 1

                        # Log validation verdict if available
                        verdict_msg = ""
                        if job.validation_verdict:
                            verdict_msg = (" (Validation: "
                                           "%s)" % job.validation_verdict)
                        self.main_logger.info(
                            "🎉 Completed: %s%s",
                            job.problem_alias, verdict_msg)

                    elif new_status == JobStatus.FAILED:
                        job.error_message = job_data.get(
                            "error_message", "Unknown failure")
                        job.error_category = ErrorCategory.API_ERROR
                        self.stats.jobs_failed += 1
                        self.main_logger.warning(
                            "❌ Failed: %s - %s",
                            job.problem_alias, job.error_message)
                        self._log_error(
                            job.problem_alias,
                            job.error_message or "Unknown error",
                            job.error_category)

                return new_status

            self.main_logger.warning(
                "⚠️  Status check failed for %s: %s",
                job_id, result.get('error'))
            return JobStatus.UNKNOWN

        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed to handle any status check failures
            # gracefully
            self.main_logger.error(
                "❌ Exception checking status for %s: %s", job_id, str(e))
            return JobStatus.UNKNOWN

    def poll_all_jobs(self) -> None:
        """Poll status for all pending/running jobs."""
        pending_jobs = [
            job for job in self.jobs.values() if job.status in [
                JobStatus.PENDING, JobStatus.RUNNING]]

        if not pending_jobs:
            self.main_logger.info("📊 No jobs to poll")
            return

        self.main_logger.info("📊 Polling %s active jobs...", len(pending_jobs))

        for job in pending_jobs:
            # Check if job has been running too long
            if job.submitted_at:
                runtime = datetime.now() - job.submitted_at
                if runtime.total_seconds() > self.max_poll_duration:
                    self.main_logger.warning(
                        "⏰ Job timeout: %s (%s)",
                        job.problem_alias, job.job_id)
                    job.status = JobStatus.FAILED
                    job.error_message = "Job timeout"
                    job.error_category = ErrorCategory.TIMEOUT
                    self.stats.jobs_failed += 1
                    continue

            # Poll job status
            if job.job_id:
                self.check_job_status(job.job_id)

            # Small delay to avoid overwhelming the API
            time.sleep(1)

    def wait_for_completion(self, max_wait_time: int = 7200) -> None:
        """Wait for all jobs to complete with periodic status polling."""
        self.main_logger.info(
            "⏳ Waiting for job completion (max %ss)...", max_wait_time)

        start_time = time.time()

        while True:
            # Poll all jobs
            self.poll_all_jobs()

            # Check if all jobs are done
            active_jobs = [
                job for job in self.jobs.values() if job.status in [
                    JobStatus.PENDING, JobStatus.RUNNING]]

            if not active_jobs:
                self.main_logger.info("🎉 All jobs completed!")
                break

            # Check timeout
            elapsed_time = time.time() - start_time
            if elapsed_time > max_wait_time:
                self.main_logger.warning(
                    "⏰ Maximum wait time (%ss) reached", max_wait_time)
                self.main_logger.warning(
                    "   %d jobs still active", len(active_jobs))
                break

            # Progress update
            total_jobs = len(self.jobs)
            completed_jobs = total_jobs - len(active_jobs)
            progress = (
                completed_jobs /
                total_jobs *
                100) if total_jobs > 0 else 0

            progress_msg = (
                "📊 Progress: %d/%d "
                "(%.1f%%) - ⏱️  %.0fs elapsed" %
                (completed_jobs, total_jobs, progress, elapsed_time))
            self.main_logger.info(progress_msg)

            # Wait before next poll cycle
            time.sleep(self.status_poll_interval)

    def load_problems_from_file(
        self, filename: Optional[str] = None) -> List[str]:
        """Load problem list from text file.
        Uses environment-configured filename by default."""
        if filename is None:
            filename = self.problems_filename
            self.main_logger.info(
                "📁 Using environment-configured problems file: %s", filename)

        file_path = Path(__file__).parent / filename

        if not file_path.exists():
            self.main_logger.warning(
                "⚠️  Problem list file not found: %s", filename)
            self.main_logger.info("🔍 Automatically discovering problems...")

            # Import and run the problem discovery
            try:
                # Import the extractor class from get_user_problems.py
                sys.path.insert(0, str(Path(__file__).parent))
                # pylint: disable=import-outside-toplevel
                from get_user_problems import UserProblemsExtractor

                # Create extractor and get problems
                extractor = UserProblemsExtractor()
                problems = extractor.extract_all_problems()

                if problems:
                    # Save the discovered problems using the extractor's save
                    # method (uses env filename)
                    extractor.save_problems_to_file(problems)
                    self.main_logger.info(
                        "✅ Auto-created %s with "
                        "%d problems", filename, len(problems))
                else:
                    self.main_logger.error(
                        "❌ No problems found during auto-discovery")
                    raise FileNotFoundError(
                        "Could not create %s: no problems found" % filename)

            except Exception as e:  # pylint: disable=broad-except
                # Broad except needed for any file creation failures
                self.main_logger.error(
                    "❌ Failed to auto-create %s: %s", filename, str(e))
                self.main_logger.info(
                    "💡 Please run 'python get_user_problems.py' "
                    "manually first")
                raise FileNotFoundError(
                    "Problem list file not found and auto-creation "
                    "failed: %s" % file_path) from e

        self.main_logger.info("📂 Loading problems from: %s", filename)

        problems = []
        with open(file_path, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                # Skip comments and empty lines
                if line and not line.startswith('#'):
                    problems.append(line)

        self.main_logger.info("📝 Loaded %d problems from file", len(problems))
        self.stats.total_problems = len(problems)

        return problems

    def save_session_state(self) -> None:
        """Save current session state for resume functionality."""
        try:
            # Convert datetime objects to ISO strings to avoid serialization
            # issues
            stats_dict = asdict(self.stats)
            if 'start_time' in stats_dict and stats_dict['start_time']:
                stats_dict['start_time'] = stats_dict['start_time'].isoformat()
            if 'end_time' in stats_dict and stats_dict['end_time']:
                stats_dict['end_time'] = stats_dict['end_time'].isoformat()

            # Convert job datetime fields and enum values
            jobs_dict = {}
            for job_id, job in self.jobs.items():
                job_dict = asdict(job)
                if 'submitted_at' in job_dict and job_dict['submitted_at']:
                    job_dict['submitted_at'] = (
                        job_dict['submitted_at'].isoformat())
                if 'completed_at' in job_dict and job_dict['completed_at']:
                    job_dict['completed_at'] = (
                        job_dict['completed_at'].isoformat())
                # Convert enum values to strings
                if 'status' in job_dict and hasattr(
                    job_dict['status'], 'value'):
                    job_dict['status'] = job_dict['status'].value
                if ('error_category' in job_dict and
                        job_dict['error_category'] and
                        hasattr(job_dict['error_category'], 'value')):
                    job_dict['error_category'] = (
                        job_dict['error_category'].value)
                jobs_dict[job_id] = job_dict

            state_data = {
                'timestamp': datetime.now().isoformat(),
                'username': self.username,
                'stats': stats_dict,
                'jobs': jobs_dict
            }

            with open(self.state_file, 'w', encoding='utf-8') as f:
                json.dump(state_data, f, indent=2, ensure_ascii=False)

            self.main_logger.debug(
                "💾 Session state saved to %s", self.state_file)

        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed for any session save failures
            self.main_logger.warning(
                "⚠️  Failed to save session state: %s", str(e))

    def _load_session_state(self) -> None:
        """Load previous session state for resume functionality."""
        if not self.state_file.exists():
            self.main_logger.info("📂 No previous session state found")
            return

        try:
            with open(self.state_file, 'r', encoding='utf-8') as f:
                state_data = json.load(f)

            # Restore jobs
            for job_id, job_data in state_data.get('jobs', {}).items():
                job = EditorialJob(
                    problem_alias=job_data['problem_alias'],
                    job_id=job_data['job_id'],
                    status=JobStatus(
                        job_data['status']),
                    submitted_at=datetime.fromisoformat(
                        job_data['submitted_at']) if job_data[
                            'submitted_at'] else None,
                    completed_at=datetime.fromisoformat(
                        job_data['completed_at']) if job_data[
                            'completed_at'] else None,
                    error_message=job_data.get('error_message'),
                    error_category=ErrorCategory(
                        job_data['error_category']) if job_data.get(
                            'error_category') else None,
                    retry_count=job_data.get(
                        'retry_count',
                        0),
                    editorial_content=job_data.get('editorial_content'))
                self.jobs[job_id] = job

            # Restore stats
            stats_data = state_data.get('stats', {})
            self.stats.total_problems = stats_data.get('total_problems', 0)
            self.stats.jobs_submitted = stats_data.get('jobs_submitted', 0)

            self.main_logger.info(
                "📂 Resumed session with %d jobs", len(self.jobs))
            self.main_logger.info(
                "   Submitted: %d", self.stats.jobs_submitted)
            active_jobs = len([
                j for j in self.jobs.values()
                if j.status in [JobStatus.PENDING, JobStatus.RUNNING]
            ])
            self.main_logger.info("   Active jobs: %d", active_jobs)

        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed for any session load failures
            self.main_logger.warning(
                "⚠️  Failed to load session state: %s", str(e))

    def generate_final_report(self) -> Dict[str, Any]:
        """Generate comprehensive final report with all statistics and
        results."""
        self.stats.end_time = datetime.now()

        # Calculate statistics
        total_runtime = (
            self.stats.end_time -
            (self.stats.start_time or datetime.now())).total_seconds()

        success_rate = (
            self.stats.jobs_completed /
            self.stats.jobs_submitted *
            100) if self.stats.jobs_submitted > 0 else 0
        api_success_rate = (
            (self.stats.api_calls_made -
             self.stats.api_errors) /
            self.stats.api_calls_made *
            100) if self.stats.api_calls_made > 0 else 0

        # Categorize jobs
        job_summary = {
            'completed': [
                job for job in self.jobs.values()
                if job.status == JobStatus.COMPLETED],
            'failed': [
                job for job in self.jobs.values()
                if job.status == JobStatus.FAILED],
            'pending': [
                job for job in self.jobs.values()
                if job.status in [JobStatus.PENDING, JobStatus.RUNNING]]
        }

        # Validation verdict analysis
        validation_stats = {}
        for job in job_summary['completed']:
            if job.validation_verdict:
                verdict = job.validation_verdict
                if verdict not in validation_stats:
                    validation_stats[verdict] = 0
                validation_stats[verdict] += 1

        # Error analysis
        error_categories: Dict[str, List[Dict[str, Optional[str]]]] = {}
        for job in job_summary['failed']:
            if job.error_category:
                category = job.error_category.value
                if category not in error_categories:
                    error_categories[category] = []
                error_categories[category].append({
                    'problem': job.problem_alias,
                    'error': job.error_message
                })

        report = {
            'session_summary': {
                'start_time': (
                    self.stats.start_time or datetime.now()).isoformat(),
                'end_time': self.stats.end_time.isoformat(),
                'total_runtime_seconds': total_runtime,
                'total_runtime_formatted': str(
                    timedelta(seconds=int(total_runtime))),
                'username': self.username,
                'auth_method': self.auth_method,
                'test_mode': self.test_mode,
                'resume_mode': self.resume_mode,
                'run_timestamp': self.run_timestamp
            },
            'job_statistics': {
                'total_problems': self.stats.total_problems,
                'jobs_submitted': self.stats.jobs_submitted,
                'jobs_completed': self.stats.jobs_completed,
                'jobs_failed': self.stats.jobs_failed,
                'jobs_pending': len(job_summary['pending']),
                'success_rate_percent': round(success_rate, 2)
            },
            'api_statistics': {
                'total_api_calls': self.stats.api_calls_made,
                'api_errors': self.stats.api_errors,
                'rate_limit_hits': self.stats.rate_limit_hits,
                'api_success_rate_percent': round(api_success_rate, 2)
            },
            'validation_statistics': {
                'total_validated': len([
                    j for j in job_summary['completed']
                    if j.validation_verdict]),
                'verdict_breakdown': validation_stats,
                'most_common_verdict': max(
                    validation_stats.items(),
                    key=lambda x: x[1])[0] if validation_stats else None
            },
            'performance_metrics': {
                'average_job_completion_time_seconds': (
                    self._calculate_average_completion_time()),
                'jobs_per_hour': round(
                    self.stats.jobs_completed / (total_runtime / 3600),
                    2) if total_runtime > 0 else 0,
                'api_calls_per_minute': round(
                    self.stats.api_calls_made / (total_runtime / 60),
                    2) if total_runtime > 0 else 0
            },
            'error_analysis': error_categories,
            'completed_editorials': [
                {
                    'problem_alias': job.problem_alias,
                    'job_id': job.job_id,
                    'completion_time': (
                        job.completed_at.isoformat()
                        if job.completed_at else None),
                    'runtime_seconds': (
                        (job.completed_at - job.submitted_at).total_seconds()
                        if job.completed_at and job.submitted_at else None),
                    'validation_verdict': job.validation_verdict,
                    'has_content': bool(job.editorial_content)
                }
                for job in job_summary['completed']
            ],
            'failed_jobs': [
                {
                    'problem_alias': job.problem_alias,
                    'job_id': job.job_id,
                    'error_message': job.error_message,
                    'error_category': (job.error_category.value
                                       if job.error_category else None),
                    'retry_count': job.retry_count
                }
                for job in job_summary['failed']
            ]
        }

        return report

    def _calculate_average_completion_time(self) -> Optional[float]:
        """Calculate average completion time for completed jobs."""
        completed_jobs = [
            job for job in self.jobs.values()
            if job.status == JobStatus.COMPLETED]

        if not completed_jobs:
            return None

        total_time = 0.0
        valid_jobs = 0

        for job in completed_jobs:
            if job.submitted_at and job.completed_at:
                runtime = (job.completed_at - job.submitted_at).total_seconds()
                total_time += runtime
                valid_jobs += 1

        return (round(total_time / valid_jobs, 2)
                if valid_jobs > 0 else None)

    # pylint: disable=too-many-statements
    def save_final_report(self, report: Dict[str, Any]) -> None:
        """Save a single TXT report file with comprehensive summary."""
        try:
            # Create a single text report file
            report_file = self.results_dir / (
                "batch_results_%s.txt" % self.run_timestamp)

            with open(report_file, 'w', encoding='utf-8') as f:
                f.write("=" * 80 + "\n")
                f.write("OMEGAUP AI EDITORIAL BATCH GENERATION REPORT\n")
                f.write("=" * 80 + "\n\n")

                # Session Summary
                f.write("SESSION INFORMATION:\n")
                f.write("-" * 40 + "\n")
                f.write("Timestamp: %s\n" % self.run_timestamp)
                f.write("User: %s (%s)\n" % (self.username, self.auth_method))
                runtime_formatted = (
                    report['session_summary']['total_runtime_formatted'])
                f.write("Duration: %s\n" % runtime_formatted)
                f.write("Test Mode: %s\n" % self.test_mode)
                f.write("Resume Mode: %s\n\n" % self.resume_mode)

                # Job Statistics
                f.write("JOB STATISTICS:\n")
                f.write("-" * 40 + "\n")
                stats = report['job_statistics']
                f.write("Total Problems: %s\n" % stats['total_problems'])
                f.write("Jobs Submitted: %s\n" % stats['jobs_submitted'])
                f.write("✅ Completed: %s\n" % stats['jobs_completed'])
                f.write("❌ Failed: %s\n" % stats['jobs_failed'])
                f.write("⏳ Pending: %s\n" % stats['jobs_pending'])
                f.write(
                    "📈 Success Rate: %s %%\n\n" %
                    stats['success_rate_percent'])

                # API Statistics
                f.write("API STATISTICS:\n")
                f.write("-" * 40 + "\n")
                api_stats = report['api_statistics']
                f.write("Total API Calls: %s\n" % api_stats['total_api_calls'])
                f.write("API Errors: %s\n" % api_stats['api_errors'])
                f.write("Rate Limit Hits: %s\n" % api_stats['rate_limit_hits'])
                f.write(
                    "API Success Rate: %s %%\n\n" %
                    api_stats['api_success_rate_percent'])

                # Validation Statistics
                f.write("VALIDATION STATISTICS:\n")
                f.write("-" * 40 + "\n")
                val_stats = report['validation_statistics']
                f.write(
                    "Jobs with Validation: %s\n" %
                    val_stats['total_validated'])
                if val_stats['verdict_breakdown']:
                    f.write("Verdict Breakdown:\n")
                    for verdict, count in val_stats['verdict_breakdown'].items(
                    ):
                        percentage = (
                            count /
                            val_stats['total_validated'] *
                            100) if val_stats['total_validated'] > 0 else 0
                        f.write(
                            "  %s: %s (%.1f%%)\n" %
                            (verdict, count, percentage))
                    f.write(
                        "Most Common: %s\n" %
                        val_stats['most_common_verdict'])
                else:
                    f.write("No validation data available\n")
                f.write("\n")

                # Performance Metrics
                f.write("PERFORMANCE METRICS:\n")
                f.write("-" * 40 + "\n")
                perf = report['performance_metrics']
                avg_time = perf['average_job_completion_time_seconds']
                f.write("Avg Completion Time: %ss\n" % (
                    "%.2f" % avg_time if avg_time else "N/A"))
                f.write("Jobs/Hour: %.1f\n" % perf['jobs_per_hour'])
                f.write(
                    "API Calls/Min: %.2f\n\n" % perf['api_calls_per_minute'])

                # Individual Job Results
                f.write("INDIVIDUAL JOB RESULTS:\n")
                f.write("-" * 40 + "\n")

                # Completed jobs
                for job_data in report['completed_editorials']:
                    f.write("Problem: %s\n" % job_data['problem_alias'])
                    f.write("  Status: completed\n")
                    f.write("  Job ID: %s\n" % job_data['job_id'])
                    if job_data.get('validation_verdict'):
                        f.write("  Validation: %s\n" %
                                job_data['validation_verdict'])
                    if job_data.get('completion_time'):
                        f.write("  Completed: %s\n" %
                                job_data['completion_time'])
                    f.write("\n")

                # Failed jobs
                for job_data in report['failed_jobs']:
                    f.write("Problem: %s\n" % job_data['problem_alias'])
                    f.write("  Status: failed\n")
                    f.write("  Job ID: %s\n" % job_data['job_id'])
                    if job_data.get('error_message'):
                        f.write("  Error: %s\n" % job_data['error_message'])
                    f.write("\n")
                    f.write("\n")

                f.write("=" * 80 + "\n")
                f.write(
                    "Report generated by omegaUp AI Editorial "
                    "Batch Generator\n")
                f.write("=" * 80 + "\n")

            self.main_logger.info("📊 Report saved: %s", report_file)

        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed for any report save failures
            self.main_logger.error("❌ Failed to save report: %s", str(e))

    def print_final_summary(self, report: Dict[str, Any]) -> None:
        """Print a comprehensive final summary to console and log."""
        session = report['session_summary']
        jobs = report['job_statistics']
        api = report['api_statistics']
        validation = report['validation_statistics']
        perf = report['performance_metrics']

        self.main_logger.info("%s", "\n" + "=" * 80)
        self.main_logger.info("📊 FINAL SUMMARY")
        self.main_logger.info("%s", "=" * 80)

        self.main_logger.info(
            "⏱️  Session Duration: %s", session['total_runtime_formatted'])
        self.main_logger.info(
            "👤 User: %s (%s)", session['username'], session['auth_method'])
        self.main_logger.info("🧪 Test Mode: %s", session['test_mode'])
        self.main_logger.info("🔄 Resume Mode: %s", session['resume_mode'])

        self.main_logger.info("\n📝 JOB STATISTICS:")
        self.main_logger.info("   Total Problems: %s", jobs['total_problems'])
        self.main_logger.info("   Jobs Submitted: %s", jobs['jobs_submitted'])
        self.main_logger.info("   ✅ Completed: %s", jobs['jobs_completed'])
        self.main_logger.info("   ❌ Failed: %s", jobs['jobs_failed'])
        self.main_logger.info("   ⏳ Pending: %s", jobs['jobs_pending'])
        self.main_logger.info(
            "   📈 Success Rate: %s %%", jobs['success_rate_percent'])

        self.main_logger.info("\n🌐 API STATISTICS:")
        self.main_logger.info("   Total API Calls: %s", api['total_api_calls'])
        self.main_logger.info("   API Errors: %s", api['api_errors'])
        self.main_logger.info("   Rate Limit Hits: %s", api['rate_limit_hits'])
        self.main_logger.info(
            "   API Success Rate: %s%%", api['api_success_rate_percent'])

        self.main_logger.info("\n🔍 VALIDATION STATISTICS:")
        self.main_logger.info(
            "   Jobs with Validation: %s", validation['total_validated'])
        if validation['verdict_breakdown']:
            self.main_logger.info("   Verdict Breakdown:")
            total_validated = validation['total_validated']
            for verdict, count in validation['verdict_breakdown'].items():
                percentage = (
                    count /
                    total_validated *
                    100) if total_validated > 0 else 0
                self.main_logger.info(
                    "     %s: %s (%.1f%%)", verdict, count, percentage)

            if validation['most_common_verdict']:
                self.main_logger.info(
                    "   Most Common: %s", validation['most_common_verdict'])

            # Additional validation insights
            ac_count = validation['verdict_breakdown'].get('AC', 0)
            wa_count = validation['verdict_breakdown'].get('WA', 0)
            tle_count = validation['verdict_breakdown'].get('TLE', 0)
            ce_count = validation['verdict_breakdown'].get('CE', 0)

            self.main_logger.info("   Validation Summary:")
            self.main_logger.info("     ✅ Accepted (AC): %s", ac_count)
            if wa_count > 0:
                self.main_logger.info("     ❌ Wrong Answer (WA): %s", wa_count)
            if tle_count > 0:
                self.main_logger.info("     ⏰ Time Limit (TLE): %s", tle_count)
            if ce_count > 0:
                self.main_logger.info(
                    "     🔧 Compile Error (CE): %s", ce_count)

            # Success rate based on AC
            if total_validated > 0:
                validation_success_rate = (ac_count / total_validated * 100)
                self.main_logger.info(
                    "     📊 Validation Success Rate: %.1f%%",
                    validation_success_rate)
        else:
            self.main_logger.info("   No validation data available")

        self.main_logger.info("\n🚀 PERFORMANCE METRICS:")
        avg_time = perf['average_job_completion_time_seconds']
        self.main_logger.info(
            "   Avg Completion Time: %ss", avg_time if avg_time
            else "N/A")
        self.main_logger.info("   Jobs/Hour: %s", perf['jobs_per_hour'])
        self.main_logger.info(
            "   API Calls/Min: %s", perf['api_calls_per_minute'])

        if report['error_analysis']:
            self.main_logger.info("\n❌ ERROR ANALYSIS:")
            for category, errors in report['error_analysis'].items():
                self.main_logger.info(
                    "   %s: %d errors", category, len(errors))

        self.main_logger.info(
            "\n📁 RESULTS SAVED TO: results/batch_results_%s.txt",
            self.run_timestamp)
        self.main_logger.info("%s", "=" * 80)
        self.main_logger.info("🎯 BATCH EDITORIAL GENERATION COMPLETED")
        self.main_logger.info("%s", "=" * 80)

    def run_batch_generation(self, problems: List[str]) -> None:
        """Main execution method for batch editorial generation."""
        try:
            self.main_logger.info(
                "🚀 Starting batch generation for %d problems", len(problems))

            if self.test_mode:
                self.main_logger.info(
                    "🧪 TEST MODE: Limited to first problem only")
                problems = problems[:1]

            self.stats.total_problems = len(problems)

            # Phase 1: Submit all jobs
            self.main_logger.info("%s", "\n" + "=" * 60)
            self.main_logger.info("PHASE 1: JOB SUBMISSION")
            self.main_logger.info("%s", "=" * 60)

            for i, problem in enumerate(problems, 1):
                self.main_logger.info(
                    "📝 [%d/%d] Processing: %s", i, len(problems), problem)

                # Check if already submitted (resume mode)
                if any(job.problem_alias ==
                       problem for job in self.jobs.values()):
                    self.main_logger.info(
                        "   ⏭️  Already submitted, skipping")
                    continue

                # Try to submit job
                success = self.submit_editorial_job(problem)

                if not success:
                    self.main_logger.warning(
                        "⚠️  Failed to submit job for %s", problem)

                # Save state periodically
                if i % 10 == 0:
                    self.save_session_state()

                # Small delay between submissions
                time.sleep(2)

            # Phase 2: Wait for completion
            self.main_logger.info("%s", "\n" + "=" * 60)
            self.main_logger.info("PHASE 2: STATUS MONITORING")
            self.main_logger.info("%s", "=" * 60)

            if self.jobs:
                self.wait_for_completion()
            else:
                self.main_logger.warning(
                    "⚠️  No jobs were submitted successfully")

            # Phase 3: Final reporting
            self.main_logger.info("%s", "\n" + "=" * 60)
            self.main_logger.info("PHASE 3: FINAL REPORTING")
            self.main_logger.info("%s", "=" * 60)

            report = self.generate_final_report()
            self.save_final_report(report)
            self.save_session_state()
            self.print_final_summary(report)

        except KeyboardInterrupt:
            self.main_logger.info("\n⏹️  Interrupted by user")
            self.main_logger.info("💾 Saving current state...")
            self.save_session_state()

            # Generate partial report
            report = self.generate_final_report()
            self.save_final_report(report)
            self.print_final_summary(report)

        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed to catch any unexpected runtime errors
            self.main_logger.error("💥 Unexpected error: %s", str(e))
            self.save_session_state()
            raise


def main() -> int:
    """Main entry point with argument parsing."""
    # Load environment variables to get default problems file
    env_path = Path(__file__).parent.parent / ".env"
    load_dotenv(env_path)
    default_problems_file = os.getenv("PROBLEMS_LIST_FILE", "my_problems.txt")

    parser = argparse.ArgumentParser(
        description="Batch generate editorials using AI Editorial API",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python batch_generate_editorials.py
      # Use %s (from env)
  python batch_generate_editorials.py --problems-file custom.txt
      # Use custom file
  python batch_generate_editorials.py --resume
      # Resume previous session
  python batch_generate_editorials.py --test-mode --problem-alias example
      # Test single problem

Environment Variables:
  PROBLEMS_LIST_FILE: Default problems file (currently: %s)
  OMEGAUP_OUAT_TOKEN: Authentication token (preferred)
  OMEGAUP_USERNAME: Username for login authentication
  OMEGAUP_PASSWORD: Password for login authentication
        """ % (default_problems_file, default_problems_file)
    )

    parser.add_argument(
        '--problems-file',
        default=default_problems_file,
        help=('Text file containing problem aliases '
              '(default: %s)' % default_problems_file)
    )

    parser.add_argument(
        '--resume',
        action='store_true',
        help='Resume previous session from saved state'
    )

    parser.add_argument(
        '--test-mode',
        action='store_true',
        help='Test mode: process only first problem'
    )

    parser.add_argument(
        '--problem-alias',
        help='Single problem alias for testing (implies --test-mode)'
    )

    args = parser.parse_args()

    # Single problem test mode
    if args.problem_alias:
        args.test_mode = True
        problems = [args.problem_alias]
        print(
            "🧪 Test mode: Processing single problem '%s'" %
            args.problem_alias)
    else:
        # Load problems from file (uses environment-configured file by default)
        try:
            generator = BatchEditorialGenerator(
                test_mode=args.test_mode, resume=args.resume)
            problems = generator.load_problems_from_file(
                args.problems_file if args.problems_file !=
                default_problems_file else None)
        except FileNotFoundError:
            print("❌ Error: Problem file '%s' not found" % args.problems_file)
            print("💡 Run 'python get_user_problems.py' first to "
                  "generate problem list")
            print(
                "📁 Expected file: "
                "%s" % (Path(__file__).parent / args.problems_file))
            return 1
        except Exception as e:  # pylint: disable=broad-except
            # Broad except needed for any problem loading failures
            print("❌ Error loading problems: %s" % str(e))
            return 1

    if not problems:
        print("❌ No problems found to process")
        return 1

    try:
        # Initialize generator if not already done
        if not args.problem_alias:
            pass  # Already initialized above
        else:
            generator = BatchEditorialGenerator(
                test_mode=args.test_mode, resume=args.resume)

        # Log the workflow integration
        generator.main_logger.info(
            "🔗 Integration: get_user_problems.py → %s → "
            "batch_generate_editorials.py",
            args.problems_file)

        # Run batch generation
        generator.run_batch_generation(problems)

        print("\n🎉 SUCCESS! Batch editorial generation completed.")
        print(
            "📊 Check results/overview_%s.json "
            "for detailed statistics." % generator.run_timestamp)
        print("📝 Check logs/ directory for detailed execution logs.")

        return 0

    except KeyboardInterrupt:
        print("\n⏹️  Process interrupted by user")
        return 0
    except (ValueError, FileNotFoundError, ConnectionError) as e:
        print("💥 Unexpected error: %s" % str(e))
        return 1


if __name__ == "__main__":
    sys.exit(main())
