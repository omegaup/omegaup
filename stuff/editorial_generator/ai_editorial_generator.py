#!/usr/bin/env python3
"""
AI Editorial Generator Configuration and Core Components

This module provides configuration management, statistics tracking,
and API client functionality for the omegaUp editorial generation system.

PROGRAM FLOW:
1. Input Loading: Load problem aliases from text file
2. Editorial Existence Check: Check if editorial already exists, skip if found
3. Problem Details: Fetch problem details from omegaUp API
4. Admin Access Check: Verify admin access to see existing AC solutions
5. AC Solution Discovery: Look for existing AC solutions
6. AC Solution Generation: If no AC found, generate and verify solution
7. Multi-Language Editorial Generation: Generate editorials in 3 languages
8. Website Updates: Upload all 3 language editorials to omegaUp website
9. Statistics & Reporting: Track success/failure rates and generate report

KEY FEATURES:
- Skips problems that already have editorials
- Uses existing AC solutions as reference for high-quality editorials
- Generates and verifies solutions if none exist
- Supports 3 languages: English (en), Spanish (es), Portuguese (pt)
- Updates editorials directly on the omegaUp website
- Comprehensive logging and error handling
- Detailed statistics tracking with problem categorization
"""

import logging
import os
from datetime import datetime
from typing import Dict, Any, Optional
from pathlib import Path

import requests
from dotenv import load_dotenv  # type: ignore


def setup_logging() -> logging.Logger:
    """Setup logging configuration."""
    log_dir = Path("logs")
    log_dir.mkdir(exist_ok=True)

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    log_file = log_dir / f"AiEG1_{timestamp}.log"

    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler(log_file, encoding='utf-8'),
            logging.StreamHandler()
        ]
    )
    return logging.getLogger(__name__)


logger = setup_logging()


class EditorialGeneratorConfig:
    """Configuration management for the editorial generator."""

    def __init__(self) -> None:
        """Initialize configuration from environment variables."""
        load_dotenv()

        # API Configuration
        self.api_url = os.getenv("OMEGAUP_API_URL", "https://omegaup.com/api")
        self.base_url = os.getenv("OMEGAUP_BASE_URL", "https://omegaup.com")

        # Authentication credentials
        self.username = os.getenv("OMEGAUP_USERNAME")
        self.password = os.getenv("OMEGAUP_PASSWORD")

        if not self.username or not self.password:
            raise ValueError(
                "OMEGAUP_USERNAME and OMEGAUP_PASSWORD must be set"
            )

        # OpenAI API key
        self.openai_api_key = os.getenv("OPENAI_API_KEY")
        if not self.openai_api_key:
            raise ValueError("OPENAI_API_KEY must be set in .env file")

        # Target languages for editorial generation
        self.target_languages = {
            'es': 'Spanish',
            'en': 'English',
            'pt': 'Portuguese'
        }


class StatsTracker:
    """Statistics tracking for editorial generation process."""

    def __init__(self) -> None:
        """Initialize statistics tracker."""
        self.stats: Dict[str, Any] = {
            'total_problems': 0,
            'problems_with_existing_editorial': 0,
            'problems_needing_editorial': 0,
            'editorials_generated_successfully': 0,
            'editorials_failed': 0,
            'problems_with_admin_access': 0,
            'problems_with_existing_ac': 0,
            'problems_ac_generated': 0,
            'problems_ac_verified': 0,
            'problems_ac_verification_failed': 0,
            'website_updates_successful': 0,
            'website_updates_failed': 0,
            'api_errors': 0,
            'karel_skipped': 0,
            'karel_problem_names': [],
            'existing_editorial_problems': [],
            'generated_editorial_problems': [],
            'failed_editorial_problems': [],
            'ac_verification_failed_problems': [],
            'admin_access_problem_names': [],
            'existing_ac_problem_names': [],
            'verified_ac_problem_names': [],
            'problem_results': []
        }

    def increment(self, key: str) -> None:
        """Increment a counter statistic."""
        if key in self.stats and isinstance(self.stats[key], int):
            self.stats[key] += 1

    def append_to_list(self, key: str, value: str) -> None:
        """Append a value to a list statistic."""
        if key in self.stats and isinstance(self.stats[key], list):
            self.stats[key].append(value)

    def set_value(self, key: str, value: Any) -> None:
        """Set a statistic value."""
        if key in self.stats:
            self.stats[key] = value

    def get_value(self, key: str) -> Any:
        """Get a statistic value."""
        return self.stats.get(key, 0)


class OmegaUpAPIClient:
    """API client for omegaUp interactions."""

    def __init__(self, config: EditorialGeneratorConfig) -> None:
        """Initialize API client."""
        self.config = config

        # Initialize session for persistent connections and cookies
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'omegaUp-MultiLang-Editorial-Generator/1.0',
            'Accept': 'application/json',
            'Accept-Language': 'en-US,en;q=0.9,es;q=0.8,pt;q=0.7'
        })

        # Login for authenticated access
        self._login()

    def _login(self) -> None:
        """Login using the official API with username and password."""
        try:
            # Login endpoint
            login_url = f"{self.config.api_url}/user/login"
            login_data = {
                'usernameOrEmail': self.config.username,
                'password': self.config.password
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

            if response.status_code == 200:
                result = response.json()
                if result.get("status") == "ok":
                    logging.getLogger(__name__).info(
                        "Successfully logged in to omegaUp"
                    )
                else:
                    raise ValueError(
                        f"Login failed: {result.get('error', 'Unknown error')}"
                    )
            else:
                raise ValueError(
                    f"HTTP {response.status_code}: Login request failed"
                )

        except requests.RequestException as e:
            logging.getLogger(__name__).error("Failed to login: %s", str(e))
            raise

    def check_existing_editorial(self, problem_alias: str) -> bool:
        """Check if the problem has an editorial using the solution API."""
        try:
            url = f"{self.config.api_url}/problem/solution"
            params = {'problem_alias': problem_alias}

            response = self.session.get(url, params=params, timeout=(10, 30))

            # If we get a successful response, it means an editorial exists
            if response.status_code == 200:
                result = response.json()
                if result.get("status") == "ok":
                    solution = result.get('solution', {})
                    # Check if there's actual content in the editorial
                    if solution and solution.get('markdown', '').strip():
                        logging.getLogger(__name__).info(
                            "[%s] Existing editorial found - SKIPPING",
                            problem_alias
                        )
                        return True

            # If we get 404 or any error, assume no editorial exists
            logging.getLogger(__name__).info(
                "[%s] No existing editorial found", problem_alias
            )
            return False

        except requests.RequestException as e:
            logging.getLogger(__name__).warning(
                "[%s] Request error checking editorial: %s",
                problem_alias, str(e)
            )
            # If there's an error, assume no editorial to be safe
            return False

    def get_problem_details(
        self, problem_alias: str
    ) -> Optional[Dict[str, Any]]:
        """Fetch problem details using the official API."""
        try:
            url = f"{self.config.api_url}/problem/details"
            params = {'problem_alias': problem_alias}

            response = self.session.get(url, params=params, timeout=(10, 30))

            if response.status_code != 200:
                logging.getLogger(__name__).error(
                    "[%s] HTTP %d when fetching details",
                    problem_alias, response.status_code
                )
                return None

            result = response.json()
            if result.get("status") != "ok":
                logging.getLogger(__name__).error(
                    "[%s] API error: %s", problem_alias,
                    result.get('error', 'Unknown error')
                )
                return None

            logging.getLogger(__name__).info(
                "[%s] Problem details fetched: %s", problem_alias,
                result.get('title', 'Unknown')
            )
            return result  # type: ignore

        except requests.RequestException as e:
            logging.getLogger(__name__).error(
                "[%s] Request error fetching problem details: %s",
                problem_alias, str(e)
            )
            return None

    def check_admin_access(self, problem_alias: str) -> bool:
        """Check if we have admin access to this problem (can see runs)."""
        try:
            url = f"{self.config.api_url}/problem/runs"
            params = {
                'problem_alias': problem_alias,
                'show_all': 'true',
                'verdict': 'AC',
                'offset': 0,
                'rowcount': 1
            }

            response = self.session.get(
                url, params=params, timeout=(10, 30)  # type: ignore
            )

            if response.status_code == 200:
                result = response.json()
                if result.get("status") == "ok":
                    logging.getLogger(__name__).info(
                        "[%s] ✓ Admin access confirmed", problem_alias
                    )
                    return True

            logging.getLogger(__name__).info(
                "[%s] ✗ No admin access", problem_alias
            )
            return False

        except requests.RequestException as e:
            logging.getLogger(__name__).warning(
                "[%s] Request error checking admin access: %s",
                problem_alias, str(e)
            )
            return False
