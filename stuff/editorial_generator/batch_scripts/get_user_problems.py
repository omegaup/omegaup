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
    - .env file with OMEGAUP_AUTH_TOKEN or
      OMEGAUP_USERNAME/OMEGAUP_PASSWORD in parent directory
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
from typing import List

from dotenv import load_dotenv  # type: ignore[import]

# Add parent directory to path for omegaup_api_client
sys.path.insert(0, str(Path(__file__).parent.parent.parent))

# Import the shared API client
from omegaup_api_client import OmegaUpAPIClient  # type: ignore[import]


class UserProblemsExtractor:
    """Extracts all problems where the current user has admin access."""

    def __init__(self) -> None:
        """Initialize the extractor with authentication and configuration."""
        # Set up logging first
        self.logger = self._setup_logging()

        # Load environment variables from parent directory
        env_path = Path(__file__).parent.parent / ".env"
        load_dotenv(env_path)

        # Prefer auth token, fallback to username/password
        auth_token = os.getenv("OMEGAUP_AUTH_TOKEN")
        username = os.getenv("OMEGAUP_USERNAME")
        password = os.getenv("OMEGAUP_PASSWORD")
        base_url = os.getenv("OMEGAUP_BASE_URL", "https://omegaup.com")

        if auth_token:
            self.api_client = OmegaUpAPIClient(
                auth_token=auth_token,
                base_url=base_url,
                user_agent='omegaUp-UserProblems-Extractor/2.0'
            )
        elif username and password:
            self.api_client = OmegaUpAPIClient(
                credentials=(username, password),
                base_url=base_url,
                user_agent='omegaUp-UserProblems-Extractor/2.0'
            )
        else:
            raise ValueError(
                "OMEGAUP_AUTH_TOKEN or OMEGAUP_USERNAME/OMEGAUP_PASSWORD "
                "must be set in .env file"
            )

        # Statistics tracking
        self.stats = {
            'api_calls_made': 0,
            'problems_checked': 0,
            'admin_problems_found': 0,
            'api_errors': 0,
            'start_time': time.time()
        }

        self.logger.info("✅ Successfully initialized with shared API client")

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

    def get_user_created_problems(self) -> List[str]:
        """Get problems created by the current user."""
        self.logger.info("🔍 Searching for user-created problems...")

        try:
            # Use the shared API client method
            response = self.api_client.get_user_problems(
                page=1,
                rowcount=1000  # Get a large number of problems
            )
            self.stats['api_calls_made'] += 1

            # Extract problems from API response
            problems = response.get("problems", [])
            problem_aliases = [p.get("alias")
                               for p in problems if p.get("alias")]

            self.logger.info(
                "✅ Found %d user-created problems", len(problem_aliases)
            )
            for alias in problem_aliases:
                self.logger.info("   📝 %s", alias)

            return problem_aliases

        except Exception as e:  # pylint: disable=broad-except
            self.logger.warning(
                "⚠️  Could not fetch user-created problems: %s", str(e)
            )
            self.stats['api_errors'] += 1
            return []

    def extract_all_problems(self) -> List[str]:
        """Extract all public problems where the user has admin access."""
        self.logger.info("🚀 Starting public problem extraction...")

        # Get user-created problems and filter for public ones only
        self.logger.info("🔍 Searching for user-created public problems...")

        created_problems = self.get_user_created_problems()

        # Filter for public problems only
        public_problems = []
        for alias in created_problems:
            try:
                # Check if problem is public via problem details
                problem_details = self.api_client.get_problem_details(alias)
                visibility = problem_details.get('visibility', 0)
                if visibility >= 1:  # 1 = public
                    public_problems.append(alias)
                    self.logger.info("   ✅ Public problem: %s", alias)
                else:
                    self.logger.info(
                        "   ⏭️  Skipping private problem: %s", alias)
                self.stats['api_calls_made'] += 1
            except Exception as e:  # pylint: disable=broad-except
                self.logger.debug(
                    "Could not check visibility for %s: %s", alias, e)

        self.stats['admin_problems_found'] = len(public_problems)

        # Get statistical information
        self._log_problem_statistics(public_problems)

        self.logger.info("\n%s", "=" * 60)
        self.logger.info("EXTRACTION COMPLETE")
        self.logger.info("%s", "=" * 60)
        self.logger.info(
            "📊 Total public problems found: %d", len(public_problems)
        )

        return sorted(public_problems)

    def _log_problem_statistics(self, problems_data: List[str]) -> None:
        """Log statistical information about the problems found."""
        if not problems_data:
            return

        # Get full problem data for statistics
        try:
            # Get the full problem objects for statistics
            response = self.api_client.get_admin_problems(
                page=1,
                page_size=min(len(problems_data), 100)
            )
            problems = response.get("problems", [])

            if problems:
                stats = self.api_client.extract_problem_statistics(problems)
                self.stats['api_calls_made'] += 1

                self.logger.info("\n📊 PROBLEM STATISTICS:")
                self.logger.info(
                    "   Total problems: %d", stats['total_problems']
                )
                self.logger.info(
                    "   Public problems: %d", stats['public_problems']
                )
                self.logger.info(
                    "   Private problems: %d", stats['private_problems']
                )

                if stats['visibility_breakdown']:
                    self.logger.info("   Visibility breakdown:")
                    for visibility, count in (
                            stats['visibility_breakdown'].items()):
                        self.logger.info(
                            "     %s: %d", visibility, count
                        )

        except Exception as e:  # pylint: disable=broad-except
            self.logger.debug("Could not get detailed statistics: %s", str(e))

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
                f.write(
                    "# Public problems where current user has admin access\n")
                f.write(f"# Generated on {datetime.now().isoformat()}\n")
                f.write(f"# Total public problems: {len(problems)}\n")
                f.write("#\n")
                f.write("# One problem alias per line\n")
                f.write("# Lines starting with # are comments\n")
                f.write(
                    "# Note: Only public problems are included "
                    "(private problems excluded)\n")
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
                "⚠️  No public problems found with admin access"
            )
            extractor.logger.info(
                "💡 Make sure you have created public problems or have admin "
                "access to existing public ones"
            )
            return 1

        # Save to file
        extractor.save_problems_to_file(problems)

        # Print statistics
        extractor.print_final_statistics()

        print("\n🎉 SUCCESS! Public problem extraction completed.")
        print(
            f"📝 Found {len(problems)} public problems saved to "
            "my_problems.txt")
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
