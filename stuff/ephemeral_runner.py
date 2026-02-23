#!/usr/bin/env python3
"""
Ephemeral Runner - Updated and working correctly
"""

import json
import sys
import time
from pathlib import Path
from typing import Any, Dict, List, Optional, cast

import requests

from token_utils import parse_url_from_args


def parse_demo_flag() -> bool:
    """Parse --demo flag from arguments."""
    return '--demo' in sys.argv


class EphemeralRunner:
    """Runner for executing code in ephemeral mode without DB traces"""
    def __init__(self, base_url: Optional[str] = None) -> None:
        self.base_url = base_url or 'http://localhost:8001'
        print(f"🌐 Using server: {self.base_url}")
        print("🔓 Ephemeral mode: No authentication required")

    def get_aliases_file(self) -> str:
        """Determine which aliases file to use based on URL"""
        script_dir = Path(__file__).parent
        if 'omegaup.com' in self.base_url:
            return str(script_dir / 'prod_test_aliases.txt')
        return str(script_dir / 'aliases.txt')

    def load_solution(self, problem_alias: str) -> Optional[str]:
        """Load solution from file"""
        solution_path = Path(f'solutions/{problem_alias}.py')

        if not solution_path.exists():
            print(f"❌ Solution not found: {solution_path}")
            return None

        solution_code = solution_path.read_text(encoding='utf-8')
        print(f"✅ Solution loaded: {len(solution_code)} characters")
        return solution_code

    def get_problem_details(
        self,
        problem_alias: str) -> Optional[Dict[str, Any]]:
        """Get problem details from API"""
        try:
            session = requests.Session()
            # Don't send token/cookies for public queries
            url = f"{self.base_url}/api/problem/details/"
            response = session.get(url,
                                   params={'problem_alias': problem_alias},
                                   timeout=10)

            if response.status_code == 200:
                # Show short hash and case count to verify changes
                try:
                    data = cast(Dict[str, Any], response.json())
                except (ValueError, KeyError, TypeError):
                    data = {}
                    data = None

                return data
            return None
        except (requests.RequestException, ConnectionError):
            return None

    def show_problem_details(self, problem_alias: str) -> None:
        """Show detailed problem information including examples"""
        print(f"\n📋 Problem Details: {problem_alias}")
        print("=" * 50)

        details = self.get_problem_details(problem_alias)
        if not details:
            print("❌ Could not fetch problem details")
            return

        # Problem basic info
        problem = details.get('problem', {})
        print(f"Title: {problem.get('title', 'N/A')}")
        print(f"Points: {problem.get('points', 'N/A')}")
        print(f"Memory Limit: {problem.get('memory_limit', 'N/A')} bytes")
        print(f"Time Limit: {problem.get('time_limit', 'N/A')} ms")

        # Examples
        examples = details.get('examples', [])
        if examples:
            print(f"\n📝 Examples ({len(examples)} found):")
            for i, example in enumerate(examples, 1):
                print(f"\n  Example {i}:")
                print(f"    Input:  {repr(example.get('input', ''))}")
                print(f"    Output: {repr(example.get('output', ''))}")
        else:
            print("\n⚠️  No examples found")

        # Settings info
        settings = details.get('settings', {})
        if settings:
            print("\n⚙️  Settings:")
            cases = settings.get('cases', {})
            limits = settings.get('limits', {})
            validator = settings.get('validator', {})

            print(f"  Cases: {len(cases)} found")
            if limits:
                print(f"  Time Limit: {limits.get('TimeLimit', 'N/A')}")
                print(f"  Memory Limit: {limits.get('MemoryLimit', 'N/A')}")
            if validator:
                print(f"  Validator: {validator.get('name', 'N/A')}")

        print("=" * 50)

    def _extract_json_from_multipart(
            self, response_text: str) -> Optional[str]:
        """Extract JSON content from multipart response."""
        lines = response_text.split('\n')
        json_content = ""
        collecting = False
        brace_count = 0

        for i in range(len(lines) - 1, -1, -1):
            line = lines[i]

            if line.startswith('--') and len(line) > 10:
                continue

            if collecting:
                json_content = line + '\n' + json_content
                brace_count += line.count('}') - line.count('{')

                if brace_count == 0 and line.strip().startswith('{'):
                    break

            if '}' in line and not collecting:
                collecting = True
                json_content = line + '\n'
                brace_count = line.count('}') - line.count('{')

        return json_content.strip() if json_content else None

    def _extract_verdict_from_data(
            self, data: Dict[str, Any]) -> Dict[str, Any]:
        """Extract verdict information from judge data."""
        verdict = 'Unknown'
        score = 0.0
        time_used = 'N/A'
        memory_used = 'N/A'

        if 'groups' in data and data['groups']:
            group = data['groups'][0]
            if 'cases' in group and group['cases']:
                case = group['cases'][0]
                if 'verdict' in case:
                    verdict = case['verdict']
                    score = case.get('score', 0.0)

                    if 'meta' in case:
                        meta = case['meta']
                        if 'time' in meta and meta['time']:
                            time_used = f"{meta['time']*1000:.1f}ms"
                        if 'memory' in meta and meta['memory']:
                            memory_used = f"{meta['memory']/1024:.1f}KB"

        return {
            'verdict': verdict,
            'score': score,
            'time': time_used,
            'memory': memory_used,
            'groups': data.get('groups', [])
        }

    def _parse_status_updates(self, response_text: str) -> Dict[str, Any]:
        """Parse status updates from response text."""
        if 'running' in response_text:
            return {'verdict': 'Running/Completed'}
        if 'queueing' in response_text:
            return {'verdict': 'Queued'}
        if 'waiting' in response_text:
            return {'verdict': 'Waiting'}
        return {'verdict': 'Unknown'}

    def _parse_judge_response(self, response_text: str) -> (
            Optional[Dict[str, Any]]):
        """Parse multipart judge response to extract verdict."""
        json_content = self._extract_json_from_multipart(response_text)

        if json_content:
            try:
                data = json.loads(json_content)
                result = self._extract_verdict_from_data(data)
                result['raw_response'] = (response_text[:300] + '...'
                                          if len(response_text) > 300
                                          else response_text)
                return result
            except json.JSONDecodeError:
                pass

        return self._parse_status_updates(response_text)

    def _build_request_data(self, problem_settings: Optional[Dict[str, Any]],
                            source_code: str, language: str) -> Dict[str, Any]:
        """Build request data using problem details"""
        # Build cases
        if problem_settings and 'cases' in problem_settings:
            cases = problem_settings['cases']
            sample_case = list(cases.values())[0] if cases else {}
            sample_input = sample_case.get('in', "1 2\n")
            sample_output = sample_case.get('out', "3\n")
            sample_weight = sample_case.get('weight', 1)
        else:
            sample_input = "1 2\n"
            sample_output = "3\n"
            sample_weight = 1

        # Get limits
        if problem_settings and 'limits' in problem_settings:
            limits = problem_settings['limits']
            time_limit = limits.get('TimeLimit', '1s')
            memory_limit = limits.get('MemoryLimit', 33554432)
            output_limit = limits.get('OutputLimit', 10240)
            overall_wall_time = limits.get('OverallWallTimeLimit', '1s')
            extra_wall_time = limits.get('ExtraWallTime', '0s')
        else:
            time_limit = '1s'
            memory_limit = 33554432
            output_limit = 10240
            overall_wall_time = '1s'
            extra_wall_time = '0s'

        # Get validator
        if problem_settings and 'validator' in problem_settings:
            validator = problem_settings['validator']
        else:
            validator = {'name': 'token-caseless'}

        return {
            "input": {
                "cases": {
                    "sample": {
                        "in": sample_input,
                        "out": sample_output,
                        "weight": sample_weight
                    }
                },
                "limits": {
                    "ExtraWallTime": extra_wall_time,
                    "MemoryLimit": memory_limit,
                    "OutputLimit": output_limit,
                    "OverallWallTimeLimit": overall_wall_time,
                    "TimeLimit": time_limit
                },
                "validator": validator
            },
            "language": language,
            "source": source_code
        }

    def run_ephemeral(self, problem_alias: str,
                      source_code: Optional[str] = None,
                      language: str = 'py3') -> Dict[str, Any]:
        """Execute code using ephemeral endpoint (no DB traces)"""

        if source_code is None:
            source_code = self.load_solution(problem_alias)
            if source_code is None:
                return {'success': False,
                        'error': 'Could not load solution'}

        # Get problem details from API
        problem_details = self.get_problem_details(problem_alias)

        if not problem_details:
            problem_settings = None
        else:
            problem_settings = problem_details.get('settings', {})

        try:
            # Configure session (don't send token/cookies)
            session = requests.Session()
            # Basic headers
            session.headers.update({
                'Content-Type': 'application/json',
                'User-Agent': 'omegaup-ephemeral-runner/1.0',
                'Origin': self.base_url,
                'Referer': f'{self.base_url}/grader/ephemeral/'
            })

            # Use helper method to build request data
            request_data = self._build_request_data(
                problem_settings, source_code, language)

            url = f"{self.base_url}/grader/ephemeral/run/new/"

            print(f"🚀 Running ephemeral: {problem_alias} ({language})", end="")

            # Send as JSON
            response = session.post(url, json=request_data, timeout=30)

            if response.status_code == 200:
                response_text = response.text
                ephemeral_token = response.headers.get(
                    'X-Omegaup-Ephemeraltoken', '')
                success = bool(ephemeral_token)

                # Parse judge response if available
                judge_result = self._parse_judge_response(response_text)

                # Show verdict immediately
                if judge_result:
                    verdict = judge_result['verdict']
                    score = judge_result['score']
                    print(f" → {verdict} (Score: {score})")
                else:
                    print(" → Executed")

                return {
                    'success': success,
                    'response': response_text,
                    'status_code': response.status_code,
                    'ephemeral_token': ephemeral_token,
                    'problem_alias': problem_alias,
                    'judge_result': judge_result
                }

            print(f" → Error HTTP {response.status_code}")
            return {
                'success': False,
                'error': f"HTTP {response.status_code}",
                'response': response.text
            }

        except (requests.RequestException, ConnectionError) as e:
            print(f" → Error: {e}")
            return {
                'success': False,
                'error': str(e)
            }

    def run_batch_ephemeral(self,
                            problems: List[Dict[str, Any]]
                            ) -> List[Dict[str, Any]]:
        """Execute multiple problems in ephemeral mode"""

        results = []

        for problem in problems:
            result = self.run_ephemeral(
                problem_alias=problem['alias'],
                source_code=problem.get('source'),
                language=problem.get('language', 'py3')
            )

            results.append({
                'problem': problem['alias'],
                'success': result['success'],
                'result': result
            })

            # Small pause between executions
            time.sleep(0.5)

        # Simplified summary
        successful = sum(1 for r in results if r['success'])
        print(f"\n📊 Summary: {successful}/{len(problems)} successful | "
              "No DB traces")

        return results


def main() -> None:
    """Main function - usage examples"""

    # Get URL and demo flag from arguments
    provided_url = parse_url_from_args()
    demo_mode = parse_demo_flag()
    runner = EphemeralRunner(provided_url)

    # Determine which aliases file to use
    # Constant for number of aliases to test
    max_test_aliases = 48

    aliases_file = runner.get_aliases_file()
    print(f"📁 Using aliases file: {aliases_file}")

    # Load some aliases from corresponding file
    try:
        with open(aliases_file, 'r', encoding='utf-8') as f:
            # Take first max_test_aliases
            aliases = [line.strip() for line in f.readlines()
                       if line.strip()][:max_test_aliases]

        if not aliases:
            print(f"⚠️  No aliases found in {aliases_file}, "
                  "using default values")
            aliases = ['sumas']  # Fallback

        if demo_mode:
            print(f"🎯 Demo mode: testing only '{aliases[0]}'")
        else:
            print(f"🎯 Aliases to test: {aliases}")

    except FileNotFoundError:
        print(f"⚠️  File {aliases_file} not found, "
              "using default alias")
        aliases = ['sumas']  # Fallback

    if demo_mode:
        # Demo mode: single individual test to demonstrate functionality
        first_alias = aliases[0]
        print(f"\n🧪 Individual ephemeral test (demo mode) with: {first_alias}")

        # First, show problem details
        runner.show_problem_details(first_alias)

        # Then run the test
        runner.run_ephemeral(
            problem_alias=first_alias,
            source_code=(
                '#include <iostream>\n'
                'using namespace std;\n\n'
                'int main()\n{\n'
                '   int a,b;\n'
                '   cin>>a>>b;\n'
                '   cout<<a+b<<endl;\n'
                '   return 0;\n}'
            ),
            language='cpp11-gcc'
        )

        # Result already shown inline during execution

        print("\n💡 Demo mode completed. Use without --demo to process "
              "all aliases.")
    else:
        # Normal mode: run all aliases in batch
        problems = []

        for alias in aliases:
            problems.append({
                'alias': alias,
                'source': (
                    '#include <iostream>\n'
                    'using namespace std;\n\n'
                    'int main()\n{\n'
                    '   int a,b;\n'
                    '   cin>>a>>b;\n'
                    '   cout<<a+b<<endl;\n'
                    '   return 0;\n}'
                ),
                'language': 'cpp11-gcc'
            })

        runner.run_batch_ephemeral(problems)


if __name__ == "__main__":
    main()
