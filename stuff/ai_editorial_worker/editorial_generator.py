#!/usr/bin/env python3
"""Editorial generator using 3-prompt system with multi-LLM support.

Uses secure configuration management following omegaUp patterns.
"""

import logging
from typing import Dict, Any, Optional, List, cast, Tuple

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
from llm_wrapper import LLMWrapper  # type: ignore
from solution_handler import SolutionHandler  # type: ignore


class EditorialGenerator:
    """Generate editorials using a sophisticated 3-prompt system."""

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

        logging.info(
            "Editorial generator initialized with provider: %s",
            self.llm_config['provider'])

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

    def _find_delimiter_positions(
        self, response: str) -> Tuple[int, int, int, int]:
        """Find positions of editorial and code delimiters."""
        editorial_delimiters = [
            '=== EDITORIAL ===',
            '== = EDITORIAL == =',
            '### EDITORIAL',
            '# EDITORIAL'
        ]

        code_delimiters = [
            '=== SOLUTION CODE ===',
            '== = SOLUTION CODE == =',
            '### SOLUTION CODE',
            '# SOLUTION CODE',
            '### Solution',
            '# Solution'
        ]

        editorial_start = -1
        editorial_delimiter_len = 0
        code_start = -1
        code_delimiter_len = 0

        # Find editorial delimiter
        for delimiter in editorial_delimiters:
            pos = response.find(delimiter)
            if pos != -1:
                editorial_start = pos
                editorial_delimiter_len = len(delimiter)
                logging.debug(
                    "Found editorial delimiter: %s at %d", delimiter, pos)
                break

        # Find code delimiter
        for delimiter in code_delimiters:
            pos = response.find(delimiter)
            if pos != -1:
                code_start = pos
                code_delimiter_len = len(delimiter)
                logging.debug(
                    "Found code delimiter: %s at %d", delimiter, pos)
                break

        return (editorial_start, editorial_delimiter_len,
                code_start, code_delimiter_len)

    def _extract_editorial_content(
        self,
        response: str,
        editorial_start: int,
        editorial_delimiter_len: int,
        code_start: int) -> Optional[str]:
        """Extract editorial content from response."""
        if editorial_start == -1:
            return None

        start_pos = editorial_start + editorial_delimiter_len
        if code_start != -1:
            editorial_content = response[start_pos:code_start].strip()
        else:
            editorial_content = response[start_pos:].strip()

        logging.info("Extracted editorial (%d chars)", len(editorial_content))
        return editorial_content

    def _extract_code_content(self, response: str, code_start: int,
                              code_delimiter_len: int) -> Optional[str]:
        """Extract code content from response."""
        if code_start == -1:
            return None

        start_pos = code_start + code_delimiter_len
        code_section = response[start_pos:].strip()

        # Extract C++ code from markdown code block
        cpp_start = code_section.find('```cpp')
        if cpp_start != -1:
            cpp_content_start = cpp_start + len('```cpp')
            cpp_end = code_section.find('```', cpp_content_start)
            if cpp_end != -1:
                code_content = code_section[cpp_content_start:cpp_end].strip()
                logging.info(
                    "Extracted C++ code (%d chars)",
                    len(code_content))
                return code_content

            logging.warning("No closing ``` found for C++ code")
        else:
            # Try to find code without markdown blocks
            logging.warning(
                "No ```cpp found, trying to extract code directly")
            # Look for #include as start of C++ code
            include_pos = code_section.find('#include')
            if include_pos != -1:
                code_content = code_section[include_pos:].strip()
                logging.info(
                    "Extracted C++ code without markdown (%d chars)",
                    len(code_content))
                return code_content

        return None

    def _apply_fallback_extraction(
        self,
        response: str,
        editorial_content: Optional[str],
        code_start: int) -> str:
        """Apply fallback extraction strategies for editorial content."""
        if editorial_content:
            return editorial_content

        # Try to extract everything before code
        if code_start != -1:
            editorial_content = response[:code_start].strip()
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
        try:
            logging.info("Parsing combined editorial and code response")
            logging.debug("Response length: %d characters", len(response))

            # Log first 500 characters for debugging
            preview = response[:500].replace('\n', '\\n')
            logging.debug("Response preview: %s", preview)

            # Find delimiter positions
            positions = self._find_delimiter_positions(response)
            editorial_start, editorial_delimiter_len = positions[:2]
            code_start, code_delimiter_len = positions[2:]

            # Extract content using helper methods
            editorial_content = self._extract_editorial_content(
                response, editorial_start, editorial_delimiter_len, code_start)
            code_content = self._extract_code_content(
                response, code_start, code_delimiter_len)

            # Apply fallback extraction
            editorial_content = self._apply_fallback_extraction(
                response, editorial_content, code_start)

            # Log final extraction results
            logging.info(
                "Extraction results: editorial=%s chars, code=%s chars",
                len(editorial_content) if editorial_content else 0,
                len(code_content) if code_content else 0)

            if not editorial_content:
                logging.error("Failed to extract editorial content")
                return "", None

            return editorial_content, code_content

        except (TypeError, ValueError, AttributeError) as e:
            logging.exception("Error parsing editorial and code: %s", e)
            return "", None

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

        disclaimers = {
            'en': {
                'verified': (
                    "\n\n---\n*This editorial was generated using AI "
                    "assistance and **verified**. "
                    "The solution approach has been tested "
                    "and works correctly.*"
                ),
                'not_verified': (
                    "\n\n---\n*This editorial was generated using AI "
                    "assistance but **could not be verified**. "
                    "Please verify the solution approach and "
                    "report any issues.*"
                ),
                'error': (
                    "\n\n---\n*This editorial was generated using AI "
                    "assistance but **verification failed** due to technical "
                    "issues. Please verify the solution approach and report "
                    "any issues.*"
                )
            },
            'es': {
                'verified': (
                    "\n\n---\n*Este editorial fue generado con asistencia de "
                    "IA y **verificado**. El enfoque de solución ha sido "
                    "probado y funciona correctamente.*"
                ),
                'not_verified': (
                    "\n\n---\n*Este editorial fue generado con asistencia de "
                    "IA pero **no pudo ser verificado**. Por favor verifica "
                    "el enfoque de la solución y reporta cualquier problema.*"
                ),
                'error': (
                    "\n\n---\n*Este editorial fue generado con asistencia de "
                    "IA pero **la verificación falló** debido a problemas "
                    "técnicos. Por favor verifica el enfoque de la solución "
                    "y reporta cualquier problema.*"
                )
            },
            'pt': {
                'verified': (
                    "\n\n---\n*Este editorial foi gerado com assistência de "
                    "IA e **verificado**. A abordagem da solução foi "
                    "testada e funciona corretamente.*"
                ),
                'not_verified': (
                    "\n\n---\n*Este editorial foi gerado com assistência de "
                    "IA mas **não pôde ser verificado**. Por favor "
                    "verifique a abordagem da solução e reporte quaisquer "
                    "problemas.*"
                ),
                'error': (
                    "\n\n---\n*Este editorial foi gerado com assistência de "
                    "IA mas **a verificação falhou** devido a problemas "
                    "técnicos. Por favor verifique a abordagem da solução "
                    "e reporte quaisquer problemas.*"
                )
            }
        }

        lang_disclaimers = disclaimers.get(language, disclaimers['en'])

        if verification_result.get('verified', False):
            return lang_disclaimers['verified']
        if verification_result.get('verdict'):
            return lang_disclaimers['not_verified']
        return lang_disclaimers['error']

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

        disclaimers = {
            'en': ("\n\n---\n*This editorial was generated using AI "
                   "assistance. While we strive for accuracy, please verify "
                   "the solution approach and report any issues.*"),
            'es': ("\n\n---\n*Este editorial fue generado con asistencia de "
                   "IA. Aunque nos esforzamos por la precisión, por favor "
                   "verifica el enfoque de la solución y reporta cualquier "
                   "problema.*"),
            'pt': ("\n\n---\n*Este editorial foi gerado com assistência de "
                   "IA. Embora nos esforcemos pela precisão, por favor "
                   "verifique a abordagem da solução e reporte quaisquer "
                   "problemas.*")
        }

        disclaimer = disclaimers.get(language, disclaimers['en'])
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
