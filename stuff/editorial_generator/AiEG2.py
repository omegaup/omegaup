#!/usr/bin/env python3
"""
AiEG2 - Two-Step Editorial + Solution Generator (File Storage Only)
Generates editorial first, then solution code based only on editorial and problem details.

PROGRAM FLOW:
1. **Input Loading**: Load problem aliases from text file
2. **Problem Details**: Fetch problem details from omegaUp API
3. **Admin Access Check**: Verify admin access to see existing AC solutions
4. **Reference AC Discovery**: Find oldest AC solution for reference (understanding only)
5. **Two-Step Generation Process**:
   - **Step A**: Generate editorial using problem details + reference AC (for algorithmic understanding)
   - **Step B**: Generate solution code using ONLY problem details + editorial (NOT reference AC)
6. **Solution Verification**: Submit generated solution to omegaUp grader for testing
7. **Retry Logic**: If verification fails, regenerate both editorial and solution with error feedback
8. **File Storage**: Save editorial, solution, reference AC, and metadata to local log files
9. **Statistics & Reporting**: Track success/failure rates and generate final report

KEY FEATURES:
-  Two-step independent generation (Editorial → Solution)
-  Solution generation is purely based on editorial, not reference AC code
-  Automatic retry with error feedback for failed solutions
-  60-second wait between retry submissions (omegaUp rate limit compliance)
-  No website updates - files saved locally only
-  Comprehensive logging and error tracking
-  Supports multiple programming languages for reference AC
-  Detailed verification process with grader feedback

OUTPUTS:
- Local files: editorial.md, solution.cpp, reference AC, details.json
- Detailed logs with processing results and code samples
- Statistics report with verification success rates
- No website updates (files only)
"""

import os
import sys
import json
import time
import logging
from datetime import datetime
from pathlib import Path
from typing import Dict, Any, Optional, List, Tuple
import requests
from openai import OpenAI
from dotenv import load_dotenv

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('logs/AiEG2.log', encoding='utf-8'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

class EditorialSolutionGeneratorV5:
    """Generates editorial first, then solution code based only on editorial and problem details."""

    def __init__(self):
        load_dotenv()
        
        # API Configuration
        self.api_url = os.getenv("OMEGAUP_API_URL", "https://omegaup.com/api")
        self.base_url = os.getenv("OMEGAUP_BASE_URL", "https://omegaup.com")
        
        # Authentication credentials
        self.username = os.getenv("OMEGAUP_USERNAME")
        self.password = os.getenv("OMEGAUP_PASSWORD")
        
        if not self.username or not self.password:
            raise ValueError("OMEGAUP_USERNAME and OMEGAUP_PASSWORD must be set in environment variables")
        
        # Initialize OpenAI
        api_key = os.getenv("OPENAI_API_KEY")
        if not api_key:
            raise ValueError("OPENAI_API_KEY must be set in environment variables")
        self.openai_client = OpenAI(api_key=api_key)
        
        # Initialize session for persistent connections
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'omegaUp-Editorial-Solution-Generator-V5/1.0',
            'Accept': 'application/json',
            'Accept-Language': 'en-US,en;q=0.9'
        })
        
        # Login for authenticated access
        self._login()
        
        # Statistics tracking
        self.stats = {
            'total_problems': 0,
            'problems_with_admin_access': 0,
            'problems_with_existing_ac': 0,
            'problems_no_existing_ac': 0,
            'editorial_generated': 0,
            'solution_generated': 0,
            'solution_verified_first_try': 0,
            'solution_verified_second_try': 0,
            'solution_verification_failed': 0,
            'problems_successful': 0,
            'problems_failed': 0,
            'karel_problems_skipped': 0,
            'successful_problems': [],
            'failed_problems': [],
            'karel_problems': [],
            'no_ac_problems': [],
            'verification_failed_problems': []
        }
        
        logger.info("Editorial + Solution Generator V5 initialized successfully")

    def _login(self) -> None:
        """Login using the official API."""
        try:
            login_url = f"{self.api_url}/user/login"
            login_data = {
                'usernameOrEmail': self.username,
                'password': self.password
            }
            
            headers = {'Content-Type': 'application/x-www-form-urlencoded'}
            response = self.session.post(login_url, data=login_data, headers=headers, timeout=(10, 30))
            
            if response.status_code == 200:
                logger.info("Successfully logged in to omegaUp")
            else:
                raise Exception(f"Login failed with status code: {response.status_code}")
            
        except Exception as e:
            logger.error(f"Failed to login: {str(e)}")
            raise

    def get_problem_details(self, problem_alias: str) -> Optional[Dict[str, Any]]:
        """Fetch problem details using the official API."""
        try:
            url = f"{self.api_url}/problem/details"
            params = {'problem_alias': problem_alias}
            
            response = self.session.get(url, params=params, timeout=(10, 30))
            
            if response.status_code != 200:
                logger.error(f"HTTP {response.status_code} when fetching problem details")
                return None
                
            result = response.json()
            if result.get("status") != "ok":
                logger.error(f"Error fetching problem details: {result.get('error', 'Unknown error')}")
                return None
            
            logger.info(f"[{problem_alias}] Problem details fetched: {result.get('title', 'Unknown')}")
            return result
            
        except Exception as e:
            logger.error(f"[{problem_alias}] Error fetching problem details: {str(e)}")
            return None

    def check_admin_access(self, problem_alias: str) -> bool:
        """Check if we have admin access to this problem (can see runs)."""
        try:
            url = f"{self.api_url}/problem/runs"
            params = {
                'problem_alias': problem_alias,
                'show_all': 'true',
                'verdict': 'AC',
                'offset': 0,
                'rowcount': 1
            }
            
            response = self.session.get(url, params=params, timeout=(10, 30))
            
            if response.status_code == 200:
                logger.info(f"[{problem_alias}] ✓ Admin access confirmed")
                return True
            
            logger.info(f"[{problem_alias}] ✗ No admin access")
            return False
            
        except Exception as e:
            logger.warning(f"[{problem_alias}] Error checking admin access: {str(e)}")
            return False

    def get_first_ac_run(self, problem_alias: str) -> Optional[Dict[str, Any]]:
        """Get the oldest AC run for the problem (first person to solve it)."""
        try:
            logger.info(f"[{problem_alias}] Searching for oldest AC solution...")
            
            # Get all AC runs, sorted by time (oldest first)
            url = f"{self.api_url}/problem/runs"
            params = {
                'problem_alias': problem_alias,
                'show_all': 'true',
                'verdict': 'AC',
                'offset': 0,
                'rowcount': 100
            }
            
            response = self.session.get(url, params=params, timeout=(10, 30))
            
            if response.status_code != 200:
                logger.error(f"HTTP {response.status_code} when fetching AC runs")
                return None
                
            result = response.json()
            if result.get("status") != "ok":
                logger.error(f"Error fetching AC runs: {result.get('error', 'Unknown error')}")
                return None
            
            runs = result.get("runs", [])
            if not runs:
                logger.warning(f"[{problem_alias}] No AC runs found")
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
                logger.info(f"[{problem_alias}] Found oldest AC run by {oldest_run.get('username', 'unknown')} "
                          f"in {oldest_run.get('language', 'unknown')} at {oldest_run.get('time', 'unknown time')}")
                return oldest_run
            else:
                # Fallback to first run if no timestamp comparison worked
                first_ac = runs[0]
                logger.info(f"[{problem_alias}] Using first AC run by {first_ac.get('username', 'unknown')} "
                          f"in {first_ac.get('language', 'unknown')} (fallback - no timestamps)")
                return first_ac
            
        except Exception as e:
            logger.warning(f"[{problem_alias}] Error fetching AC runs: {str(e)}")
            return None

    def get_run_source(self, run_guid: str) -> Optional[str]:
        """Get source code of a specific run."""
        try:
            url = f"{self.api_url}/run/source"
            params = {'run_alias': run_guid}
            
            response = self.session.get(url, params=params, timeout=(10, 30))
            
            if response.status_code != 200:
                logger.warning(f"HTTP {response.status_code} when fetching run source")
                return None
                
            result = response.json()
            if result.get("status") != "ok":
                logger.warning(f"Error fetching run source: {result.get('error', 'Unknown error')}")
                return None
            
            source_code = result.get('source', '')
            if source_code:
                logger.info(f"Successfully retrieved source code ({len(source_code)} chars)")
                return source_code
            else:
                logger.warning("Source code is empty")
                return None
            
        except Exception as e:
            logger.warning(f"Error fetching run source: {str(e)}")
            return None

    def generate_editorial(self, problem_details: Dict[str, Any], reference_ac: str, reference_language: str, error_feedback: str = None) -> Optional[str]:
        """Generate editorial based on problem details and reference AC solution."""
        try:
            problem_statement = problem_details.get('statement', {}).get('markdown', '')
            problem_title = problem_details.get('title', 'Unknown Problem')
            
            # Trim problem statement if too long
            max_statement_length = 5000
            if len(problem_statement) > max_statement_length:
                problem_statement = problem_statement[:max_statement_length]
            
            # Build prompt based on whether this is a retry or first attempt
            if error_feedback:
                prompt = f"""You are an expert competitive programming assistant. The previous editorial and solution failed with errors. You need to generate a corrected editorial.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

REFERENCE SOLUTION (Language: {reference_language}) - FOR UNDERSTANDING ONLY:
```{reference_language}
{reference_ac}
```

PREVIOUS ERROR FEEDBACK:
{error_feedback}

Please generate a comprehensive editorial (WITHOUT any code) that addresses the issues from the previous failure.

Requirements:
- Explain what the problem is asking for in simple terms
- Identify the key insights needed to solve this problem
- Describe the correct solution approach step by step with detailed algorithmic flow
- Explain the implementation strategy that avoids the previous errors
- Analyze the time & space complexity of the solution
- NO CODE in the editorial - only algorithmic explanation
- Clear, educational English that addresses the specific issues that caused the failure

Generate the editorial:"""
            else:
                prompt = f"""You are an expert competitive programming assistant. Generate a comprehensive editorial for this omegaUp problem.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

REFERENCE SOLUTION (Language: {reference_language}) - FOR UNDERSTANDING ONLY:
```{reference_language}
{reference_ac}
```

Please generate a comprehensive editorial (WITHOUT any code) explaining the approach:

Requirements:
- Explain what the problem is asking for in simple terms
- Identify the key insights needed to solve this problem
- Describe the solution approach step by step with detailed algorithmic flow
- Explain the implementation strategy and important considerations
- Analyze the time & space complexity of the solution
- NO CODE in the editorial - only algorithmic explanation
- Clear, educational English

Generate the editorial:"""
            
            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {"role": "system", "content": "You are an expert competitive programming assistant who generates clear, educational editorials without any code."},
                    {"role": "user", "content": prompt}
                ],
                max_tokens=2500,
                temperature=0.3
            )
            
            editorial = response.choices[0].message.content.strip()
            logger.info(f"Generated editorial ({len(editorial)} chars)")
            return editorial
            
        except Exception as e:
            logger.error(f"Failed to generate editorial: {str(e)}")
            return None

    def generate_solution_code(self, problem_details: Dict[str, Any], editorial: str, error_feedback: str = None) -> Optional[str]:
        """Generate solution code based ONLY on problem details and editorial (not AC solution)."""
        try:
            problem_statement = problem_details.get('statement', {}).get('markdown', '')
            problem_title = problem_details.get('title', 'Unknown Problem')
            
            # Trim problem statement if too long
            max_statement_length = 5000
            if len(problem_statement) > max_statement_length:
                problem_statement = problem_statement[:max_statement_length]
            
            # Build prompt based on whether this is a retry or first attempt
            if error_feedback:
                prompt = f"""You are an expert competitive programmer. The previous solution failed with errors. Generate a corrected C++ solution.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

EDITORIAL (implement exactly what this describes):
{editorial}

PREVIOUS ERROR FEEDBACK:
{error_feedback}

Generate a corrected C++ solution that:
- Implements EXACTLY what the editorial describes
- Fixes the specific issues from the previous failure
- Uses efficient, correct C++ code that will get AC (Accepted)
- Handles edge cases properly
- Uses proper C++ syntax and includes (#include <bits/stdc++.h>)

IMPORTANT: Base your solution ONLY on the problem statement and editorial above. Do NOT use any external reference solutions.

Provide only the complete C++ source code without explanations or markdown formatting:"""
            else:
                prompt = f"""You are an expert competitive programmer. Generate a C++ solution for this omegaUp problem.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

EDITORIAL (implement exactly what this describes):
{editorial}

Generate a C++ solution that:
- Implements EXACTLY what the editorial describes
- Uses efficient, correct C++ code that will get AC (Accepted)
- Handles edge cases properly
- Uses proper C++ syntax and includes (#include <bits/stdc++.h>)

IMPORTANT: Base your solution ONLY on the problem statement and editorial above. Do NOT use any external reference solutions.

Provide only the complete C++ source code without explanations or markdown formatting:"""
            
            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {"role": "system", "content": "You are an expert competitive programmer who generates clean, working C++ source code without any explanations or markdown formatting."},
                    {"role": "user", "content": prompt}
                ],
                max_tokens=2000,
                temperature=0.3
            )
            
            solution_code = response.choices[0].message.content.strip()
            
            # Clean the code thoroughly
            solution_code = self._clean_solution_code(solution_code)
            
            logger.info(f"Generated solution code ({len(solution_code)} chars)")
            return solution_code
            
        except Exception as e:
            logger.error(f"Failed to generate solution code: {str(e)}")
            return None

    def _clean_solution_code(self, code: str) -> str:
        """Clean solution code by removing markdown formatting and ensuring proper C++ format."""
        try:
            # Remove markdown code blocks
            if code.startswith('```'):
                lines = code.split('\n')
                # Find the first line that's not a markdown delimiter
                start_idx = 1
                for i, line in enumerate(lines):
                    if not line.strip().startswith('```') and not line.strip() in ['cpp', 'c++', 'cpp17', 'c']:
                        start_idx = i
                        break
                
                # Find the last line that's not a markdown delimiter
                end_idx = len(lines)
                for i in range(len(lines) - 1, -1, -1):
                    if not lines[i].strip().startswith('```'):
                        end_idx = i + 1
                        break
                
                code = '\n'.join(lines[start_idx:end_idx])
            
            # Remove any remaining markdown artifacts
            code = code.replace('```cpp', '').replace('```c++', '').replace('```', '')
            
            # Clean up whitespace
            code = code.strip()
            
            # Ensure it doesn't end with markdown formatting
            while code.endswith('```') or code.endswith('`'):
                code = code.rstrip('`').strip()
            
            # Enhanced cleaning: Remove explanatory text that might follow the code
            lines = code.split('\n')
            cleaned_lines = []
            code_ended = False
            
            for line in lines:
                line_stripped = line.strip()
                
                # If we haven't finished the code yet, check if this line marks the end of actual C++ code
                if not code_ended:
                    # If line contains explanatory text (common patterns)
                    if (line_stripped.startswith('This code') or 
                        line_stripped.startswith('The above') or 
                        line_stripped.startswith('This solution') or
                        line_stripped.startswith('Note:') or
                        line_stripped.startswith('Explanation:') or
                        'will get AC' in line_stripped or
                        'follows the approach' in line_stripped or
                        'described in the editorial' in line_stripped):
                        code_ended = True
                        continue
                    
                    # Add lines to cleaned code
                    cleaned_lines.append(line)
                
                # If code has ended, don't add any more lines
            
            # Join the cleaned lines back
            code = '\n'.join(cleaned_lines).strip()
            
            # Remove trailing empty lines
            while code.endswith('\n\n'):
                code = code.rstrip('\n')
            
            logger.info(f"Cleaned solution code ({len(code)} chars)")
            return code
            
        except Exception as e:
            logger.error(f"Error cleaning solution code: {str(e)}")
            return code

    def submit_solution(self, problem_alias: str, language: str, source_code: str, wait_before_submit: bool = False) -> Optional[tuple]:
        """Submit solution to omegaUp grader and return tuple of (run_guid, actual_language_used)."""
        try:
            # Wait before submission if this is a retry
            if wait_before_submit:
                logger.info("Waiting 60 seconds before retry submission due to omegaUp rate limit...")
                time.sleep(60)
            
            url = f"{self.api_url}/run/create"
            
            data = {
                'problem_alias': problem_alias,
                'language': language,
                'source': source_code
            }
            
            logger.info(f"Submitting solution to grader for verification...")
            
            response = self.session.post(url, data=data, timeout=(10, 30))
            
            if response.status_code != 200:
                logger.error(f"Submission failed with status {response.status_code}")
                return None
                
            result = response.json()
            if result.get("status") != "ok":
                error_msg = result.get('error', 'Unknown error')
                
                # Check for Karel-specific errors
                if 'karel' in error_msg.lower() or 'kj' in error_msg.lower():
                    logger.info(f"KAREL SKIP: {error_msg} - Problem requires Karel language")
                    return ("KAREL_SKIP", "kj")
                
                logger.error(f"Submission error: {error_msg}")
                return None
            
            run_guid = result.get('guid', '')
            if not run_guid:
                logger.error("No run GUID returned")
                return None
            
            logger.info(f"Submission successful. Run GUID: {run_guid}")
            return (run_guid, language)
            
        except Exception as e:
            logger.error(f"Failed to submit solution: {str(e)}")
            return None

    def check_run_status(self, run_guid: str, max_wait_time: int = 60) -> tuple:
        """Check run status and return (verdict, score, feedback)."""
        try:
            url = f"{self.api_url}/run/status"
            start_time = time.time()
            last_status = ""
            
            while time.time() - start_time < max_wait_time:
                params = {'run_alias': run_guid}
                response = self.session.get(url, params=params, timeout=(10, 30))
                
                if response.status_code != 200:
                    logger.error(f"Status check failed with status {response.status_code}")
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
                        logger.info(f"Status: {status} | Verdict: {verdict}")
                        last_status = status
                    
                    # Check if grading is complete
                    if status in ['ready', 'done']:
                        logger.info(f"Grading completed with verdict: {verdict}, score: {score}")
                        
                        # Get feedback info
                        execution = result.get('execution', '')
                        output = result.get('output', '')
                        compile_error = result.get('compile_error', '')
                        
                        feedback = execution or output or compile_error or ""
                        
                        return verdict, str(score), feedback
                    
                    # Check for errors
                    if status in ['error', 'compile_error']:
                        logger.error(f"Grading error - Status: {status}, Verdict: {verdict}")
                        
                        execution = result.get('execution', '')
                        output = result.get('output', '')
                        compile_error = result.get('compile_error', '')
                        
                        feedback = execution or output or compile_error or ""
                        
                        return verdict, str(score), feedback
                        
                else:
                    logger.warning(f"Status response doesn't match GUID or has unexpected format: {result}")
                
                time.sleep(3)
            
            logger.warning("Run status check timed out")
            return "TIMEOUT", "0", "Status check timed out"
            
        except Exception as e:
            logger.error(f"Failed to check run status: {str(e)}")
            return None, None, str(e)

    def verify_solution(self, problem_alias: str, editorial: str, solution_code: str, 
                       problem_details: Dict[str, Any], reference_ac: str, 
                       reference_language: str) -> Tuple[bool, Optional[str], Optional[str]]:
        """Verify that the generated solution gets AC (with one retry that regenerates both editorial and solution)."""
        try:
            language = "cpp17-gcc"
            
            # First attempt
            logger.info("=== VERIFYING SOLUTION (ATTEMPT 1) ===")
            submission_result1 = self.submit_solution(problem_alias, language, solution_code, wait_before_submit=False)
            if not submission_result1:
                logger.error("Failed to submit solution for verification")
                return False, None, None
            
            run_guid1, actual_language1 = submission_result1
            
            # Check if this is a Karel skip
            if run_guid1 == "KAREL_SKIP":
                logger.info(f"KAREL SKIP: Problem {problem_alias} requires Karel language")
                self.stats['karel_problems_skipped'] += 1
                self.stats['karel_problems'].append(problem_alias)
                return False, None, None
            
            # Check first attempt result
            verdict1, score1, feedback1 = self.check_run_status(run_guid1)
            if verdict1 is None:
                logger.error("Failed to check first run status")
                return False, None, None
            
            if verdict1 == "AC":
                logger.info(f"SUCCESS: Solution verified on first try!")
                self.stats['solution_verified_first_try'] += 1
                return True, None, None
            
            logger.info(f"FAILED: First attempt failed: {verdict1} (score: {score1})")
            if feedback1:
                logger.info(f"Feedback: {feedback1}")
            
            # Second attempt - regenerate BOTH editorial and solution based on error feedback
            logger.info("=== VERIFYING SOLUTION (ATTEMPT 2) ===")
            logger.info("Regenerating both editorial and solution based on error feedback...")
            
            error_info = f"Previous verdict: {verdict1}, Score: {score1}"
            if feedback1:
                error_info += f"\nFeedback: {feedback1}"
            
            # Step 1: Generate improved editorial with error feedback
            logger.info("Step 1: Regenerating editorial with error feedback...")
            improved_editorial = self.generate_editorial(problem_details, reference_ac, reference_language, error_info)
            
            if not improved_editorial:
                logger.error("Failed to generate improved editorial")
                self.stats['solution_verification_failed'] += 1
                return False, None, None
            
            logger.info("*** REGENERATED EDITORIAL ***")
            for i, line in enumerate(improved_editorial.split('\n'), 1):
                logger.info(f"{i:3d} | {line}")
            logger.info("*" * 40)
            
            # Step 2: Generate improved solution based on improved editorial and error feedback
            logger.info("Step 2: Regenerating solution based on improved editorial and error feedback...")
            improved_solution = self.generate_solution_code(problem_details, improved_editorial, error_info)
            
            if not improved_solution:
                logger.error("Failed to generate improved solution")
                self.stats['solution_verification_failed'] += 1
                return False, None, None
            
            logger.info("*** REGENERATED SOLUTION CODE ***")
            for i, line in enumerate(improved_solution.split('\n'), 1):
                logger.info(f"{i:3d} | {line}")
            logger.info("*" * 40)
            
            # Wait 60 seconds before submitting retry for same problem (omegaUp requirement)
            logger.info("Waiting 60 seconds before retry submission (omegaUp same-problem retry limit)...")
            time.sleep(60)
            
            # Submit second attempt
            submission_result2 = self.submit_solution(problem_alias, actual_language1, improved_solution, wait_before_submit=False)
            if not submission_result2:
                logger.error("Failed to submit improved solution")
                self.stats['solution_verification_failed'] += 1
                return False, None, None
            
            run_guid2, actual_language2 = submission_result2
            
            # Check second attempt result
            verdict2, score2, feedback2 = self.check_run_status(run_guid2)
            if verdict2 is None:
                logger.error("Failed to check second run status")
                self.stats['solution_verification_failed'] += 1
                return False, None, None
            
            if verdict2 == "AC":
                logger.info(f"SUCCESS: Solution verified on second try with regenerated content!")
                self.stats['solution_verified_second_try'] += 1
                return True, improved_editorial, improved_solution
            else:
                logger.error(f"FAILED: Second attempt also failed: {verdict2} (score: {score2})")
                if feedback2:
                    logger.error(f"Feedback: {feedback2}")
                self.stats['solution_verification_failed'] += 1
                self.stats['verification_failed_problems'].append(problem_alias)
                return False, None, None
            
        except Exception as e:
            logger.error(f"Failed to verify solution: {str(e)}")
            self.stats['solution_verification_failed'] += 1
            return False, None, None

    def save_results_to_files(self, problem_alias: str, problem_title: str, editorial: str, 
                             solution_code: str, verified: bool, reference_ac: str, 
                             reference_language: str, first_ac_run: Dict[str, Any]) -> None:
        """Save editorial and solution to log files."""
        try:
            log_dir = Path("logs") / "editorials"
            log_dir.mkdir(parents=True, exist_ok=True)
            
            # Save editorial
            editorial_file = log_dir / f"{problem_alias}_editorial.md"
            with open(editorial_file, 'w', encoding='utf-8') as f:
                f.write(f"# Editorial for {problem_title}\n\n")
                f.write(f"**Problem Alias**: {problem_alias}\n")
                f.write(f"**Generated**: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                f.write(f"**Solution Verified**: {'✓ AC' if verified else '✗ Failed'}\n")
                f.write(f"**Generation Method**: Two-step (Editorial first, then Solution based on Editorial)\n\n")
                f.write("---\n\n")
                f.write(editorial)
            
            # Save solution
            solution_file = log_dir / f"{problem_alias}_solution.cpp"
            with open(solution_file, 'w', encoding='utf-8') as f:
                f.write(f"// Solution for {problem_title}\n")
                f.write(f"// Problem Alias: {problem_alias}\n")
                f.write(f"// Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
                f.write(f"// Verification: {'AC' if verified else 'Failed'}\n")
                f.write(f"// Generation Method: Two-step (Editorial first, then Solution based on Editorial)\n\n")
                f.write(solution_code)
            
            # Save reference AC solution
            reference_file = log_dir / f"{problem_alias}_reference.{self._get_file_extension(reference_language)}"
            with open(reference_file, 'w', encoding='utf-8') as f:
                f.write(f"// Reference AC Solution for {problem_title}\n")
                f.write(f"// Problem Alias: {problem_alias}\n")
                f.write(f"// Author: {first_ac_run.get('username', 'unknown')}\n")
                f.write(f"// Language: {reference_language}\n")
                f.write(f"// Submission time: {first_ac_run.get('time', 'unknown')}\n")
                f.write(f"// Retrieved: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
                f.write(reference_ac)
            
            # Save problem details
            details_file = log_dir / f"{problem_alias}_details.json"
            with open(details_file, 'w', encoding='utf-8') as f:
                details = {
                    'problem_alias': problem_alias,
                    'problem_title': problem_title,
                    'generated_timestamp': datetime.now().isoformat(),
                    'solution_verified': verified,
                    'editorial_length': len(editorial),
                    'solution_length': len(solution_code),
                    'reference_author': first_ac_run.get('username', 'unknown'),
                    'reference_language': reference_language,
                    'reference_time': first_ac_run.get('time', 'unknown'),
                    'generation_method': 'two_step_editorial_first'
                }
                json.dump(details, f, indent=2, ensure_ascii=False)
            
            logger.info(f"[{problem_alias}] Saved to files:")
            logger.info(f"  Editorial: {editorial_file}")
            logger.info(f"  Solution: {solution_file}")
            logger.info(f"  Reference: {reference_file}")
            logger.info(f"  Details: {details_file}")
            
        except Exception as e:
            logger.error(f"Failed to save results to files: {str(e)}")

    def _get_file_extension(self, language: str) -> str:
        """Get appropriate file extension for a programming language."""
        language_extensions = {
            'cpp': 'cpp',
            'cpp11': 'cpp',
            'cpp11-gcc': 'cpp',
            'cpp17-gcc': 'cpp',
            'cpp17-clang': 'cpp',
            'cpp20-gcc': 'cpp',
            'cpp20-clang': 'cpp',
            'c': 'c',
            'c11-gcc': 'c',
            'c11-clang': 'c',
            'java': 'java',
            'py': 'py',
            'py2': 'py',
            'py3': 'py',
            'rb': 'rb',
            'pl': 'pl',
            'cs': 'cs',
            'pas': 'pas',
            'hs': 'hs',
            'lua': 'lua',
            'go': 'go',
            'rs': 'rs',
            'js': 'js',
            'kj': 'kj',
            'kp': 'kp'
        }
        return language_extensions.get(language.lower(), 'txt')

    def process_problem(self, problem_alias: str) -> Dict[str, Any]:
        """Process a single problem - main workflow with two-step generation."""
        result = {
            'problem_alias': problem_alias,
            'has_admin_access': False,
            'found_existing_ac': False,
            'editorial_generated': False,
            'solution_generated': False,
            'solution_verified': False,
            'saved_to_files': False,
            'success': False,
            'error': None
        }
        
        logger.info("=" * 60)
        logger.info(f"PROCESSING PROBLEM: {problem_alias}")
        logger.info("=" * 60)
        
        # Step 1: Get problem details
        problem_details = self.get_problem_details(problem_alias)
        if not problem_details:
            result['error'] = "Failed to fetch problem details"
            self.stats['problems_failed'] += 1
            self.stats['failed_problems'].append(problem_alias)
            return result
        
        problem_title = problem_details.get('title', 'Unknown')
        logger.info(f"Problem title: {problem_title}")
        
        # Step 2: Check admin access and find existing AC solution
        if not self.check_admin_access(problem_alias):
            result['error'] = "No admin access - cannot see existing AC solutions"
            self.stats['problems_failed'] += 1
            self.stats['failed_problems'].append(problem_alias)
            return result
        
        result['has_admin_access'] = True
        self.stats['problems_with_admin_access'] += 1
        
        # Step 3: Find existing AC solution
        first_ac_run = self.get_first_ac_run(problem_alias)
        if not first_ac_run:
            result['error'] = "No existing AC solution found"
            self.stats['problems_no_existing_ac'] += 1
            self.stats['no_ac_problems'].append(problem_alias)
            return result
        
        run_guid = first_ac_run.get('guid', '')
        if not run_guid:
            result['error'] = "No run GUID in AC solution"
            self.stats['problems_failed'] += 1
            self.stats['failed_problems'].append(problem_alias)
            return result
        
        reference_ac = self.get_run_source(run_guid)
        if not reference_ac:
            result['error'] = "Failed to get AC solution source code"
            self.stats['problems_failed'] += 1
            self.stats['failed_problems'].append(problem_alias)
            return result
        
        result['found_existing_ac'] = True
        self.stats['problems_with_existing_ac'] += 1
        reference_language = first_ac_run.get('language', 'cpp17-gcc')
        
        logger.info(f"[{problem_alias}] Using existing AC solution as reference for editorial generation only")
        logger.info(f"[{problem_alias}] Reference language: {reference_language}")
        
        # Log the reference AC solution
        logger.info("=" * 60)
        logger.info("*** REFERENCE AC SOLUTION (FOR EDITORIAL GENERATION) ***")
        logger.info("=" * 60)
        logger.info(f"Author: {first_ac_run.get('username', 'unknown')}")
        logger.info(f"Language: {reference_language}")
        logger.info(f"Submission time: {first_ac_run.get('time', 'unknown')}")
        logger.info("-" * 60)
        for i, line in enumerate(reference_ac.split('\n'), 1):
            logger.info(f"{i:3d} | {line}")
        logger.info("=" * 60)
        
        # Step 4: Generate editorial using reference AC (first step)
        logger.info(f"[{problem_alias}] Step 1: Generating editorial using reference AC solution...")
        editorial = self.generate_editorial(problem_details, reference_ac, reference_language)
        
        if not editorial:
            result['error'] = "Failed to generate editorial"
            self.stats['problems_failed'] += 1
            self.stats['failed_problems'].append(problem_alias)
            return result
        
        result['editorial_generated'] = True
        self.stats['editorial_generated'] += 1
        
        logger.info("=" * 60)
        logger.info("*** GENERATED EDITORIAL ***")
        logger.info("=" * 60)
        for i, line in enumerate(editorial.split('\n'), 1):
            logger.info(f"{i:3d} | {line}")
        logger.info("=" * 60)
        
        # Step 5: Generate solution code based ONLY on editorial and problem details (second step)
        logger.info(f"[{problem_alias}] Step 2: Generating solution code based ONLY on editorial and problem details...")
        solution_code = self.generate_solution_code(problem_details, editorial)
        
        if not solution_code:
            result['error'] = "Failed to generate solution code"
            self.stats['problems_failed'] += 1
            self.stats['failed_problems'].append(problem_alias)
            return result
        
        result['solution_generated'] = True
        self.stats['solution_generated'] += 1
        
        logger.info("=" * 60)
        logger.info("*** GENERATED SOLUTION CODE (BASED ON EDITORIAL) ***")
        logger.info("=" * 60)
        for i, line in enumerate(solution_code.split('\n'), 1):
            logger.info(f"{i:3d} | {line}")
        logger.info("=" * 60)
        
        # Step 6: Verify the generated solution
        logger.info(f"[{problem_alias}] Verifying generated solution...")
        solution_verified, updated_editorial, updated_solution = self.verify_solution(
            problem_alias, editorial, solution_code, problem_details, reference_ac, reference_language
        )
        result['solution_verified'] = solution_verified
        
        # Update editorial and solution if they were regenerated during verification
        if updated_editorial and updated_solution:
            editorial = updated_editorial
            solution_code = updated_solution
            logger.info("Updated editorial and solution from retry generation")
        
        # Step 7: Save results to files (regardless of verification result)
        try:
            self.save_results_to_files(problem_alias, problem_title, editorial, solution_code, 
                                     solution_verified, reference_ac, reference_language, first_ac_run)
            result['saved_to_files'] = True
        except Exception as e:
            logger.error(f"Failed to save results: {str(e)}")
            result['saved_to_files'] = False
        
        # Determine overall success
        if result['editorial_generated'] and result['solution_generated'] and result['saved_to_files']:
            result['success'] = True
            self.stats['problems_successful'] += 1
            self.stats['successful_problems'].append(problem_alias)
            
            logger.info("")
            logger.info("=" * 80)
            logger.info(f"[{problem_alias}] PROCESSING SUCCESS!")
            logger.info("=" * 80)
            logger.info(f"Problem: {problem_title}")
            logger.info(f"Editorial generated: ✓ ({len(editorial)} chars)")
            logger.info(f"Solution generated: ✓ ({len(solution_code)} chars)")
            logger.info(f"Solution verified: {'✓ AC' if solution_verified else '✗ Failed'}")
            logger.info(f"Files saved: ✓")
            logger.info(f"Method: Two-step (Editorial → Solution)")
            logger.info("=" * 80)
        else:
            result['error'] = "Failed to complete processing"
            self.stats['problems_failed'] += 1
            self.stats['failed_problems'].append(problem_alias)
        
        return result

    def load_problems_from_file(self, filename: str) -> List[str]:
        """Load problem aliases from text file."""
        try:
            filepath = Path(filename)
            if not filepath.exists():
                logger.error(f"File not found: {filename}")
                return []
            
            problems = []
            with open(filepath, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#'):  # Skip empty lines and comments
                        problems.append(line)
            
            logger.info(f"Loaded {len(problems)} problems from {filename}")
            return problems
            
        except Exception as e:
            logger.error(f"Error reading file {filename}: {str(e)}")
            return []

    def run_editorial_generation(self, problems: List[str]) -> None:
        """Run editorial generation for all problems."""
        if not problems:
            logger.error("No problems to process")
            return
        
        self.stats['total_problems'] = len(problems)
        
        logger.info("=" * 80)
        logger.info("STARTING EDITORIAL + SOLUTION GENERATION V5")
        logger.info("=" * 80)
        logger.info(f"Total problems: {len(problems)}")
        logger.info(f"Mode: Two-step generation (Editorial → Solution)")
        logger.info("=" * 80)
        
        for i, problem_alias in enumerate(problems, 1):
            logger.info(f"\n\n{'='*80}")
            logger.info(f"PROGRESS: {i}/{len(problems)} - Processing: {problem_alias}")
            logger.info("=" * 80)
            
            try:
                result = self.process_problem(problem_alias)
                logger.info(f"[{problem_alias}] Result: {'SUCCESS' if result['success'] else 'FAILED'}")
                if result['error']:
                    logger.info(f"[{problem_alias}] Error: {result['error']}")
                    
            except Exception as e:
                logger.error(f"[{problem_alias}] Unexpected error: {str(e)}")
                self.stats['problems_failed'] += 1
                self.stats['failed_problems'].append(problem_alias)
        
        # Print final statistics
        self._print_final_statistics()

    def _print_final_statistics(self) -> None:
        """Print comprehensive final statistics."""
        logger.info("\n\n")
        logger.info("=" * 80)
        logger.info("FINAL STATISTICS - V5 (Two-Step Generation)")
        logger.info("=" * 80)
        
        # Overall stats
        logger.info(f"Total problems processed: {self.stats['total_problems']}")
        logger.info(f"Successful: {self.stats['problems_successful']}")
        logger.info(f"Failed: {self.stats['problems_failed']}")
        logger.info(f"Success rate: {(self.stats['problems_successful']/self.stats['total_problems']*100):.1f}%" if self.stats['total_problems'] > 0 else "N/A")
        
        logger.info("\n" + "-" * 40)
        logger.info("DETAILED BREAKDOWN")
        logger.info("-" * 40)
        
        # Access and AC stats
        logger.info(f"Problems with admin access: {self.stats['problems_with_admin_access']}")
        logger.info(f"Problems with existing AC: {self.stats['problems_with_existing_ac']}")
        logger.info(f"Problems without existing AC: {self.stats['problems_no_existing_ac']}")
        logger.info(f"Karel problems skipped: {self.stats['karel_problems_skipped']}")
        
        # Generation stats
        logger.info(f"Editorials generated: {self.stats['editorial_generated']}")
        logger.info(f"Solutions generated: {self.stats['solution_generated']}")
        
        # Verification stats
        logger.info(f"Solutions verified (1st try): {self.stats['solution_verified_first_try']}")
        logger.info(f"Solutions verified (2nd try): {self.stats['solution_verified_second_try']}")
        logger.info(f"Solution verification failed: {self.stats['solution_verification_failed']}")
        
        total_verified = self.stats['solution_verified_first_try'] + self.stats['solution_verified_second_try']
        total_attempts = total_verified + self.stats['solution_verification_failed']
        verification_rate = (total_verified / total_attempts * 100) if total_attempts > 0 else 0
        logger.info(f"Verification success rate: {verification_rate:.1f}%")
        
        # Lists of problems
        if self.stats['successful_problems']:
            logger.info(f"\nSUCCESSFUL PROBLEMS ({len(self.stats['successful_problems'])}):")
            for problem in self.stats['successful_problems']:
                logger.info(f"  ✓ {problem}")
        
        if self.stats['failed_problems']:
            logger.info(f"\nFAILED PROBLEMS ({len(self.stats['failed_problems'])}):")
            for problem in self.stats['failed_problems']:
                logger.info(f"  ✗ {problem}")
        
        if self.stats['karel_problems']:
            logger.info(f"\nKAREL PROBLEMS SKIPPED ({len(self.stats['karel_problems'])}):")
            for problem in self.stats['karel_problems']:
                logger.info(f"  ⚠ {problem}")
        
        if self.stats['no_ac_problems']:
            logger.info(f"\nPROBLEMS WITHOUT EXISTING AC ({len(self.stats['no_ac_problems'])}):")
            for problem in self.stats['no_ac_problems']:
                logger.info(f"  ? {problem}")
        
        if self.stats['verification_failed_problems']:
            logger.info(f"\nPROBLEMS WITH VERIFICATION FAILURES ({len(self.stats['verification_failed_problems'])}):")
            for problem in self.stats['verification_failed_problems']:
                logger.info(f"  ✗ {problem}")
        
        logger.info("=" * 80)

def main():
    """Main function."""
    if len(sys.argv) != 2:
        print("Usage: python AiEG2.py <problems_file.txt>")
        sys.exit(1)
    
    problems_file = sys.argv[1]
    
    try:
        # Initialize generator
        generator = EditorialSolutionGeneratorV5()
        
        # Load problems from file
        problems = generator.load_problems_from_file(problems_file)
        if not problems:
            logger.error("No problems loaded from file")
            return
        
        # Run editorial generation
        generator.run_editorial_generation(problems)
        
    except KeyboardInterrupt:
        logger.info("\nProcess interrupted by user")
        sys.exit(1)
    except Exception as e:
        logger.error(f"Fatal error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()
