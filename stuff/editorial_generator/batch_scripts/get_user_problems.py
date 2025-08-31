#!/usr/bin/env python3
"""
get_user_problems.py - Extract all problems owned/created by the current user

This script authenticates with omegaUp and finds all problems where the current
user has admin access (i.e., problems they created or have admin rights to).
It saves the problem aliases to a text file for use with batch processing.

Usage:
    cd stuff/editorial_generator/batch_scripts/
    python get_user_problems.py

Prerequisites:
    - .env file with OMEGAUP_USERNAME and OMEGAUP_PASSWORD in parent directory
    - Active internet connection to omegaUp

Outputs:
    - my_problems.txt: List of problem aliases (one per line)
    - logs/get_problems_YYYYMMDD_HHMMSS.log: Detailed execution log
"""

import sys
import os
import logging
import time
from datetime import datetime
from pathlib import Path
from typing import List, Dict, Any, Optional, cast

import requests
from dotenv import load_dotenv  # type: ignore[import]

# Add parent directory to path for .env file
sys.path.insert(0, str(Path(__file__).parent.parent))


class UserProblemsExtractor:
    """Extracts all problems where the current user has admin access."""

    def __init__(self) -> None:
        """Initialize the extractor with authentication and configuration."""
        # Set up logging first
        self.logger = self._setup_logging()

        # Load environment variables from parent directory
        env_path = Path(__file__).parent.parent / ".env"
        load_dotenv(env_path)

        # API Configuration
        self.api_url = os.getenv("OMEGAUP_API_URL", "https://omegaup.com/api")
        self.base_url = os.getenv("OMEGAUP_BASE_URL", "https://omegaup.com")

        # Authentication credentials
        self.username = os.getenv("OMEGAUP_USERNAME")
        self.password = os.getenv("OMEGAUP_PASSWORD")

        if not self.username or not self.password:
            raise ValueError(
                "OMEGAUP_USERNAME and OMEGAUP_PASSWORD must be set in "
                ".env file"
            )

        # Initialize session for persistent connections and cookies
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'omegaUp-UserProblems-Extractor/1.0',
            'Accept': 'application/json',
            'Accept-Language': 'en-US,en;q=0.9,es;q=0.8,pt;q=0.7'
        })

        # Statistics tracking
        self.stats = {
            'api_calls_made': 0,
            'problems_checked': 0,
            'admin_problems_found': 0,
            'api_errors': 0,
            'start_time': time.time()
        }

        # Login for authenticated access
        self._login()

    def _setup_logging(self) -> logging.Logger:
        """Setup comprehensive logging configuration."""
        log_dir = Path(__file__).parent / "logs"
        log_dir.mkdir(exist_ok=True)

        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        log_file = log_dir / f"get_problems_{timestamp}.log"

        # Configure logging with both file and console output
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler(log_file, encoding='utf-8'),
                logging.StreamHandler()
            ]
        )

        logger = logging.getLogger(__name__)
        logger.info("=" * 80)
        logger.info("🚀 USER PROBLEMS EXTRACTION STARTED")
        logger.info("=" * 80)
        logger.info("📝 Log file: %s", log_file)

        return logger

    def _login(self) -> None:
        """Login using the official API with username and password."""
        try:
            self.logger.info("🔐 Authenticating as %s...", self.username)

            # Login endpoint
            login_url = f"{self.api_url}/user/login"
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

            self.stats['api_calls_made'] += 1

            if response.status_code == 200:
                result = response.json()
                if result.get("status") == "ok":
                    self.logger.info(
                        "✅ Successfully authenticated with omegaUp"
                    )
                    self.logger.info("   User: %s", self.username)
                    self.logger.info("   API URL: %s", self.api_url)
                else:
                    error_msg = result.get('error', 'Unknown error')
                    raise Exception(f"Login failed: {error_msg}")
            else:
                raise Exception(
                    f"HTTP {response.status_code}: Login request failed"
                )

        except Exception as e:
            self.logger.error("❌ Authentication failed: %s", str(e))
            raise

    def _make_api_call(self,
                       endpoint: str,
                       params: Optional[Dict[str, Any]] = None,
                       method: str = 'GET') -> Dict[str, Any]:
        """Make an API call with error handling and logging."""
        try:
            url = f"{self.api_url}/{endpoint}"
            self.stats['api_calls_made'] += 1

            self.logger.debug("🌐 API Call: %s %s", method, endpoint)
            if params:
                self.logger.debug("   Parameters: %s", params)

            if method == 'GET':
                response = self.session.get(
                    url, params=params, timeout=(10, 30))
            else:
                headers = {'Content-Type': 'application/x-www-form-urlencoded'}
                response = self.session.post(
                    url, data=params, headers=headers, timeout=(10, 30))

            if response.status_code != 200:
                self.logger.warning(
                    "⚠️  HTTP %d for %s", response.status_code, endpoint
                )
                self.stats['api_errors'] += 1
                return {
                    'status': 'error',
                    'error': f'HTTP {response.status_code}'
                }

            result = response.json()

            if result.get("status") != "ok":
                self.logger.warning(
                    "⚠️  API error for %s: %s",
                    endpoint, result.get('error', 'Unknown')
                )
                self.stats['api_errors'] += 1

            return cast(Dict[str, Any], result)

        except Exception as e:  # pylint: disable=broad-except
            self.logger.error(
                "❌ API call failed for %s: %s", endpoint, str(e)
            )
            self.stats['api_errors'] += 1
            return {'status': 'error', 'error': str(e)}

    def get_user_created_problems(self) -> List[str]:
        """Get problems created by the current user."""
        self.logger.info("🔍 Searching for user-created problems...")

        # Use the correct API endpoint for user's own problems
        result = self._make_api_call("problem/myList", {
            'page': 1,
            'rowcount': 1000  # Get a large number of problems
        })

        if result.get("status") == "ok":
            problems = result.get("problems", [])
            problem_aliases = [p.get("alias")
                               for p in problems if p.get("alias")]

            self.logger.info(
                "✅ Found %d user-created problems", len(problem_aliases)
            )
            for alias in problem_aliases:
                self.logger.info("   📝 %s", alias)

            return problem_aliases

        error_msg = result.get('error', 'Unknown error')
        self.logger.warning(
            "⚠️  Could not fetch user-created problems: %s", error_msg
        )
        return []

    def get_admin_problems(self) -> List[str]:
        """Get problems where user has admin access using adminList API."""
        self.logger.info("🔍 Searching for admin-accessible problems...")

        admin_problems = []
        page = 1
        page_size = 100

        while True:
            # Use the correct adminList endpoint
            result = self._make_api_call("problem/adminList", {
                'page': page,
                'page_size': page_size,
                'query': ''  # Empty query to get all problems
            })

            if result.get("status") != "ok":
                error_msg = result.get('error', 'Unknown error')
                self.logger.warning(
                    "⚠️  Admin problem search failed on page %d: %s",
                    page, error_msg
                )
                break

            problems = result.get("problems", [])
            if not problems:
                self.logger.info(
                    "📄 No more problems found at page %d", page
                )
                break

            self.logger.info(
                "📄 Found page %d: %d admin problems", page, len(problems)
            )

            # Add all problems from this page
            for problem in problems:
                alias = problem.get("alias")
                if alias:
                    admin_problems.append(alias)
                    self.logger.info("   ✅ Admin problem: %s", alias)
                    self.stats['problems_checked'] += 1

            # Check if we should continue
            if len(problems) < page_size:
                self.logger.info("📄 Reached end of admin problem list")
                break

            page += 1

            # Rate limiting - don't overwhelm the API
            time.sleep(0.5)

        self.logger.info(
            "🎯 Found %d problems with admin access", len(admin_problems)
        )
        return admin_problems

    def extract_all_problems(self) -> List[str]:
        """Extract all problems where the user has admin access."""
        self.logger.info("🚀 Starting comprehensive problem extraction...")

        all_problems = set()

        # Method 1: Get user-created problems
        self.logger.info("\n%s", "=" * 60)
        self.logger.info("METHOD 1: User-created problems (myList)")
        self.logger.info("%s", "=" * 60)

        created_problems = self.get_user_created_problems()
        all_problems.update(created_problems)
        self.stats['admin_problems_found'] += len(created_problems)

        # Method 2: Get admin-accessible problems
        self.logger.info("\n%s", "=" * 60)
        self.logger.info("METHOD 2: Admin-accessible problems (adminList)")
        self.logger.info("%s", "=" * 60)

        admin_problems = self.get_admin_problems()

        # Add new admin problems that aren't already in our list
        new_admin_problems = 0
        for alias in admin_problems:
            if alias not in all_problems:
                all_problems.add(alias)
                new_admin_problems += 1
                self.logger.info("   ✅ Additional admin problem: %s", alias)

        self.stats['admin_problems_found'] += new_admin_problems

        final_list = sorted(list(all_problems))

        self.logger.info("\n%s", "=" * 60)
        self.logger.info("EXTRACTION COMPLETE")
        self.logger.info("%s", "=" * 60)
        self.logger.info(
            "📊 User-created problems: %d", len(created_problems)
        )
        self.logger.info(
            "📊 Additional admin problems: %d", new_admin_problems
        )
        self.logger.info(
            "📊 Total problems with admin access: %d", len(final_list)
        )

        return final_list

    def save_problems_to_file(
        self,
        problems: List[str],
        filename: str = "my_problems.txt") -> None:
        """Save problem list to text file."""
        try:
            output_path = Path(__file__).parent / filename

            self.logger.info(
                "💾 Saving %d problems to %s", len(problems), output_path
            )

            with open(output_path, 'w', encoding='utf-8') as f:
                f.write("# Problems where current user has admin access\n")
                f.write(f"# Generated on {datetime.now().isoformat()}\n")
                f.write(f"# User: {self.username}\n")
                f.write(f"# Total problems: {len(problems)}\n")
                f.write("#\n")
                f.write("# One problem alias per line\n")
                f.write("# Lines starting with # are comments\n")
                f.write("\n")

                for problem in problems:
                    f.write(f"{problem}\n")

            self.logger.info("✅ Successfully saved problems to %s", filename)
            self.logger.info("   File location: %s", output_path.absolute())

        except Exception as e:
            self.logger.error(
                "❌ Failed to save problems to file: %s", str(e)
            )
            raise

    def print_final_statistics(self) -> None:
        """Print comprehensive final statistics."""
        duration = time.time() - self.stats['start_time']

        self.logger.info("\n%s", "=" * 80)
        self.logger.info("📊 FINAL STATISTICS")
        self.logger.info("%s", "=" * 80)
        self.logger.info("⏱️  Total Runtime: %.1f seconds", duration)
        self.logger.info(
            "🌐 API Calls Made: %d", self.stats['api_calls_made']
        )
        self.logger.info(
            "🔍 Problems Checked: %d", self.stats['problems_checked']
        )
        self.logger.info(
            "✅ Admin Problems Found: %d", self.stats['admin_problems_found']
        )
        self.logger.info("❌ API Errors: %d", self.stats['api_errors'])

        if self.stats['api_calls_made'] > 0:
            success_rate = (
                (self.stats['api_calls_made'] -
                 self.stats['api_errors']) /
                self.stats['api_calls_made'] *
                100)
            self.logger.info("📈 API Success Rate: %.1f%%", success_rate)

        self.logger.info("%s", "=" * 80)
        self.logger.info("🎯 USER PROBLEMS EXTRACTION COMPLETED")
        self.logger.info("%s", "=" * 80)


def main() -> int:
    """Main entry point for user problems extraction."""
    try:
        # Initialize extractor
        extractor = UserProblemsExtractor()

        # Extract all problems
        problems = extractor.extract_all_problems()

        if not problems:
            extractor.logger.warning(
                "⚠️  No problems found with admin access"
            )
            extractor.logger.info(
                "💡 Make sure you have created problems or have admin "
                "access to existing ones"
            )
            return 1

        # Save to file
        extractor.save_problems_to_file(problems)

        # Print statistics
        extractor.print_final_statistics()

        print("\n🎉 SUCCESS! Problem extraction completed.")
        print(f"📝 Found {len(problems)} problems saved to my_problems.txt")
        print("🚀 Ready to run: python batch_generate_editorials.py")

        return 0

    except KeyboardInterrupt:
        print("\n⏹️  Interrupted by user")
        return 0
    except Exception as e:  # pylint: disable=broad-except
        print(f"💥 Unexpected error: {str(e)}")
        return 1


if __name__ == "__main__":
    sys.exit(main())
