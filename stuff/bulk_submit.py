#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Improved script for sending submissions using existing system.

Replaces Selenium script with more efficient version.
"""

import sys
import time
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple

import requests

# Import our existing clients
# Add the directory containing this script to sys.path for importing
# token_utils
script_dir = Path(__file__).parent
sys.path.insert(0, str(script_dir))
from token_utils import get_token, parse_token_from_args, parse_url_from_args


class BulkSubmissionRunner:
    """Bulk submission runner for omegaUp."""

    def __init__(self, token: Optional[str] = None,
                 base_url: Optional[str] = None) -> None:
        self.client = None
        self.solution_code: Optional[str] = None
        self.token = get_token(token)
        self.base_url = base_url or 'http://localhost:8001'
        if self.token and len(self.token) > 20:
            print(f"✅ Token configured: {self.token[:20]}...")
        print(f"🌐 Using server: {self.base_url}")

    def load_solution(self, solution_file: str) -> bool:
        """Load solution code."""
        solution_path = Path(solution_file)
        if not solution_path.exists():
            print(f"❌ File not found: {solution_file}")
            return False

        self.solution_code = solution_path.read_text(encoding='utf-8')
        print(f"📄 Solution loaded: {solution_file}")
        return True

    def load_aliases_from_file(self, aliases_file: str) -> List[str]:
        """Load aliases from text file"""
        aliases_path = Path(aliases_file)
        if not aliases_path.exists():
            print(f"❌ Aliases file not found: {aliases_file}")
            return []

        aliases = []
        with open(aliases_path, 'r', encoding='utf-8') as f:
            for line in f:
                alias = line.strip()
                if alias and not alias.startswith('#'):
                    aliases.append(alias)

        print(f"✅ Loaded {len(aliases)} aliases")
        return aliases

    def submit_to_problem(self, problem_name: str,
                          language: str = 'cpp11-gcc') -> tuple[bool, str]:
        """Submit solution to a specific problem."""
        try:
            print(f"\n🚀 Submitting to: {problem_name}")

            # Hacer submission usando requests directamente con header
            submission_url = f"{self.base_url}/api/run/create/"

            headers = {
                'Authorization': f'token {self.token}',
                'Content-Type': 'application/x-www-form-urlencoded'
            }

            data = {
                'problem_alias': problem_name,
                'source': self.solution_code,
                'language': language
            }

            response = requests.post(submission_url, headers=headers,
                                     data=data, timeout=30)
            if response.status_code == 200:
                response_json = response.json()
                if response_json['status'] == 'ok':
                    guid = response_json['guid']
                    print(f"✅ Successful submission: {guid}")
                    return True, guid
                print(f"❌ Response error: {response_json}")
                return False, ""
            print(f"❌ HTTP error {response.status_code}: {response.text}")
            return False, ""

        except (requests.RequestException, ValueError, KeyError) as e:
            print(f"❌ Error submitting to {problem_name}: {e}")
            return False, ""

    def wait_for_verdict(self, guid: str, max_wait: int = 60) -> (
            Optional[Dict[str, Any]]):
        """Wait for submission verdict."""
        start_time = time.time()

        while time.time() - start_time < max_wait:
            try:
                details_url = f"{self.base_url}/api/run/status/"
                headers = {'Authorization': f'token {self.token}'}
                params = {'run_alias': guid}

                response = requests.get(details_url, headers=headers,
                                        params=params, timeout=10)

                if response.status_code == 200:
                    result: Dict[str, Any] = response.json()

                    # Check if finished
                    if result.get('status') == 'ready':
                        return result

                time.sleep(2)  # Wait 2 seconds before next attempt

            except requests.RequestException as e:
                print(f"      Error checking verdict: {e}")
                break

        return None

    def run_bulk_submissions(self, aliases_file: str, solution_file: str,
                             wait_between: int = 3) -> bool:
        """Execute bulk submissions"""
        print("🎯 Starting bulk submissions")
        print("=" * 50)

        # Token was already configured in __init__
        if not self.token:
            print("❌ Could not obtain valid token")
            return False

        if not self.load_solution(solution_file):
            return False

        aliases = self.load_aliases_from_file(aliases_file)
        if not aliases:
            return False

        print(f"\n📋 Processing {len(aliases)} problems with "
              f"{wait_between}s wait between each one")

        # Confirm before proceeding
        if len(aliases) > 10:
            response = input(f"\n⚠️  Submit to {len(aliases)} "
                             f"problems? (y/n): ")
            if response.lower() != 'y':
                print("❌ Cancelled by user")
                return False

        return self._process_submissions(aliases, wait_between)

    def _process_submissions(self, aliases: List[str],
                             wait_between: int) -> bool:
        """Process submissions individually"""
        success_count = 0
        failed_count = 0

        for i, alias in enumerate(aliases, 1):
            print(f"\n[{i}/{len(aliases)}]", end="")

            if self.submit_to_problem(alias):
                success_count += 1
            else:
                failed_count += 1

            # Wait between submissions to avoid server overload
            if i < len(aliases):
                print(f"   ⏳ Waiting {wait_between}s...")
                time.sleep(wait_between)

        # Final summary
        print("\n📊 Summary:")
        print(f"   ✅ Successful: {success_count}")
        print(f"   ❌ Failed: {failed_count}")
        total = success_count + failed_count
        if total > 0:
            print(f"   📈 Success rate: {success_count/total*100:.1f}%")

        return success_count > 0


def show_help() -> None:
    """Mostrar ayuda del script."""
    print("Uso:")
    script_name = sys.argv[0]
    print(f"  {script_name} <aliases_file> <solution_file> "
          f"[tiempo_espera] [--token TOKEN] [--url URL]")
    print()
    print("Examples:")
    print(f"  {script_name} aliases.txt solution.py")
    print(f"  {script_name} aliases.txt solution.py 5")
    print(f"  {script_name} aliases.txt solution.py --token abc123def456")
    print(f"  {script_name} aliases.txt solution.py "
          f"--url https://omegaup.com")
    print(f"  {script_name} aliases.txt solution.py 5 -t abc123def456 "
          f"--url https://omegaup.com")
    print()
    print("aliases.txt format:")
    print("  SumasJP")
    print("  A-PLUS-B")
    print("  HELLO-WORLD")
    print("  # Comments start with #")
    print()
    print("Options:")
    print("  --token, -t TOKEN  omegaUp API token")
    print("  --url, -u URL      Base URL (default: http://localhost:8001)")
    print("                     Use https://omegaup.com for production")
    print()
    print("Token:")
    print("  • Can be provided with --token or -t")
    print("  • If not provided, searches in .token file")
    print("  • If .token doesn't exist, prompts for input")


def parse_main_args() -> Tuple[str, str, int]:
    """Parse main script arguments."""
    # Filter token and URL arguments to get normal arguments
    filtered_args = []
    skip_next = False

    for arg in sys.argv[1:]:
        if skip_next:
            skip_next = False
            continue
        if arg in ['--token', '-t', '--url', '-u']:
            skip_next = True
            continue
        filtered_args.append(arg)

    if len(filtered_args) < 2:
        print("❌ Argumentos insuficientes. Usa --help para ver el uso.")
        sys.exit(1)

    aliases_file = filtered_args[0]
    solution_file = filtered_args[1]
    wait_time = int(filtered_args[2]) if len(filtered_args) > 2 else 3

    return aliases_file, solution_file, wait_time


def main() -> None:
    """Main function that handles arguments and executes submissions."""
    # Parsear argumentos especiales para token y URL
    if '--help' in sys.argv or '-h' in sys.argv:
        show_help()
        sys.exit(0)

    aliases_file, solution_file, wait_time = parse_main_args()

    # Obtener token y URL desde argumentos
    provided_token = parse_token_from_args()
    provided_url = parse_url_from_args()

    runner = BulkSubmissionRunner(provided_token, provided_url)
    success = runner.run_bulk_submissions(aliases_file, solution_file,
                                          wait_time)

    if success:
        print("\n🎉 Process completed!")
    else:
        print("\n💥 Process finished with errors")
        sys.exit(1)


if __name__ == "__main__":
    main()
