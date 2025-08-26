#!/usr/bin/env python3
"""Editorial generator using 3-prompt system with multi-LLM support.

Uses secure configuration management following omegaUp patterns.
"""

import json
import logging
import re
from typing import Dict, Any, Optional, List, cast, Tuple

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
from llm_wrapper import LLMWrapper  # type: ignore
from solution_handler import SolutionHandler  # type: ignore


class EditorialGenerator:
    """Generate editorials using a sophisticated 3-prompt system."""
    # pylint: disable=too-many-instance-attributes

    def __init__(self,
                 config: Dict[str, Any]) -> None:
        """Initialize with configuration dictionary.

        Args:
            config: Configuration dictionary containing:
                - llm_config: LLM provider configuration
                - prompts: Dictionary of prompt templates
                - redis_client: Redis client instance
                - api_client: API client instance
                - full_config: Complete configuration dict
        """
        self.llm_config = config['llm_config']
        self.full_config = config.get('full_config', {})
        self.prompts = config['prompts']
        self.redis_client = config['redis_client']

        # Map provider names to what LLMWrapper expects
        provider_mapping = {
            'openai': 'gpt',
            'anthropic': 'claude',
            'google': 'gemini',
            'deepseek': 'deepseek'  # Already matches
        }

        llm_provider = provider_mapping.get(
            self.llm_config['provider'],
            self.llm_config['provider']
        )

        # Initialize LLM wrapper with proper provider and API key
        self.llm_wrapper = LLMWrapper(
            provider=llm_provider,
            api_key=self.llm_config['api_key']
        )

        # Initialize API client if not provided
        api_client = config.get('api_client')
        if api_client:
            self.api_client = api_client
        else:
            # Note: auth_token would need to be passed as parameter if
            # no api_client provided
            raise ValueError("API client must be provided")

        # Initialize solution handler
        self.solution_handler = SolutionHandler(
            config_manager=None,  # Not needed since we pass api_client
            api_client=self.api_client
        )

        # Load disclaimers from JSON file
        self.disclaimers = self._load_disclaimers()

        logging.info(
            "Editorial generator initialized with provider: %s",
            self.llm_config['provider'])

    def _load_disclaimers(self) -> Dict[str, Any]:
        """Load disclaimers from JSON file."""
        disclaimer_file = os.path.join(
            os.path.dirname(__file__), 'disclaimers.json')
        try:
            with open(disclaimer_file, 'r', encoding='utf-8') as f:
                data: Dict[str, Any] = json.load(f)
                return data
        except (FileNotFoundError, json.JSONDecodeError) as e:
            logging.warning(
                "Failed to load disclaimers: %s, using fallback", e)
            return self._get_fallback_disclaimers()

    def _get_fallback_disclaimers(self) -> Dict[str, Any]:
        """Fallback disclaimers if JSON file fails to load."""
        return {
            'verification_disclaimers': {
                'en': {
                    'verified': ("\n\n---\n*This editorial was generated "
                               "using AI assistance and **verified**. The "
                               "solution approach has been tested and works "
                               "correctly.*"),
                    'not_verified': ("\n\n---\n*This editorial was "
                                   "generated using AI assistance but "
                                   "**could not be verified**. Please verify "
                                   "the solution approach and report any "
                                   "issues.*"),
                    'error': ("\n\n---\n*This editorial was generated using "
                            "AI assistance but **verification failed** due "
                            "to technical issues. Please verify the solution "
                            "approach and report any issues.*")
                }
            },
            'ai_generation_disclaimers': {
                'en': ("\n\n---\n*This editorial was generated using AI "
                      "assistance. While we strive for accuracy, please "
                      "verify the solution approach and report any issues.*")
            }
        }

    def generate_editorial_prompt(self,
                                  problem_data: Dict[str, Any],
                                  ac_solution: Optional[Dict[str, Any]] = None
                                  ) -> Optional[str]:
        """Generate editorial using Prompt 1: Problem + AC Solution →
        Editorial."""

        try:
            # Build context
            _ = self.build_problem_context(problem_data, ac_solution)

            # Get prompt template
            prompt_template = self.prompts.get('editorial_generation', '')
            if not prompt_template:
                raise ValueError(
                    "Editorial generation prompt template not found")

            # Format prompt with context
            reference_source = ac_solution.get(
                'source', '') if ac_solution else ''
            reference_lang = ac_solution.get(
                'language', 'unknown') if ac_solution else 'unknown'

            prompt = prompt_template.format(
                problem_title=problem_data.get('title', 'Unknown'),
                problem_statement=problem_data.get('statement', ''),
                reference_language=reference_lang,
                reference_ac=reference_source
            )

            # Generate with LLM
            response = self.llm_wrapper.generate_response(
                prompt=prompt,
                temperature=self.llm_config.get('temperature', 0.7)
            )

            if response:
                return response

            logging.error("LLM generation failed: No response received")
            return None

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception("Error generating editorial: %s", e)
            return None

    def _parse_editorial_and_code_regex(
        self, response: str) -> Tuple[str, Optional[str]]:
        """Parse editorial and code using regex patterns."""
        try:
            logging.info("Parsing combined editorial and code response")
            logging.debug("Response length: %d characters", len(response))

            # Log first 500 characters for debugging
            preview = response[:500].replace('\n', '\\n')
            logging.debug("Response preview: %s", preview)

            # Comprehensive regex pattern to match the structure
            pattern = r'''
                # Editorial delimiter (flexible matching)
                (?P<editorial_delim>
                    ={3,}\s*EDITORIAL\s*={3,}|
                    ==\s*=\s*EDITORIAL\s*==\s*=|
                    #{1,3}\s*EDITORIAL
                )
                # Editorial content (non-greedy until code section or end)
                (?P<editorial_content>.*?)
                (?:
                    # Code delimiter (flexible matching)
                    (?P<code_delim>
                        ={3,}\s*(?:SOLUTION\s*)?CODE\s*={3,}|
                        ==\s*=\s*(?:SOLUTION\s*)?CODE\s*==\s*=|
                        #{1,3}\s*(?:SOLUTION\s*)?(?:CODE|Solution)
                    )
                    # Code section content
                    (?P<code_section>.*)
                |
                    # No code section found
                    $
                )
            '''

            match = re.search(pattern, response, re.DOTALL | re.VERBOSE)

            editorial_content = None
            code_content = None

            if match:
                # Extract editorial content
                editorial_raw = match.group('editorial_content')
                if editorial_raw:
                    editorial_content = editorial_raw.strip()
                    if editorial_content:
                        logging.debug(
                            "Found editorial delimiter: %s",
                            match.group('editorial_delim'))
                        logging.info(
                            "Extracted editorial (%d chars)",
                            len(editorial_content))

                # Extract code content if code section exists
                code_section = match.group('code_section')
                if code_section:
                    logging.debug(
                        "Found code delimiter: %s",
                        match.group('code_delim'))
                    code_content = self._extract_cpp_code(code_section)

            # Fallback strategies if no structured content found
            if not editorial_content:
                editorial_content = self._apply_fallback_extraction_regex(
                    response)

            # Log final extraction results
            logging.info(
                "Extraction results: editorial=%s chars, code=%s chars",
                len(editorial_content) if editorial_content else 0,
                len(code_content) if code_content else 0)

            if not editorial_content:
                logging.error("Failed to extract editorial content")
                return "", None

            return editorial_content, code_content

        except (TypeError, ValueError, AttributeError, re.error) as e:
            logging.exception("Error parsing editorial and code: %s", e)
            return "", None

    def _extract_cpp_code(self, code_section: str) -> Optional[str]:
        """Extract C++ code from code section using regex."""
        if not code_section:
            return None

        code_section = code_section.strip()

        # Try to extract C++ code from markdown code block
        cpp_block_pattern = r'```cpp\s*\n(.*?)\n\s*```'
        cpp_match = re.search(cpp_block_pattern, code_section, re.DOTALL)

        if cpp_match:
            code_content = cpp_match.group(1).strip()
            logging.info("Extracted C++ code (%d chars)", len(code_content))
            return code_content

        # Fallback: look for code starting with #include
        logging.warning("No ```cpp found, trying to extract code directly")
        include_pattern = r'(#include.*?)(?=\n\s*(?:={3,}|#{1,3}|$))'
        include_match = re.search(include_pattern, code_section, re.DOTALL)

        if include_match:
            code_content = include_match.group(1).strip()
            logging.info(
                "Extracted C++ code without markdown (%d chars)",
                len(code_content))
            return code_content

        logging.warning("No C++ code found in code section")
        return None

    def _apply_fallback_extraction_regex(self, response: str) -> str:
        """Apply fallback extraction strategies using regex."""
        # Try to find any code delimiter and extract everything before it
        code_delim_pattern = (
            r'(?:={3,}\s*(?:SOLUTION\s*)?CODE\s*={3,}|'
            r'==\s*=\s*(?:SOLUTION\s*)?CODE\s*==\s*=|'
            r'#{1,3}\s*(?:SOLUTION\s*)?(?:CODE|Solution))'
        )

        code_match = re.search(code_delim_pattern, response)
        if code_match:
            editorial_content = response[:code_match.start()].strip()
            if editorial_content:
                logging.info(
                    "Fallback: extracted editorial as everything before "
                    "code (%d chars)",
                    len(editorial_content))
                return editorial_content

        # Use entire response as fallback
        editorial_content = response.strip()
        logging.warning("Fallback: using entire response as editorial")
        return editorial_content

    def _parse_editorial_and_code(
        self, response: str) -> Tuple[str, Optional[str]]:
        """Parse combined editorial and code response."""
        return self._parse_editorial_and_code_regex(response)

    def _submit_and_verify_solution(self, problem_alias: str,
                                    cpp_code: str) -> Dict[str, Any]:
        """Submit generated code and return verification result."""
        try:
            logging.info("Submitting solution for verification: %s",
                         problem_alias)

            if not cpp_code or not cpp_code.strip():
                logging.error("No C++ code to submit")
                return {
                    'verified': False,
                    'verdict': 'NO_CODE',
                    'error': 'No C++ code generated',
                    'run_guid': None
                }

            # Submit the solution
            result = self.api_client.create_run(
                problem_alias=problem_alias,
                language='cpp17-gcc',  # Use standard C++17
                source=cpp_code
            )

            run_guid = result.get('guid')
            if not run_guid:
                logging.error("Failed to submit solution - no GUID returned")
                return {
                    'verified': False,
                    'verdict': 'SUBMIT_FAILED',
                    'error': 'Failed to submit solution',
                    'run_guid': None
                }

            logging.info("Submitted solution with GUID: %s", run_guid)

            # Wait for verdict
            verdict_result = self.api_client.wait_for_verdict(run_guid)

            verification_result = {
                'verified': verdict_result['success'],
                'verdict': verdict_result['verdict'],
                'score': verdict_result['score'],
                'memory': verdict_result['memory'],
                'runtime': verdict_result['runtime'],
                'run_guid': run_guid
            }

            if verdict_result['success']:
                logging.info("Solution verified successfully: AC")
            else:
                logging.warning("Solution verification failed: %s",
                                verdict_result['verdict'])

            return verification_result

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception("Error submitting solution: %s", e)
            return {
                'verified': False,
                'verdict': 'ERROR',
                'error': str(e),
                'run_guid': None
            }

    def _generate_verification_disclaimer(self, verification_result: Dict[
            str, Any], language: str) -> str:
        """Generate language-specific disclaimer based on verification."""

        disclaimers = self.disclaimers.get('verification_disclaimers', {})

        lang_disclaimers = disclaimers.get(language, disclaimers.get('en', {}))

        if verification_result.get('verified', False):
            return str(lang_disclaimers.get('verified', ''))
        if verification_result.get('verdict'):
            return str(lang_disclaimers.get('not_verified', ''))
        return str(lang_disclaimers.get('error', ''))

    def _parse_combined_translation(self, response: str) -> Dict[str, str]:
        """Parse combined Spanish and Portuguese translation response."""
        translations = {}
        try:
            logging.info("Response length: %d characters", len(response))
            logging.info("Looking for translation delimiters...")

            # Extract Spanish translation
            spanish_start = response.find('=== SPANISH TRANSLATION ===')
            portuguese_start = response.find('=== PORTUGUESE TRANSLATION ===')

            logging.info("Spanish delimiter at position: %d", spanish_start)
            logging.info(
                "Portuguese delimiter at position: %d",
                portuguese_start)

            if spanish_start != -1 and portuguese_start != -1:
                # Extract Spanish content
                start_pos = spanish_start + len('=== SPANISH TRANSLATION ===')
                spanish_content = response[start_pos:portuguese_start].strip()
                if spanish_content:
                    translations['es'] = spanish_content
                    msg = ("Successfully extracted Spanish translation "
                           "(%d chars)")
                    logging.info(msg, len(spanish_content))

                # Extract Portuguese content
                pt_start_pos = portuguese_start + \
                    len('=== PORTUGUESE TRANSLATION ===')
                portuguese_content = response[pt_start_pos:].strip()
                if portuguese_content:
                    translations['pt'] = portuguese_content
                    msg = ("Successfully extracted Portuguese translation "
                           "(%d chars)")
                    logging.info(msg, len(portuguese_content))
            elif spanish_start != -1:
                # Only Spanish found - try to extract it anyway
                logging.warning(
                    "Only Spanish translation found, extracting what we have")
                start_pos = spanish_start + len('=== SPANISH TRANSLATION ===')
                spanish_content = response[start_pos:].strip()
                if spanish_content:
                    translations['es'] = spanish_content
                    logging.info(
                        "Extracted partial Spanish translation (%d chars)",
                        len(spanish_content))
                    # Try to regenerate with more specific prompt for
                    # Portuguese only
                    logging.info(
                        "Attempting to generate Portuguese translation "
                        "separately")
                    pt_translation = self._generate_single_translation(
                        response[:500], 'Portuguese')
                    if pt_translation:
                        translations['pt'] = pt_translation
            else:
                logging.warning(
                    "Could not find translation delimiters in response")
                preview = (response[:500] + "..." if len(response) > 500
                           else response)
                logging.info("Response preview: %s", preview)

            return translations
        except (ValueError, TypeError, KeyError) as e:
            logging.warning("Failed to parse combined translation: %s", e)
            return {}

    def _generate_single_translation(
        self,
        editorial_text: str,
        language: str) -> Optional[str]:
        """Generate translation for a single language as fallback."""
        try:
            simple_prompt = (
                f"Translate the following competitive programming editorial "
                f"to {language}. Provide only the translation, no additional "
                f"text:\n\n{editorial_text}")

            response = self.llm_wrapper.generate_response(
                prompt=simple_prompt,
                temperature=self.llm_config.get('temperature', 0.7)
            )

            if response:
                logging.info(
                    "Successfully generated %s translation as fallback "
                    "(%d chars)", language, len(response))
                return response
            return None

        except (ConnectionError, TypeError, ValueError) as e:
            logging.warning(
                "Failed to generate %s translation as fallback: %s",
                language,
                e)
            return None

    def generate_translation(
        self,
        content: str,
        target_language: str) -> Optional[str]:
        """Generate translation of editorial content to target language."""
        try:
            # Get translation prompt template
            prompt_template = self.prompts.get('translation', '')
            if not prompt_template:
                logging.warning("Translation prompt template not found")
                return None

            # Format prompt with content using template
            prompt = prompt_template.format(editorial=content)

            # Generate translation with LLM
            response = self.llm_wrapper.generate_response(
                prompt=prompt,
                temperature=self.llm_config.get('temperature', 0.7)
            )

            return response if response else None

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception(
                "Error generating translation to %s: %s",
                target_language,
                e)
            return None

    def _get_problem_details(self, problem_alias: str) -> Dict[str, Any]:
        """Get problem details from API with authentication."""
        try:
            problem_data = self.api_client.get_problem_details(problem_alias)
            if not problem_data:
                raise ValueError(
                    f'Failed to fetch problem details for {problem_alias}')

            # Ensure alias is included in problem data
            problem_data['alias'] = problem_alias
            return cast(Dict[str, Any], problem_data)
        except ConnectionError as e:
            logging.error(
                'Authentication or connection failed for problem %s: %s',
                problem_alias, str(e))
            raise ValueError(
                f'Authentication failed when fetching problem {problem_alias}'
            ) from e
        except (TypeError, ValueError) as e:
            logging.error(
                'API error fetching problem %s: %s', problem_alias, str(e))
            raise ValueError(
                f'API error fetching problem {problem_alias}: {str(e)}'
            ) from e

    def _get_ac_solution(self, problem_alias: str) -> Optional[Dict[str, Any]]:
        """Get AC solution for the problem."""
        ac_solution_source = self.solution_handler.find_working_solution(
            problem_alias)
        return {'source': ac_solution_source} if ac_solution_source else None

    def _generate_english_editorial(self, problem_data: Dict[str, Any],
                                    ac_solution: Optional[Dict[str, Any]]
                                    ) -> Tuple[str, Optional[Dict[str, Any]]]:
        """Generate English editorial and verify with code submission."""
        # Generate combined editorial and code
        combined_response = self.generate_editorial_prompt(
            problem_data, ac_solution)
        if not combined_response:
            raise ValueError('Failed to generate editorial and code')

        # Parse the combined response
        editorial_content, cpp_code = self._parse_editorial_and_code(
            combined_response)

        if not editorial_content:
            # Fallback: use the raw response as editorial if parsing failed
            logging.warning(
                "Failed to extract editorial content, using raw response "
                "as fallback")
            editorial_content = combined_response.strip()
            if not editorial_content:
                raise ValueError(
                    'Failed to extract editorial content and response '
                    'is empty')

        # Submit and verify the generated code
        verification_result = None
        if cpp_code:
            problem_alias = problem_data.get('alias', 'unknown')
            verification_result = self._submit_and_verify_solution(
                problem_alias, cpp_code)
            logging.info("Solution verification result: %s",
                         verification_result.get('verdict', 'UNKNOWN'))
        else:
            logging.warning("No C++ code generated for verification")
            verification_result = {
                'verified': False,
                'verdict': 'NO_CODE',
                'error': 'No C++ code generated'
            }

        return editorial_content, verification_result

    def _generate_translations(self, editorial_en: str) -> Dict[str, str]:
        """Generate translations for multiple languages using combined
        prompt."""
        translations = {}
        if self.full_config.get('enable_multi_language', True):
            logging.info(
                "Starting translation generation for Spanish and Portuguese")
            # Generate both Spanish and Portuguese in one API call
            combined_response = self.generate_translation(
                editorial_en, 'combined')
            if combined_response:
                logging.info(
                    "Received combined translation response, parsing...")
                # Parse the combined response
                parsed_translations = self._parse_combined_translation(
                    combined_response)
                lang_list = list(parsed_translations.keys())
                logging.info("Parsed translations for languages: %s",
                             lang_list)
                translations.update(parsed_translations)
            else:
                logging.warning("No translation response received from LLM")
        else:
            logging.info("Multi-language translation disabled in config")
        return translations

    def _add_disclaimers_to_all(
        self, editorials: Dict[str, str]) -> Dict[str, str]:
        """Add AI disclaimers to all editorial versions."""
        for lang, content in editorials.items():
            editorials[lang] = self.add_ai_disclaimer(content, lang)
        return editorials

    def _add_verification_disclaimers_to_all(
        self, editorials: Dict[str, str],
        verification_result: Optional[Dict[str, Any]]) -> Dict[str, str]:
        """Add verification-based disclaimers to all editorial versions."""
        if not verification_result:
            # Fallback to standard disclaimers
            return self._add_disclaimers_to_all(editorials)

        for lang, content in editorials.items():
            disclaimer = self._generate_verification_disclaimer(
                verification_result, lang)
            editorials[lang] = content + disclaimer
        return editorials

    def generate_complete_editorial(
        self, problem_alias: str, job_id: str) -> Dict[str, Any]:
        """Generate complete editorial using 3-prompt system with website
        publishing."""

        logging.info(
            "Starting complete editorial generation for %s", problem_alias)

        try:
            # Step 1: Get problem details
            problem_data = self._get_problem_details(problem_alias)

            # Step 2: Get AC solution (if available)
            ac_solution = self._get_ac_solution(problem_alias)

            # Step 3: Generate English editorial with code verification
            editorial_en, verification_result = (
                self._generate_english_editorial(problem_data, ac_solution))

            # Step 4: Generate translations (Prompt 3)
            translated_editorials = self._generate_translations(editorial_en)
            all_editorials = {'en': editorial_en, **translated_editorials}

            # Step 5: Add verification-based disclaimers to all editorials
            final_editorials = self._add_verification_disclaimers_to_all(
                all_editorials, verification_result)

            return {
                'success': True,
                'editorials': final_editorials,
                'solution_verification': verification_result,
                'problem_alias': problem_alias,
                'job_id': job_id
            }

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception("Error in complete editorial generation: %s", e)
            return {
                'success': False,
                'error': str(e)
            }

    def build_problem_context(self,
                              problem_data: Dict[str, Any],
                              ac_solution: Optional[Dict[str, Any]] = None
                              ) -> Dict[str, Any]:
        """Build comprehensive problem context for LLM."""

        context = {
            'title': problem_data.get('title', 'Unknown'),
            'statement': problem_data.get('statement', ''),
            'constraints': self.extract_constraints(problem_data),
            'sample_cases': self.extract_sample_cases(problem_data),
            'time_limit': problem_data.get('time_limit', '1s'),
            'memory_limit': problem_data.get('memory_limit', '256MB'),
            'difficulty': problem_data.get('difficulty', 'Unknown'),
            'tags': problem_data.get('tags', [])
        }

        if ac_solution:
            context['ac_solution'] = {
                'language': ac_solution.get('language', 'cpp'),
                'source': ac_solution.get('source', ''),
                'verdict': ac_solution.get('verdict', 'AC'),
                'runtime': ac_solution.get('runtime', 'Unknown')
            }

        return context

    def extract_constraints(self, problem_data: Dict[str, Any]) -> str:
        """Extract problem constraints from problem data."""
        # This would parse the problem statement for constraints
        # For now, return basic info
        return (f"Time limit: {problem_data.get('time_limit', '1s')}, "
                f"Memory limit: {problem_data.get('memory_limit', '256MB')}")

    def extract_sample_input(self, problem_data: Dict[str, Any]) -> str:
        """Extract sample input from problem data."""
        examples = problem_data.get('examples', [])
        if examples and len(examples) > 0:
            return examples[0].get('input', '')  # type: ignore
        return ''

    def extract_sample_output(self, problem_data: Dict[str, Any]) -> str:
        """Extract sample output from problem data."""
        examples = problem_data.get('examples', [])
        if examples and len(examples) > 0:
            return examples[0].get('output', '')  # type: ignore
        return ''

    def extract_sample_cases(
        self, problem_data: Dict[str, Any]) -> List[Dict[str, str]]:
        """Extract all sample test cases."""
        examples = problem_data.get('examples', [])
        return [
            {
                'input': example.get('input', ''),
                'output': example.get('output', '')
            }
            for example in examples
        ]

    def add_ai_disclaimer(self, content: str, language: str) -> str:
        """Add AI generation disclaimer to editorial content."""

        disclaimers = self.disclaimers.get('ai_generation_disclaimers', {})
        disclaimer = str(disclaimers.get(language, disclaimers.get('en', '')))
        return content + disclaimer

    def get_generation_stats(self) -> Dict[str, Any]:
        """Get statistics about editorial generation."""
        return {
            'provider': self.llm_config['provider'],
            'model': self.llm_config['model'],
            'max_tokens': self.llm_config.get(
                'max_tokens',
                4000),
            'temperature': self.llm_config.get(
                'temperature',
                0.7),
            'multi_language_enabled': self.llm_config.get(
                'enable_multi_language',
                True)}
