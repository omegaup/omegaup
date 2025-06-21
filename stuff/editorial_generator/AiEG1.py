#!/usr/bin/env python3
"""
AiEG1 - Multi-Language Editorial Generator for omegaUp
Generates comprehensive editorials WITH AC solution code for problems and updates them on the website in 3 languages.

PROGRAM FLOW:
1. **Input Loading**: Load problem aliases from text file
2. **Editorial Existence Check**: Check if editorial already exists, skip if found
3. **Problem Details**: Fetch problem details from omegaUp API  
4. **Admin Access Check**: Verify admin access to see existing AC solutions
5. **AC Solution Discovery**: Look for existing AC solutions (oldest AC submission first)
6. **AC Solution Generation**: If no AC found, generate and verify solution using grader
7. **Multi-Language Editorial Generation**: 
   - Generate English editorial using AC solution as reference
   - Translate English editorial to Spanish and Portuguese using GPT-4
8. **Website Updates**: Upload all 3 language editorials to omegaUp website via API
9. **Statistics & Reporting**: Track success/failure rates and generate final report

KEY FEATURES:
-  Skips problems that already have editorials (checks via /problem/solution API)
-  Uses existing AC solutions as reference for high-quality editorials
-  Generates and verifies solutions if none exist
-  Supports 3 languages: English (en), Spanish (es), Portuguese (pt)
-  Updates editorials directly on the omegaUp website
-  Comprehensive logging and error handling
-  Detailed statistics tracking with problem categorization

OUTPUTS:
- Updates editorials on omegaUp website in 3 languages
- Detailed logs with processing results
- Statistics report with success/failure breakdown
"""

import sys
import os
import json
import logging
import requests
import time
from datetime import datetime
from pathlib import Path
from typing import List, Dict, Any, Optional
from dotenv import load_dotenv
from openai import OpenAI

# Setup logging
def setup_logging():
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

class ACBasedMultiLanguageEditorialGenerator:
    """Generates and uploads multi-language editorials for omegaUp problems."""

    def __init__(self):
        load_dotenv()
        
        # API Configuration
        self.api_url = os.getenv("OMEGAUP_API_URL", "https://omegaup.com/api")
        self.base_url = os.getenv("OMEGAUP_BASE_URL", "https://omegaup.com")
        
        # Authentication credentials
        self.username = os.getenv("OMEGAUP_USERNAME")
        self.password = os.getenv("OMEGAUP_PASSWORD")
        
        if not self.username or not self.password:
            raise ValueError("OMEGAUP_USERNAME and OMEGAUP_PASSWORD must be set in .env file")
        
        # Initialize OpenAI
        api_key = os.getenv("OPENAI_API_KEY")
        if not api_key:
            raise ValueError("OPENAI_API_KEY must be set in .env file")
        self.openai_client = OpenAI(api_key=api_key)
        
        # Initialize session for persistent connections and cookies
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'omegaUp-MultiLang-Editorial-Generator/1.0',
            'Accept': 'application/json',
            'Accept-Language': 'en-US,en;q=0.9,es;q=0.8,pt;q=0.7'
        })
        
        # Login for authenticated access
        self._login()
        
        # Target languages for editorial generation
        self.target_languages = {
            'es': 'Spanish',
            'en': 'English', 
            'pt': 'Portuguese'
        }
        
        # Statistics tracking
        self.stats = {
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
        
        logger.info("AC-Based Multi-Language Editorial Generator initialized successfully")

    def _login(self) -> None:
        """Login using the official API with username and password."""
        try:
            # Login endpoint
            login_url = f"{self.api_url}/user/login"
            login_data = {
                'usernameOrEmail': self.username,
                'password': self.password
            }
            
            # Headers for form data
            headers = {'Content-Type': 'application/x-www-form-urlencoded'}
            
            # Perform login
            response = self.session.post(login_url, data=login_data, headers=headers, timeout=(10, 30))
            
            if response.status_code == 200:
                result = response.json()
                if result.get("status") == "ok":
                    logger.info("Successfully logged in to omegaUp")
                else:
                    raise Exception(f"Login failed: {result.get('error', 'Unknown error')}")
            else:
                raise Exception(f"HTTP {response.status_code}: Login request failed")
            
        except Exception as e:
            logger.error(f"Failed to login: {str(e)}")
            raise

    def check_existing_editorial(self, problem_alias: str) -> bool:
        """Check if the problem already has an editorial using the solution API."""
        try:
            url = f"{self.api_url}/problem/solution"
            params = {'problem_alias': problem_alias}
            
            response = self.session.get(url, params=params, timeout=(10, 30))
            
            # If we get a successful response, it means an editorial exists
            if response.status_code == 200:
                result = response.json()
                if result.get("status") == "ok":
                    solution = result.get('solution', {})
                    # Check if there's actual content in the editorial
                    if solution and solution.get('markdown', '').strip():
                        logger.info(f"[{problem_alias}] Existing editorial found - SKIPPING")
                        return True
            
            # If we get 404 or any error, assume no editorial exists
            logger.info(f"[{problem_alias}] No existing editorial - will generate")
            return False
            
        except Exception as e:
            logger.warning(f"[{problem_alias}] Error checking editorial: {str(e)}")
            # If there's an error, assume no editorial to be safe
            return False

    def get_problem_details(self, problem_alias: str) -> Optional[Dict[str, Any]]:
        """Fetch problem details using the official API."""
        try:
            url = f"{self.api_url}/problem/details"
            params = {'problem_alias': problem_alias}
            
            response = self.session.get(url, params=params, timeout=(10, 30))
            
            if response.status_code != 200:
                logger.error(f"[{problem_alias}] HTTP {response.status_code} when fetching details")
                return None
                
            result = response.json()
            if result.get("status") != "ok":
                logger.error(f"[{problem_alias}] API error: {result.get('error', 'Unknown error')}")
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
                result = response.json()
                if result.get("status") == "ok":
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
                'rowcount': 100  # Get more runs to find the oldest
            }
            
            response = self.session.get(url, params=params, timeout=(10, 30))
            
            if response.status_code != 200:
                logger.warning(f"[{problem_alias}] HTTP {response.status_code} when fetching AC runs")
                return None
                
            result = response.json()
            if result.get("status") != "ok":
                logger.warning(f"[{problem_alias}] Error fetching AC runs: {result.get('error', 'Unknown error')}")
                return None
            
            runs = result.get("runs", [])
            if not runs:
                logger.info(f"[{problem_alias}] No AC runs found")
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

    def generate_solution_code(self, problem_details: Dict[str, Any], language: str = "cpp17-gcc", 
                             previous_error: Optional[str] = None, attempt: int = 1, previous_code: Optional[str] = None) -> Optional[str]:
        """Generate solution code using OpenAI GPT-4."""
        try:
            problem_statement = problem_details.get('statement', {}).get('markdown', '')
            problem_title = problem_details.get('title', 'Unknown Problem')
            
            # Trim problem statement if too long
            max_statement_length = 5000
            if len(problem_statement) > max_statement_length:
                problem_statement = problem_statement[:max_statement_length]
            
            if attempt == 1:
                prompt = f"""You are an expert competitive programming assistant. Generate a solution for this omegaUp problem.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

Requirements:
- Language: {language}
- Write efficient, correct code that will get AC (Accepted)
- Use appropriate algorithms and data structures
- Handle edge cases properly
- Follow competitive programming best practices
- Provide only the complete source code without explanations

Generate the complete {language} solution:"""
            else:
                prompt = f"""The previous solution failed. Generate an improved solution.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

Previous Error Information:
{previous_error}

Previous Code:
```{language}
{previous_code}
```

Requirements:
- Language: {language}
- Fix the issues from the previous attempt
- Write efficient, correct code that will get AC
- Handle edge cases properly
- Provide only the complete source code without explanations

Generate the improved {language} solution:"""
            
            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {"role": "system", "content": "You are an expert competitive programmer. Generate only clean, working source code without any explanations or markdown formatting."},
                    {"role": "user", "content": prompt}
                ],
                max_tokens=2000,
                temperature=0.3
            )
            
            code = response.choices[0].message.content.strip()
            
            # Clean up code (remove markdown formatting if present)
            if code.startswith('```'):
                lines = code.split('\n')
                if len(lines) > 2:
                    code = '\n'.join(lines[1:-1])
            
            logger.info(f"Generated {language} solution ({len(code)} chars)")
            return code
            
        except Exception as e:
            logger.error(f"Failed to generate solution code: {str(e)}")
            return None

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

    def generate_and_verify_solution(self, problem_details: Dict[str, Any], problem_alias: str, language: str = "cpp17-gcc") -> Optional[str]:
        """Generate solution and verify it gets AC before using for editorial."""
        try:
            logger.info(f"[{problem_alias}] Generating and verifying AC solution...")
            
            # First attempt
            logger.info("=== GENERATING SOLUTION (ATTEMPT 1) ===")
            code1 = self.generate_solution_code(problem_details, language)
            if not code1:
                logger.error("Failed to generate solution code")
                return None
            
            # Submit first attempt
            submission_result1 = self.submit_solution(problem_alias, language, code1, wait_before_submit=False)
            if not submission_result1:
                logger.error("Failed to submit first solution")
                return None
            
            run_guid1, actual_language1 = submission_result1
            
            # Check if this is a Karel skip
            if run_guid1 == "KAREL_SKIP":
                logger.info(f"KAREL SKIP: Problem {problem_alias} requires Karel language - cannot generate editorial")
                return None
            
            # Check first attempt result
            verdict1, score1, feedback1 = self.check_run_status(run_guid1)
            if verdict1 is None:
                logger.error("Failed to check first run status")
                return None
            
            if verdict1 == "AC":
                logger.info(f"SUCCESS: AC achieved on first try! Using this solution for editorial.")
                logger.info("=" * 60)
                logger.info("*** VERIFIED AC SOLUTION FOR EDITORIAL ***")
                logger.info("=" * 60)
                for i, line in enumerate(code1.split('\n'), 1):
                    logger.info(f"{i:3d} | {line}")
                logger.info("=" * 60)
                return code1
            
            logger.info(f"FAILED: First attempt failed: {verdict1} (score: {score1})")
            
            # Second attempt with error feedback
            logger.info("=== GENERATING SOLUTION (ATTEMPT 2) ===")
            error_info = f"Previous verdict: {verdict1}, Score: {score1}"
            if feedback1:
                error_info += f"\nFeedback: {feedback1}"
            
            # Generate second solution with error feedback
            code2 = self.generate_solution_code(problem_details, actual_language1, previous_error=error_info, attempt=2, previous_code=code1)
            if not code2:
                logger.error("Failed to generate second solution")
                return None
            
            # Submit second attempt  
            submission_result2 = self.submit_solution(problem_alias, actual_language1, code2, wait_before_submit=True)
            if not submission_result2:
                logger.error("Failed to submit second solution")
                return None
            
            run_guid2, actual_language2 = submission_result2
            
            # Check second attempt result
            verdict2, score2, feedback2 = self.check_run_status(run_guid2)
            if verdict2 is None:
                logger.error("Failed to check second run status")
                return None
            
            if verdict2 == "AC":
                logger.info(f"SUCCESS: AC achieved on second try! Using this solution for editorial.")
                logger.info("=" * 60)
                logger.info("*** VERIFIED AC SOLUTION FOR EDITORIAL ***")
                logger.info("=" * 60)
                for i, line in enumerate(code2.split('\n'), 1):
                    logger.info(f"{i:3d} | {line}")
                logger.info("=" * 60)
                return code2
            else:
                logger.error(f"FAILED: Second attempt also failed: {verdict2} (score: {score2})")
                logger.error("Cannot generate editorial - no verified AC solution available")
                return None
            
        except Exception as e:
            logger.error(f"Failed to generate and verify solution: {str(e)}")
            return None

    def generate_multilanguage_editorial(self, problem_details: Dict[str, Any], ac_solution: str, language: str) -> Optional[Dict[str, str]]:
        """Generate editorial in English first, then translate to Spanish and Portuguese."""
        try:
            # Step 1: Generate English editorial first
            english_editorial = self._generate_english_editorial(problem_details, ac_solution, language)
            if not english_editorial:
                logger.error("Failed to generate English editorial")
                return None
            
            # Step 2: Translate English editorial to Spanish and Portuguese
            translations = self._translate_editorial_to_languages(english_editorial)
            if not translations:
                logger.error("Failed to translate editorial to other languages")
                return None
            
            # Step 3: Combine with hardcoded AI disclaimers
            editorials = self._add_ai_disclaimers({
                'en': english_editorial,
                'es': translations['es'],
                'pt': translations['pt']
            })
            
            logger.info("Successfully generated all 3 language editorials")
            for lang_code, content in editorials.items():
                lang_name = self.target_languages[lang_code]
                logger.info(f"  {lang_name}: {len(content)} characters")
            
            return editorials
            
        except Exception as e:
            logger.error(f"Failed to generate multi-language editorials: {str(e)}")
            return None

    def _generate_english_editorial(self, problem_details: Dict[str, Any], ac_solution: str, language: str) -> Optional[str]:
        """Generate English editorial using AC solution as reference but WITHOUT including solution code."""
        try:
            problem_statement = problem_details.get('statement', {}).get('markdown', '')
            problem_title = problem_details.get('title', 'Unknown Problem')
            
            # Trim problem statement if too long (max 5k characters)
            max_statement_length = 5000
            if len(problem_statement) > max_statement_length:
                problem_statement = problem_statement[:max_statement_length]
            
            # Use AC solution as reference but don't include it in the final editorial
            prompt = f"""You are an expert competitive programming editorial writer. Create a comprehensive editorial for this omegaUp problem.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

REFERENCE SOLUTION (Language: {language}) - FOR UNDERSTANDING ONLY:
```{language}
{ac_solution}
```

Please create a comprehensive editorial that includes:

1. **Problem Analysis**: Explain what the problem is asking for in simple terms
2. **Key Insights**: What are the main insights needed to solve this problem?
3. **Algorithm/Approach**: Describe the solution approach step by step with detailed algorithmic flow
4. **Implementation Strategy**: Explain the implementation strategy and important considerations
5. **Time & Space Complexity**: Analyze the complexity of the solution
6. **Alternative Approaches**: Discuss any alternative solution methods if applicable

Requirements:
- Write in clear, educational English suitable for competitive programming students
- DO NOT include any solution code in the editorial - focus on explaining the logic and approach
- Use the reference solution to understand the approach but explain it conceptually
- Make the algorithm explanation extremely detailed and clear since no code is provided
- Explain the step-by-step flow of the solution process
- Use proper markdown formatting for the editorial
- Include complexity analysis
- Explain any mathematical concepts or algorithms used
- Do not add extra section headers beyond what's requested

Provide a complete editorial in markdown format WITHOUT any code."""
            
            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {"role": "system", "content": "You are an expert competitive programming editorial writer who creates clear, educational content WITHOUT including any solution code. Focus on algorithmic explanations and conceptual understanding."},
                    {"role": "user", "content": prompt}
                ],
                max_tokens=3000,
                temperature=0.3
            )
            
            editorial = response.choices[0].message.content.strip()
            logger.info(f"Generated English editorial ({len(editorial)} chars)")
            return editorial
            
        except Exception as e:
            logger.error(f"Failed to generate English editorial: {str(e)}")
            return None

    def _translate_editorial_to_languages(self, english_editorial: str) -> Optional[Dict[str, str]]:
        """Translate English editorial to Spanish and Portuguese."""
        try:
            prompt = f"""You are an expert translator specializing in competitive programming content. 

Please translate the following English competitive programming editorial to Spanish and Portuguese. Maintain the same technical accuracy, markdown formatting, and educational tone.

ENGLISH EDITORIAL TO TRANSLATE:
{english_editorial}

Format your response EXACTLY like this with clear delimiters:

=== SPANISH TRANSLATION ===
[Complete Spanish translation here]

=== PORTUGUESE TRANSLATION ===
[Complete Portuguese translation here]

Requirements:
- Maintain all markdown formatting exactly
- Keep technical terms accurate in each language
- Preserve the educational tone and structure
- Ensure competitive programming terminology is correct
- Do not add any additional content, just translate"""

            logger.info("Translating English editorial to Spanish and Portuguese...")
            
            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {"role": "system", "content": "You are an expert translator specializing in competitive programming and technical content."},
                    {"role": "user", "content": prompt}
                ],
                max_tokens=4000,
                temperature=0.2
            )
            
            translation_response = response.choices[0].message.content.strip()
            logger.info(f"Generated translations ({len(translation_response)} characters)")
            
            # Parse the translations
            translations = self._parse_translation_response(translation_response)
            
            if translations and len(translations) == 2:
                logger.info("Successfully parsed Spanish and Portuguese translations")
                logger.info(f"  Spanish: {len(translations['es'])} characters")
                logger.info(f"  Portuguese: {len(translations['pt'])} characters")
                return translations
            else:
                logger.error(f"Failed to parse translations. Got {len(translations) if translations else 0} translations")
                return None
            
        except Exception as e:
            logger.error(f"Failed to translate editorial: {str(e)}")
            return None

    def _parse_translation_response(self, response: str) -> Optional[Dict[str, str]]:
        """Parse the translation response to extract Spanish and Portuguese."""
        try:
            translations = {}
            
            # Define the delimiters
            delimiters = {
                'es': '=== SPANISH TRANSLATION ===',
                'pt': '=== PORTUGUESE TRANSLATION ==='
            }
            
            # Find delimiter positions
            sections = {}
            for lang_code, delimiter in delimiters.items():
                start_pos = response.find(delimiter)
                if start_pos == -1:
                    logger.error(f"Could not find delimiter for {lang_code}: {delimiter}")
                    return None
                sections[lang_code] = start_pos + len(delimiter)
            
            # Extract Spanish translation
            spanish_start = sections['es']
            portuguese_start = sections['pt']
            
            if spanish_start < portuguese_start:
                spanish_content = response[spanish_start:portuguese_start - len(delimiters['pt'])].strip()
                portuguese_content = response[portuguese_start:].strip()
            else:
                portuguese_content = response[portuguese_start:spanish_start - len(delimiters['es'])].strip()
                spanish_content = response[spanish_start:].strip()
            
            if spanish_content and portuguese_content:
                translations['es'] = spanish_content
                translations['pt'] = portuguese_content
                return translations
            else:
                logger.error("Empty translation content")
                return None
            
        except Exception as e:
            logger.error(f"Error parsing translation response: {str(e)}")
            return None

    def _add_ai_disclaimers(self, editorials: Dict[str, str]) -> Dict[str, str]:
        """Add hardcoded AI disclaimers to all editorials."""
        disclaimers = {
            'en': """*This editorial was generated using an AI model*

---

""",
            'es': """*Este editorial fue generado usando un modelo de IA*

---

""",
            'pt': """*Este editorial foi gerado usando um modelo de IA*

---

"""
        }
        
        final_editorials = {}
        for lang_code, content in editorials.items():
            final_editorials[lang_code] = disclaimers[lang_code] + content
        
        return final_editorials

    def _create_combined_editorial(self, editorials: Dict[str, str]) -> str:
        """Create a combined editorial with all three languages."""
        combined = ""
        
        # Add Spanish editorial
        if 'es' in editorials:
            combined += "# Editorial en Español\n\n"
            combined += editorials['es'] + "\n\n"
            combined += "---\n\n"
        
        # Add English editorial
        if 'en' in editorials:
            combined += "# Editorial in English\n\n"
            combined += editorials['en'] + "\n\n"
            combined += "---\n\n"
        
        # Add Portuguese editorial
        if 'pt' in editorials:
            combined += "# Editorial em Português\n\n"
            combined += editorials['pt'] + "\n\n"
        
        return combined.strip()

    def upload_editorial_simple(self, problem_alias: str, editorial_content: str) -> bool:
        """Upload editorial to omegaUp """
        try:
            logger.info(f"[{problem_alias}] Uploading editorial to omegaUp")
            logger.info(f"[{problem_alias}] Editorial content length: {len(editorial_content)} chars")
            
            commit_message = f"AI-generated multi-language editorial on {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}"
            logger.info(f"[{problem_alias}] Commit message: {commit_message}")
            
            # Prepare data for the API call 
            data = {
                'problem_alias': problem_alias,
                'solution': editorial_content,
                'message': commit_message,
                'lang': 'markdown'
            }
            
            url = f"{self.api_url}/problem/updateSolution"
            logger.info(f"POST {url}")
            
            # Log the data being sent (but truncate content for readability) 
            log_data = data.copy()
            if len(log_data['solution']) > 200:
                log_data['solution'] = log_data['solution'][:200] + f"... (total {len(data['solution'])} chars)"
            logger.info(f"Request data: {log_data}")
            
            response = self.session.post(url, data=data, timeout=(10, 60))
            
            logger.info(f"Response status code: {response.status_code}")
            
            if response.status_code != 200:
                logger.error(f"HTTP Error {response.status_code}: {response.text}")
                return False
            
            try:
                result = response.json()
                logger.info(f"API Response: {result}")
            except json.JSONDecodeError:
                logger.error(f"Invalid JSON response: {response.text}")
                return False
            
            if result.get("status") == "ok":
                logger.info(f"[{problem_alias}] Editorial uploaded successfully!")
                return True
            else:
                error_msg = result.get('error', 'Unknown error')
                logger.error(f"[{problem_alias}] Editorial upload failed: {error_msg}")
                return False
                
        except requests.exceptions.RequestException as e:
            error_msg = f"Editorial upload request failed: {str(e)}"
            logger.error(f"[{problem_alias}] {error_msg}")
            return False

    def upload_editorial_for_language(self, problem_alias: str, editorial_content: str, language_code: str) -> bool:
        """Upload editorial to omegaUp for specific language using the lang parameter."""
        try:
            language_name = self.target_languages.get(language_code, 'Spanish')
            logger.info(f"[{problem_alias}] Uploading {language_name} editorial to omegaUp")
            logger.info(f"[{problem_alias}] Editorial content length: {len(editorial_content)} chars")
            
            commit_message = f"AI-generated {language_name} editorial on {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}"
            logger.info(f"[{problem_alias}] Commit message: {commit_message}")
            
            # Prepare data for the API call using lang parameter to specify the language
            data = {
                'problem_alias': problem_alias,
                'solution': editorial_content,
                'message': commit_message,
                'lang': language_code  # Use the language code (en, es, pt) instead of 'markdown'
            }
            
            url = f"{self.api_url}/problem/updateSolution"
            logger.info(f"POST {url}")
            
            # Log the data being sent (but truncate content for readability) 
            log_data = data.copy()
            if len(log_data['solution']) > 200:
                log_data['solution'] = log_data['solution'][:200] + f"... (total {len(data['solution'])} chars)"
            logger.info(f"Request data: {log_data}")
            
            response = self.session.post(url, data=data, timeout=(10, 60))
            
            logger.info(f"Response status code: {response.status_code}")
            
            if response.status_code != 200:
                logger.error(f"HTTP Error {response.status_code}: {response.text}")
                return False
            
            try:
                result = response.json()
                logger.info(f"API Response: {result}")
            except json.JSONDecodeError:
                logger.error(f"Invalid JSON response: {response.text}")
                return False
            
            if result.get("status") == "ok":
                logger.info(f"[{problem_alias}] {language_name} editorial uploaded successfully!")
                return True
            else:
                error_msg = result.get('error', 'Unknown error')
                logger.error(f"[{problem_alias}] {language_name} editorial upload failed: {error_msg}")
                return False
                
        except requests.exceptions.RequestException as e:
            error_msg = f"{language_name} editorial upload request failed: {str(e)}"
            logger.error(f"[{problem_alias}] {error_msg}")
            return False

    def process_problem(self, problem_alias: str) -> Dict[str, Any]:
        """Process a single problem with AC solution-based multi-language editorial generation."""
        result = {
            'problem_alias': problem_alias,
            'has_existing_editorial': False,
            'has_admin_access': False,
            'used_existing_ac': False,
            'generated_ac_solution': False,
            'editorials_generated': {},
            'website_updates': {},
            'success': False,
            'error': None
        }
        
        logger.info("=" * 60)
        logger.info(f"PROCESSING PROBLEM: {problem_alias}")
        logger.info("=" * 60)
        
        # Step 1: Check if editorial already exists
        if self.check_existing_editorial(problem_alias):
            result['has_existing_editorial'] = True
            result['error'] = "Editorial already exists - skipped"
            self.stats['problems_with_existing_editorial'] += 1
            self.stats['existing_editorial_problems'].append(problem_alias)
            return result
        
        self.stats['problems_needing_editorial'] += 1
        
        # Step 2: Get problem details
        problem_details = self.get_problem_details(problem_alias)
        if not problem_details:
            result['error'] = "Failed to fetch problem details"
            self.stats['editorials_failed'] += 1
            self.stats['failed_editorial_problems'].append(problem_alias)
            return result
        
        problem_title = problem_details.get('title', 'Unknown')
        logger.info(f"Problem title: {problem_title}")
        
        # Step 3: Check for admin access and existing AC solution
        ac_solution = None
        language = "cpp17-gcc"  # default
        
        if self.check_admin_access(problem_alias):
            result['has_admin_access'] = True
            first_ac_run = self.get_first_ac_run(problem_alias)
            if first_ac_run:
                run_guid = first_ac_run.get('guid', '')
                if run_guid:
                    ac_solution = self.get_run_source(run_guid)
                    if ac_solution:
                        result['used_existing_ac'] = True
                        language = first_ac_run.get('language', 'cpp17-gcc')
                        logger.info(f"[{problem_alias}] *** USING EXISTING AC SOLUTION ***")
                        logger.info(f"[{problem_alias}] AC Solution Language: {language}")
                        logger.info("=" * 60)
                        logger.info("*** AC SOLUTION FOR EDITORIAL ***")
                        logger.info("=" * 60)
                        for i, line in enumerate(ac_solution.split('\n'), 1):
                            logger.info(f"{i:3d} | {line}")
                        logger.info("=" * 60)
        
        # Step 4: Generate AC solution if none available
        if not ac_solution:
            logger.info(f"[{problem_alias}] No existing AC solution - generating one...")
            ac_solution = self.generate_and_verify_solution(problem_details, problem_alias, language)
            if ac_solution:
                result['generated_ac_solution'] = True
                logger.info("=" * 60)
                logger.info("*** GENERATED AC SOLUTION FOR EDITORIAL ***")
                logger.info("=" * 60)
                for i, line in enumerate(ac_solution.split('\n'), 1):
                    logger.info(f"{i:3d} | {line}")
                logger.info("=" * 60)
            else:
                result['error'] = "Failed to generate verified AC solution after 2 attempts - skipping editorial generation"
                logger.error(f"[{problem_alias}] *** EDITORIAL GENERATION SKIPPED ***")
                logger.error(f"[{problem_alias}] Could not generate a verified AC solution after 2 attempts")
                logger.error(f"[{problem_alias}] Cannot create editorial without working solution")
                self.stats['editorials_failed'] += 1
                self.stats['failed_editorial_problems'].append(problem_alias)
                return result
        
        # Step 5: Generate multi-language editorials using AC solution
        logger.info(f"\n[{problem_alias}] Generating multi-language editorials using AC solution...")
        editorials = self.generate_multilanguage_editorial(problem_details, ac_solution, language)
        
        if editorials and len(editorials) == 3:
            # Mark all languages as successfully generated
            for lang_code in editorials.keys():
                result['editorials_generated'][lang_code] = True
            
            # Log all generated editorials
            for lang_code, editorial in editorials.items():
                lang_name = self.target_languages[lang_code]
                logger.info("=" * 80)
                logger.info(f"*** GENERATED {lang_name.upper()} EDITORIAL CONTENT ***")
                logger.info("=" * 80)
                for i, line in enumerate(editorial.split('\n'), 1):
                    logger.info(f"{i:3d} | {line}")
                logger.info("=" * 80)
            
            logger.info(f"Generated {len(editorials)}/{len(self.target_languages)} editorials")
        else:
            # Mark all languages as failed
            for lang_code in self.target_languages.keys():
                result['editorials_generated'][lang_code] = False
            
            result['error'] = "Failed to generate multi-language editorials"
            self.stats['editorials_failed'] += 1
            self.stats['failed_editorial_problems'].append(problem_alias)
            return result
        
        # Step 6: Upload separate editorials for each language using lang parameter
        logger.info(f"\nUploading separate editorials for each language to website...")
        
        successful_uploads = 0
        for lang_code, editorial_content in editorials.items():
            lang_name = self.target_languages[lang_code]
            logger.info(f"\nUploading {lang_name} editorial...")
            
            upload_success = self.upload_editorial_for_language(problem_alias, editorial_content, lang_code)
            
            if upload_success:
                successful_uploads += 1
                result['website_updates'][lang_code] = True
                logger.info(f"✓ {lang_name} editorial uploaded successfully")
            else:
                result['website_updates'][lang_code] = False
                logger.error(f"✗ {lang_name} editorial upload failed")
        
        # Determine overall success
        if successful_uploads > 0:
            result['success'] = True
            self.stats['editorials_generated_successfully'] += 1
            self.stats['generated_editorial_problems'].append(problem_alias)
            self.stats['website_updates_successful'] += 1
            
            logger.info("")
            logger.info("=" * 80)
            logger.info(f"[{problem_alias}] MULTI-LANGUAGE EDITORIAL SUCCESS!")
            logger.info("=" * 80)
            logger.info(f"Problem: {problem_title}")
            logger.info(f"Editorials generated: {len(editorials)}/{len(self.target_languages)}")
            logger.info(f"Separate language uploads: {successful_uploads}/{len(editorials)}")
            
            # Show which languages were successful
            for lang_code, success in result['website_updates'].items():
                lang_name = self.target_languages[lang_code]
                status = "✓" if success else "✗"
                logger.info(f"  {status} {lang_name} ({lang_code})")
            
            logger.info("=" * 80)
        else:
            result['error'] = "All website uploads failed"
            self.stats['editorials_failed'] += 1
            self.stats['failed_editorial_problems'].append(problem_alias)
            self.stats['website_updates_failed'] += 1
        
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
                    if line and not line.startswith('#'):
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
        logger.info("STARTING AC-BASED MULTI-LANGUAGE EDITORIAL GENERATION")
        logger.info("=" * 80)
        logger.info(f"Total problems: {len(problems)}")
        logger.info(f"Target languages: {', '.join(self.target_languages.values())}")
        logger.info("=" * 80)
        
        for i, problem_alias in enumerate(problems, 1):
            logger.info(f"\n\n{'='*80}")
            logger.info(f"PROGRESS: {i}/{len(problems)} - Processing: {problem_alias}")
            logger.info("=" * 80)
            
            try:
                result = self.process_problem(problem_alias)
                self.stats['problem_results'].append(result)
                
                # Track statistics
                if result.get('has_existing_editorial'):
                    self.stats['problems_with_existing_editorial'] += 1
                    self.stats['existing_editorial_problems'].append(problem_alias)
                    logger.info(f"SKIPPED: {problem_alias} - Editorial already exists")
                elif result.get('has_admin_access'):
                    self.stats['problems_with_admin_access'] += 1
                    self.stats['admin_access_problem_names'].append(problem_alias)
                
                if result.get('used_existing_ac'):
                    self.stats['problems_with_existing_ac'] += 1
                    self.stats['existing_ac_problem_names'].append(problem_alias)
                
                if result.get('generated_ac_solution'):
                    self.stats['problems_ac_generated'] += 1
                    self.stats['verified_ac_problem_names'].append(problem_alias)
                
                # Count problems that needed editorials
                if not result.get('has_existing_editorial'):
                    self.stats['problems_needing_editorial'] += 1
                
                if result.get('error'):
                    if 'Karel' in result.get('error', ''):
                        self.stats['karel_skipped'] += 1
                        self.stats['karel_problem_names'].append(problem_alias)
                        logger.info(f"KAREL SKIP: {problem_alias} - Karel-only problem")
                    elif 'Failed to generate verified AC solution' in result.get('error', ''):
                        self.stats['problems_ac_verification_failed'] += 1
                        self.stats['ac_verification_failed_problems'].append(problem_alias)
                        logger.error(f"AC VERIFICATION FAILED: {problem_alias} - No verified solution after 2 attempts")
                    elif 'Editorial already exists' not in result.get('error', ''):
                        self.stats['editorials_failed'] += 1
                        self.stats['failed_editorial_problems'].append(problem_alias)
                        logger.error(f"FAILED: {problem_alias} - {result['error']}")
                
                # Track successful editorial generation
                if result.get('success'):
                    self.stats['editorials_generated_successfully'] += 1
                    self.stats['generated_editorial_problems'].append(problem_alias)
                    logger.info(f"SUCCESS: {problem_alias} - Multi-language editorials generated and uploaded successfully")
                    
            except Exception as e:
                logger.error(f"Unexpected error processing {problem_alias}: {str(e)}")
                self.stats['editorials_failed'] += 1
                self.stats['failed_editorial_problems'].append(problem_alias)
        
        # Print final statistics
        self._print_final_statistics()

    def _print_final_statistics(self) -> None:
        """Print comprehensive final statistics."""
        logger.info("\n\n")
        logger.info("=" * 80)
        logger.info("FINAL STATISTICS - AC-BASED MULTI-LANGUAGE EDITORIAL GENERATION")
        logger.info("=" * 80)
        
        # Main statistics
        total = self.stats['total_problems']
        existing = self.stats['problems_with_existing_editorial']
        needing = self.stats['problems_needing_editorial']  # Only count problems that actually needed editorials
        generated = self.stats['editorials_generated_successfully']
        failed = self.stats['editorials_failed']
        ac_verification_failed = self.stats['problems_ac_verification_failed']
        errors = self.stats['api_errors']
        karel_skipped = self.stats['karel_skipped']
        
        admin_access = self.stats['problems_with_admin_access']
        existing_ac = self.stats['problems_with_existing_ac']
        
        logger.info(f"PROCESSING SUMMARY:")
        logger.info(f"   Total problems processed: {total}")
        logger.info(f"   Problems with existing editorial: {existing}")
        logger.info(f"   Problems needing editorial: {needing}")
        logger.info("")
        
        logger.info(f"EDITORIAL GENERATION:")
        logger.info(f"   Editorials generated successfully: {generated}")
        logger.info(f"   Editorial generation failed: {failed}")
        logger.info("")
        
        logger.info(f"AC SOLUTION STATISTICS:")
        logger.info(f"   Problems with admin access: {admin_access}/{total} ({admin_access/total*100:.1f}%)")
        logger.info(f"   Problems using existing AC solutions: {existing_ac}/{total} ({existing_ac/total*100:.1f}%)")
        logger.info(f"   Problems with generated AC solutions: {self.stats['problems_ac_generated']}/{total} ({self.stats['problems_ac_generated']/total*100:.1f}%)")
        logger.info("")
        
        # Website update results
        logger.info(f"WEBSITE UPDATES:")
        logger.info(f"   Website updates successful: {self.stats['website_updates_successful']}")
        logger.info(f"   Website updates failed: {self.stats['website_updates_failed']}")
        logger.info("")
        
        # Success rate
        if needing > 0:
            success_rate = (generated / needing) * 100
            logger.info(f"SUCCESS RATE: {success_rate:.1f}% ({generated}/{needing})")
            logger.info("")
        
        # Detailed lists
        if self.stats['existing_editorial_problems']:
            logger.info(f"PROBLEMS WITH EXISTING EDITORIALS ({len(self.stats['existing_editorial_problems'])}):")
            for problem in self.stats['existing_editorial_problems']:
                logger.info(f"   - {problem}")
            logger.info("")
        
        # Show successful generations
        if self.stats['generated_editorial_problems']:
            logger.info(f"SUCCESSFULLY GENERATED EDITORIALS ({len(self.stats['generated_editorial_problems'])} total):")
            for i, problem_name in enumerate(self.stats['generated_editorial_problems'], 1):
                logger.info(f"   {i:2d}. {problem_name}")
        
        # Show AC verification failed problems
        if self.stats['ac_verification_failed_problems']:
            logger.info(f"\nAC VERIFICATION FAILED (SKIPPED) ({len(self.stats['ac_verification_failed_problems'])} total):")
            for i, problem_name in enumerate(self.stats['ac_verification_failed_problems'], 1):
                logger.info(f"   {i:2d}. {problem_name}")
        
        # Show failed problems
        if self.stats['failed_editorial_problems']:
            logger.info(f"\nFAILED EDITORIAL GENERATION ({len(self.stats['failed_editorial_problems'])} total):")
            for i, problem_name in enumerate(self.stats['failed_editorial_problems'], 1):
                logger.info(f"   {i:2d}. {problem_name}")
        
        logger.info("=" * 80)
        logger.info("AC-Based Multi-Language Editorial Generation Complete!")
        logger.info("=" * 80)

        # User-friendly summary
        print(f"\nAC-Based Multi-Language Editorial Generation Complete!")
        print(f"Total: {total}, Existing: {existing}, Generated: {generated}, Failed: {failed}")
        if needing > 0:
            print(f"Success rate: {generated/needing*100:.1f}% ({generated}/{needing})")
        print(f"Detailed logs and statistics available above")

def main():
    """Main entry point for AC-based multi-language editorial generation."""
    if len(sys.argv) != 2:
        print("Usage: python AiEG1.py <problems_file>")
        print("")
        print("Example:")
        print("  python AiEG1.py problems_list.txt")
        print("")
        print("Features:")
        print("  1. Updates editorials ONLY if NO existing editorial found")
        print("  2. First looks for existing AC solutions (oldest AC submission)")
        print("  3. If no AC solution found, generates and verifies one using grader")
        print("  4. Uses AC solution in prompts to generate high-quality editorials")
        print("  5. Updates editorials in 3 languages: English, Spanish, Portuguese")
        print("  6. Each language gets separate API calls with lang parameter")
        print("")
        sys.exit(1)
    
    problems_file = sys.argv[1]
    
    try:
        # Initialize the generator
        generator = ACBasedMultiLanguageEditorialGenerator()
        
        # Load problems from file
        problems = generator.load_problems_from_file(problems_file)
        if not problems:
            logger.error("No problems found in file or file doesn't exist")
            sys.exit(1)
        
        # Run editorial generation
        generator.run_editorial_generation(problems)
        
    except KeyboardInterrupt:
        logger.info("\nInterrupted by user")
        sys.exit(0)
    except Exception as e:
        logger.error(f"Unexpected error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    sys.exit(main()) 