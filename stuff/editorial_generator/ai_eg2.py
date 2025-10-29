#!/usr/bin/env python3
"""
Main2 orchestrator for the AI Editorial Generation system (3-prompt approach).

This script uses a 3-prompt approach:
1. Generate editorial using AC solution as reference
2. Generate solution based ONLY on editorial + verify with grader
3. Translate editorial to multiple languages

Process: Editorial → Solution (verify) → Translation
"""

import sys
import time
from pathlib import Path
from typing import Dict, Any, Optional, List

# Import with fallback for relative imports
try:
    from .ai_editorial_generator import (  # type: ignore
        EditorialGeneratorConfig,
        StatsTracker,
        OmegaUpAPIClient,
        setup_logging
    )
except ImportError:
    from ai_editorial_generator import (
        EditorialGeneratorConfig,
        StatsTracker,
        OmegaUpAPIClient,
        setup_logging
    )

try:
    from .solution_handler import SolutionHandler  # type: ignore
except ImportError:
    from solution_handler import SolutionHandler

try:
    from .website_uploader import WebsiteUploader  # type: ignore
except ImportError:
    from website_uploader import WebsiteUploader

from openai import OpenAI  # type: ignore

logger = setup_logging()


class EditorialFirstOrchestrator:
    """3-prompt approach: Editorial → Solution (verify) → Translation."""

    def __init__(self) -> None:
        """Initialize the orchestrator with all required components."""
        self.config = EditorialGeneratorConfig()
        self.stats = StatsTracker()
        self.api_client = OmegaUpAPIClient(self.config)
        self.solution_handler = SolutionHandler(
            self.config, self.api_client.session
        )
        self.website_uploader = WebsiteUploader(
            self.config, self.api_client.session
        )

        # Initialize OpenAI client
        self.openai_client = OpenAI(api_key=self.config.openai_api_key)

        # Add stats tracking for 3-prompt approach
        self.stats.set_value('problems_successful', 0)
        self.stats.set_value('problems_failed', 0)
        self.stats.set_value('solution_verified_first_try', 0)
        self.stats.set_value('solution_verified_second_try', 0)
        self.stats.set_value('solution_verification_failed', 0)

        logger.info(
            "Editorial-First (3-prompt) Generator initialized successfully"
        )

    def process_problem(self, problem_alias: str) -> Dict[str, Any]:
        """Process single problem with 3-prompt approach."""
        result = self._initialize_result(problem_alias)

        logger.info("=" * 60)
        logger.info("PROCESSING PROBLEM (3-prompt): %s", problem_alias)
        logger.info("=" * 60)

        # Get basic requirements
        context = self._get_problem_context(problem_alias, result)
        if not context:
            return result

        # Process all steps
        return self._execute_processing_pipeline(context, result)

    def _get_problem_context(
        self, problem_alias: str, result: Dict[str, Any]
    ) -> Optional[Dict[str, Any]]:
        """Get problem context including details and AC solution."""
        # Step 1: Get problem details
        problem_details = self.api_client.get_problem_details(problem_alias)
        if not problem_details:
            result['error'] = "Failed to fetch problem details"
            self.stats.increment('problems_failed')
            return None

        problem_title = problem_details.get('title', 'Unknown')
        logger.info("Problem title: %s", problem_title)

        # Step 2: Check admin access
        if not self.api_client.check_admin_access(problem_alias):
            result['error'] = "No admin access"
            self.stats.increment('problems_failed')
            return None

        result['has_admin_access'] = True

        # Step 3: Get reference AC solution
        reference_ac, reference_language = self._get_reference_ac_solution(
            problem_alias
        )
        if not reference_ac:
            result['error'] = "No existing AC solution found for reference"
            self.stats.increment('problems_failed')
            return None

        result['found_existing_ac'] = True

        return {
            'problem_alias': problem_alias,
            'problem_details': problem_details,
            'problem_title': problem_title,
            'reference_ac': reference_ac,
            'reference_language': reference_language
        }

    def _execute_processing_pipeline(
        self, context: Dict[str, Any], result: Dict[str, Any]
    ) -> Dict[str, Any]:
        """Execute the full processing pipeline."""
        # Step 4: Generate editorial
        editorial = self._process_editorial_generation(context, result)
        if not editorial:
            return result

        # Step 5: Generate and verify solution
        editorial = self._process_solution_generation(
            context, editorial, result
        )
        if not editorial:
            return result

        # Step 6: Generate translations
        if not self._process_translation_generation(
                context, editorial, result):
            return result

        # Step 7: Optional website upload
        self._process_website_upload(context, result)

        # Finalize result
        return self._finalize_result(context, result)

    def _initialize_result(self, problem_alias: str) -> Dict[str, Any]:
        """Initialize result dictionary."""
        return {
            'problem_alias': problem_alias,
            'has_admin_access': False,
            'found_existing_ac': False,
            'editorial_generated': False,
            'solution_generated': False,
            'solution_verified': False,
            'translations_generated': False,
            'website_uploaded': False,
            'success': False,
            'error': None
        }

    def _process_editorial_generation(
        self, context: Dict[str, Any], result: Dict[str, Any]
    ) -> Optional[str]:
        """Process editorial generation step."""
        problem_alias = context['problem_alias']
        logger.info("[%s] PROMPT 1: Generating editorial...", problem_alias)

        editorial = self._generate_editorial(
            context['problem_details'],
            context['reference_ac'],
            context['reference_language']
        )
        if not editorial:
            result['error'] = "Failed to generate editorial"
            self.stats.increment('problems_failed')
            return None

        result['editorial_generated'] = True
        self._log_editorial(editorial)
        return editorial

    def _process_solution_generation(
        self, context: Dict[str, Any], editorial: str, result: Dict[str, Any]
    ) -> Optional[str]:
        """Process solution generation and verification step."""
        problem_alias = context['problem_alias']
        logger.info("[%s] PROMPT 2: Generating solution from editorial...",
                    problem_alias)

        solution_code = self._generate_solution_from_editorial(
            context['problem_details'], editorial
        )
        if not solution_code:
            result['error'] = "Failed to generate solution from editorial"
            self.stats.increment('problems_failed')
            return None

        result['solution_generated'] = True
        self._log_solution(solution_code)

        # Verify generated solution
        logger.info("[%s] Verifying generated solution...", problem_alias)
        verified, improved_editorial = self._verify_solution(
            context, solution_code
        )

        result['solution_verified'] = verified

        # Use improved version if available
        if improved_editorial:
            logger.info("Using improved editorial from retry")
            return improved_editorial

        return editorial

    def _process_translation_generation(
        self, context: Dict[str, Any], editorial: str, result: Dict[str, Any]
    ) -> bool:
        """Process translation generation step."""
        problem_alias = context['problem_alias']
        logger.info("[%s] PROMPT 3: Translating editorial...", problem_alias)

        translations = self._translate_editorial(editorial)
        if not translations:
            result['error'] = "Failed to translate editorial"
            self.stats.increment('problems_failed')
            return False

        result['translations_generated'] = True
        result['translations'] = translations
        return True

    def _process_website_upload(
        self, context: Dict[str, Any], result: Dict[str, Any]
    ) -> None:
        """Process optional website upload step."""
        if self._should_upload_to_website():
            problem_alias = context['problem_alias']
            logger.info("[%s] Uploading editorials to website...",
                        problem_alias)
            upload_success = self._upload_editorials(
                problem_alias, result.get('translations', {})
            )
            result['website_uploaded'] = upload_success

    def _finalize_result(
        self, context: Dict[str, Any], result: Dict[str, Any]
    ) -> Dict[str, Any]:
        """Finalize processing result."""
        result['success'] = (
            result['editorial_generated'] and
            result['solution_generated'] and
            result['translations_generated']
        )

        if result['success']:
            self.stats.increment('problems_successful')
            self._log_success_summary(
                context['problem_alias'], context['problem_title'], result
            )
        else:
            self.stats.increment('problems_failed')

        return result

    def _get_reference_ac_solution(
        self, problem_alias: str
    ) -> tuple[Optional[str], str]:
        """Get reference AC solution for editorial generation."""
        first_ac_run = self.solution_handler.get_first_ac_run(problem_alias)
        if not first_ac_run:
            return None, "cpp17-gcc"

        run_guid = first_ac_run.get('guid', '')
        if not run_guid:
            return None, "cpp17-gcc"

        reference_ac = self.solution_handler.get_run_source(run_guid)
        if not reference_ac:
            return None, "cpp17-gcc"

        reference_language = first_ac_run.get('language', 'cpp17-gcc')

        logger.info("Found reference AC by %s in %s",
                    first_ac_run.get('username', 'unknown'),
                    reference_language)

        return reference_ac, reference_language

    def _generate_editorial(
        self,
        problem_details: Dict[str, Any],
        reference_ac: str,
        reference_language: str
    ) -> Optional[str]:
        """Generate editorial based on problem details and reference AC."""
        try:
            statement = problem_details.get('statement', {})
            problem_statement = statement.get('markdown', '')
            problem_title = problem_details.get('title', 'Unknown Problem')

            # Trim problem statement if too long
            max_statement_length = 5000
            if len(problem_statement) > max_statement_length:
                problem_statement = problem_statement[:max_statement_length]

            prompt = f"""You are an expert competitive programming assistant.
Generate a comprehensive editorial for this omegaUp problem.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

REFERENCE SOLUTION (Language: {reference_language}) - FOR UNDERSTANDING ONLY:
```{reference_language}
{reference_ac}
```

Please generate a comprehensive editorial (WITHOUT any code) explaining
the approach:

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
                    {
                        "role": "system",
                        "content": (
                            "You are an expert competitive programming "
                            "assistant who generates clear, educational "
                            "editorials without any code."
                        )
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                max_tokens=2500,
                temperature=0.3
            )

            editorial = response.choices[0].message.content
            if editorial and isinstance(editorial, str):
                editorial = editorial.strip()
                logger.info("Generated editorial (%d chars)", len(editorial))
                return editorial
            return None

        except (AttributeError, KeyError, IndexError) as e:
            logger.error("Failed to generate editorial: %s", str(e))
            return None
        except OSError as e:
            logger.error("Network/API error generating editorial: %s", str(e))
            return None
        except Exception as e:  # pylint: disable=broad-except
            logger.error("Unexpected error generating editorial: %s", str(e))
            return None

    def _generate_solution_from_editorial(
        self, problem_details: Dict[str, Any], editorial: str
    ) -> Optional[str]:
        """Generate solution code based ONLY on problem details & editorial."""
        try:
            statement = problem_details.get('statement', {})
            problem_statement = statement.get('markdown', '')
            problem_title = problem_details.get('title', 'Unknown Problem')

            # Trim problem statement if too long
            max_statement_length = 5000
            if len(problem_statement) > max_statement_length:
                problem_statement = problem_statement[:max_statement_length]

            prompt = f"""You are an expert competitive programmer.
Generate a C++ solution for this omegaUp problem.

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

IMPORTANT: Base your solution ONLY on the problem statement and editorial
above. Do NOT use any external reference solutions.

Provide only the complete C++ source code without explanations:"""

            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {
                        "role": "system",
                        "content": (
                            "You are an expert competitive programmer who "
                            "generates clean, working C++ source code without "
                            "any explanations or markdown formatting."
                        )
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                max_tokens=2000,
                temperature=0.3
            )

            solution_code = response.choices[0].message.content
            if solution_code and isinstance(solution_code, str):
                # Clean the code
                solution_code = self._clean_solution_code(
                    solution_code.strip()
                )
                logger.info("Generated solution code (%d chars)",
                            len(solution_code))
                return solution_code
            return None

        except (AttributeError, KeyError, IndexError) as e:
            logger.error("Failed to generate solution code: %s", str(e))
            return None
        except OSError as e:
            logger.error("Network/API error generating solution: %s", str(e))
            return None
        except Exception as e:  # pylint: disable=broad-except
            logger.error("Unexpected error generating solution: %s", str(e))
            return None

    def _clean_solution_code(self, code: str) -> str:
        """Clean solution code by removing markdown formatting."""
        # Remove markdown code blocks
        if code.startswith('```'):
            lines = code.split('\n')
            if len(lines) > 2:
                code = '\n'.join(lines[1:-1])

        # Remove any remaining markdown artifacts
        code = code.replace('```cpp', '').replace('```c++', '')
        code = code.replace('```', '')

        return code.strip()

    def _verify_solution(
        self,
        context: Dict[str, Any],
        solution_code: str
    ) -> tuple[bool, Optional[str]]:
        """Verify generated solution with grader (2 attempts)."""
        try:
            problem_alias = context['problem_alias']
            language = "cpp17-gcc"

            # First attempt
            logger.info("=== VERIFYING SOLUTION (ATTEMPT 1) ===")
            success1 = self._submit_and_check_solution(
                problem_alias, language, solution_code
            )

            if success1:
                logger.info("SUCCESS: Solution verified on first try!")
                self.stats.increment('solution_verified_first_try')
                return True, None

            # Second attempt - regenerate both editorial and solution
            logger.info("=== VERIFYING SOLUTION (ATTEMPT 2) ===")
            logger.info("Regenerating editorial and solution...")

            # Wait before retry (omegaUp rate limit)
            logger.info("Waiting 60 seconds before retry...")
            time.sleep(60)

            # Try to regenerate and verify improved solution
            result = self._attempt_solution_regeneration(context)
            if result is not None:
                return result

            # Both attempts failed
            logger.error("FAILED: Both attempts failed verification")
            self.stats.increment('solution_verification_failed')
            return False, None

        except (AttributeError, KeyError, ValueError) as e:
            logger.error("Error during verification: %s", str(e))
            self.stats.increment('solution_verification_failed')
            return False, None
        except OSError as e:
            logger.error("Network error during verification: %s", str(e))
            self.stats.increment('solution_verification_failed')
            return False, None
        except Exception as e:  # pylint: disable=broad-except
            logger.error("Unexpected error during verification: %s", str(e))
            self.stats.increment('solution_verification_failed')
            return False, None

    def _attempt_solution_regeneration(
        self, context: Dict[str, Any]
    ) -> Optional[tuple[bool, Optional[str]]]:
        """Attempt to regenerate and verify improved solution."""
        # Regenerate editorial with error feedback
        improved_editorial = self._generate_editorial_with_feedback(
            context['problem_details'],
            context['reference_ac'],
            context['reference_language']
        )
        if not improved_editorial:
            return False, None

        # Regenerate solution based on improved editorial
        improved_solution = self._generate_solution_from_editorial(
            context['problem_details'], improved_editorial
        )
        if not improved_solution:
            return False, None

        # Verify improved solution
        success2 = self._submit_and_check_solution(
            context['problem_alias'], "cpp17-gcc", improved_solution
        )

        if success2:
            logger.info("SUCCESS: Solution verified on second try!")
            self.stats.increment('solution_verified_second_try')
            return True, improved_editorial

        return None  # Signal that regeneration didn't work

    def _submit_and_check_solution(
        self, problem_alias: str, language: str, solution_code: str
    ) -> bool:
        """Submit solution and check if it gets AC."""
        try:
            # Submit solution
            submission_result = self.solution_handler.submit_solution(
                problem_alias, language, solution_code
            )
            if not submission_result:
                return False

            run_guid, _ = submission_result

            # Check if Karel skip
            if run_guid == "KAREL_SKIP":
                logger.info("KAREL SKIP: Problem requires Karel language")
                return False

            # Check run status
            verdict, _, _ = self.solution_handler.check_run_status(run_guid)

            return bool(verdict == "AC")

        except (AttributeError, KeyError, ValueError) as e:
            logger.error("Failed to submit and check solution: %s", str(e))
            return False
        except OSError as e:
            logger.error("Network error submitting solution: %s", str(e))
            return False
        except Exception as e:  # pylint: disable=broad-except
            logger.error("Unexpected error submitting solution: %s", str(e))
            return False

    def _generate_editorial_with_feedback(
        self,
        problem_details: Dict[str, Any],
        reference_ac: str,
        reference_language: str
    ) -> Optional[str]:
        """Generate improved editorial with error feedback."""
        # For simplicity, just regenerate the editorial
        # In a full implementation, you'd include error feedback
        return self._generate_editorial(
            problem_details, reference_ac, reference_language
        )

    def _translate_editorial(self, editorial: str) -> Optional[Dict[str, str]]:
        """Translate editorial to multiple languages."""
        try:
            prompt = f"""You are an expert translator specializing in
competitive programming content.

Please translate the following English competitive programming editorial to
Spanish and Portuguese. Maintain the same technical accuracy, markdown
formatting, and educational tone.

ENGLISH EDITORIAL TO TRANSLATE:
{editorial}

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

            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {
                        "role": "system",
                        "content": (
                            "You are an expert translator specializing "
                            "in competitive programming and technical content."
                        )
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                max_tokens=4000,
                temperature=0.2
            )

            translation_response = response.choices[0].message.content
            if not translation_response:
                return None

            translation_response = translation_response.strip()

            # Parse translations
            translations = self._parse_translation_response(
                translation_response
            )

            if translations and len(translations) == 2:
                # Add original English and AI disclaimers
                final_editorials = self._add_ai_disclaimers({
                    'en': editorial,
                    'es': translations['es'],
                    'pt': translations['pt']
                })

                logger.info("Successfully translated editorial to 3 languages")
                return final_editorials

            return None

        except (AttributeError, KeyError, IndexError) as e:
            logger.error("Failed to translate editorial: %s", str(e))
            return None
        except OSError as e:
            logger.error("Network/API error translating editorial: %s", str(e))
            return None
        except Exception as e:  # pylint: disable=broad-except
            logger.error("Unexpected error translating editorial: %s", str(e))
            return None

    def _parse_translation_response(
        self, response: str
    ) -> Optional[Dict[str, str]]:
        """Parse the translation response."""
        try:
            translations = {}

            # Define delimiters
            spanish_delimiter = '=== SPANISH TRANSLATION ==='
            portuguese_delimiter = '=== PORTUGUESE TRANSLATION ==='

            # Find positions
            spanish_start = response.find(spanish_delimiter)
            portuguese_start = response.find(portuguese_delimiter)

            if spanish_start == -1 or portuguese_start == -1:
                return None

            # Extract content
            if spanish_start < portuguese_start:
                spanish_content = response[
                    spanish_start + len(spanish_delimiter):portuguese_start
                ].strip()
                portuguese_content = response[
                    portuguese_start + len(portuguese_delimiter):
                ].strip()
            else:
                portuguese_content = response[
                    portuguese_start + len(portuguese_delimiter):spanish_start
                ].strip()
                spanish_content = response[
                    spanish_start + len(spanish_delimiter):
                ].strip()

            if spanish_content and portuguese_content:
                translations['es'] = spanish_content
                translations['pt'] = portuguese_content
                return translations

            return None

        except (AttributeError, ValueError, TypeError) as e:
            logger.error("Error parsing translation response: %s", str(e))
            return None

    def _add_ai_disclaimers(
        self, editorials: Dict[str, str]
    ) -> Dict[str, str]:
        """Add AI disclaimers to all editorials."""
        disclaimers = {
            'en': ("*This editorial was generated using an AI model*"
                   "\n\n---\n\n"),
            'es': ("*Este editorial fue generado usando un modelo de IA*"
                   "\n\n---\n\n"),
            'pt': ("*Este editorial foi gerado usando um modelo de IA*"
                   "\n\n---\n\n")
        }

        final_editorials = {}
        for lang_code, content in editorials.items():
            final_editorials[lang_code] = disclaimers[lang_code] + content

        return final_editorials

    def _should_upload_to_website(self) -> bool:
        """Check if we should upload to website."""
        # Default to False (file-only mode like AiEG2)
        return False

    def _upload_editorials(
        self, problem_alias: str, editorials: Dict[str, str]
    ) -> bool:
        """Upload editorials to website."""
        try:
            successful_uploads = 0
            for lang_code, content in editorials.items():
                success = self.website_uploader.upload_editorial_for_language(
                    problem_alias, content, lang_code
                )
                if success:
                    successful_uploads += 1

            return successful_uploads > 0

        except (AttributeError, KeyError) as e:
            logger.error("Failed to upload editorials: %s", str(e))
            return False
        except OSError as e:
            logger.error("Network error uploading editorials: %s", str(e))
            return False
        except Exception as e:  # pylint: disable=broad-except
            logger.error("Unexpected error uploading editorials: %s", str(e))
            return False

    def _log_editorial(self, editorial: str) -> None:
        """Log generated editorial."""
        logger.info("=" * 60)
        logger.info("*** GENERATED EDITORIAL ***")
        logger.info("=" * 60)
        for i, line in enumerate(editorial.split('\n'), 1):
            logger.info("%3d | %s", i, line)
        logger.info("=" * 60)

    def _log_solution(self, solution_code: str) -> None:
        """Log generated solution."""
        logger.info("=" * 60)
        logger.info("*** GENERATED SOLUTION CODE (BASED ON EDITORIAL) ***")
        logger.info("=" * 60)
        for i, line in enumerate(solution_code.split('\n'), 1):
            logger.info("%3d | %s", i, line)
        logger.info("=" * 60)

    def _log_success_summary(
        self, problem_alias: str, problem_title: str, result: Dict[str, Any]
    ) -> None:
        """Log success summary."""
        logger.info("")
        logger.info("=" * 80)
        logger.info("[%s] 3-PROMPT PROCESSING SUCCESS!", problem_alias)
        logger.info("=" * 80)
        logger.info("Problem: %s", problem_title)
        logger.info("Editorial generated: ✓")
        logger.info("Solution generated: ✓")
        if result['solution_verified']:
            logger.info("Solution verified: ✓ AC")
        else:
            logger.info("Solution verified: ✗ Failed")
        logger.info("Translations generated: ✓")
        if result['website_uploaded']:
            logger.info("Website uploaded: ✓")
        logger.info("Method: 3-prompt (Editorial → Solution → Translation)")
        logger.info("=" * 80)

    def load_problems_from_file(self, filename: str) -> List[str]:
        """Load problem aliases from text file."""
        try:
            filepath = Path(filename)
            if not filepath.exists():
                logger.error("File not found: %s", filename)
                return []

            problems = []
            with open(filepath, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#'):
                        problems.append(line)

            logger.info("Loaded %d problems from %s", len(problems), filename)
            return problems

        except (OSError, UnicodeDecodeError) as e:
            logger.error("Error reading file %s: %s", filename, str(e))
            return []

    def run_editorial_generation(self, problems: List[str]) -> None:
        """Run 3-prompt editorial generation for all problems."""
        if not problems:
            logger.error("No problems to process")
            return

        self.stats.set_value('total_problems', len(problems))

        logger.info("=" * 80)
        logger.info("STARTING 3-PROMPT EDITORIAL GENERATION")
        logger.info("=" * 80)
        logger.info("Total problems: %d", len(problems))
        logger.info("Method: Editorial → Solution (verify) → Translation")
        logger.info("=" * 80)

        for i, problem_alias in enumerate(problems, 1):
            logger.info("\n\n%s", '=' * 80)
            logger.info("PROGRESS: %d/%d - Processing: %s",
                        i, len(problems), problem_alias)
            logger.info("=" * 80)

            try:
                result = self.process_problem(problem_alias)
                if result['success']:
                    logger.info("SUCCESS: %s", problem_alias)
                else:
                    logger.error("FAILED: %s - %s",
                                 problem_alias,
                                 result.get('error', 'Unknown error'))

            except (AttributeError, KeyError, ValueError) as e:
                logger.error("Error processing %s: %s", problem_alias, str(e))
                self.stats.increment('problems_failed')
            except OSError as e:
                logger.error("Network error processing %s: %s",
                             problem_alias, str(e))
                self.stats.increment('problems_failed')
            except Exception as e:  # pylint: disable=broad-except
                logger.error("Unexpected error processing %s: %s",
                             problem_alias, str(e))
                self.stats.increment('problems_failed')

        # Print final statistics
        self._print_final_statistics()

    def _print_final_statistics(self) -> None:
        """Print final statistics."""
        logger.info("\n\n")
        logger.info("=" * 80)
        logger.info("FINAL STATISTICS - 3-PROMPT GENERATION")
        logger.info("=" * 80)

        total = self.stats.get_value('total_problems')
        successful = self.stats.get_value('problems_successful')
        failed = self.stats.get_value('problems_failed')

        success_rate = (successful / total * 100) if total > 0 else 0

        logger.info("Total problems processed: %d", total)
        logger.info("Successful: %d", successful)
        logger.info("Failed: %d", failed)
        logger.info("Success rate: %.1f%%", success_rate)

        # Verification stats
        first_try = self.stats.get_value('solution_verified_first_try')
        second_try = self.stats.get_value('solution_verified_second_try')
        verification_failed = self.stats.get_value(
            'solution_verification_failed'
        )

        total_verified = first_try + second_try
        total_attempts = total_verified + verification_failed
        verification_rate = (
            (total_verified / total_attempts * 100)
            if total_attempts > 0 else 0
        )

        logger.info("")
        logger.info("SOLUTION VERIFICATION:")
        logger.info("  First try AC: %d", first_try)
        logger.info("  Second try AC: %d", second_try)
        logger.info("  Verification failed: %d", verification_failed)
        logger.info("  Verification rate: %.1f%%", verification_rate)

        logger.info("=" * 80)
        logger.info("3-PROMPT EDITORIAL GENERATION COMPLETE!")
        logger.info("=" * 80)


def main() -> int:
    """Main entry point for 3-prompt editorial generation."""
    if len(sys.argv) != 2:
        print("Usage: python ai_eg2.py <problems_file>")
        print("")
        print("Example:")
        print("  python ai_eg2.py problems_list.txt")
        print("")
        print("3-Prompt Approach:")
        print("  1. Generate editorial using AC solution as reference")
        print("  2. Generate solution based ONLY on editorial + verify with "
              "grader")
        print("  3. Translate editorial to multiple languages")
        print("")
        return 1

    problems_file = sys.argv[1]

    try:
        # Initialize the generator
        generator = EditorialFirstOrchestrator()

        # Load problems from file
        problems = generator.load_problems_from_file(problems_file)
        if not problems:
            logger.error("No problems found in file or file doesn't exist")
            return 1

        # Run editorial generation
        generator.run_editorial_generation(problems)
        return 0

    except KeyboardInterrupt:
        logger.info("\nInterrupted by user")
        return 0
    except (  # pylint: disable=broad-except
        AttributeError, KeyError, ValueError, OSError, Exception
    ) as e:
        error_type = "Network error" if isinstance(e, OSError) else "Error"
        logger.error("%s: %s", error_type, str(e))
        return 1


if __name__ == "__main__":
    sys.exit(main())
