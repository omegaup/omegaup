#!/usr/bin/env python3
"""Editorial generator using 3-prompt system with multi-LLM support.

Uses secure configuration management following omegaUp patterns.
"""

import logging
from typing import Dict, Any, Optional, List

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
from llm_wrapper import LLMWrapper  # type: ignore
from omegaup_api_client import OmegaUpAPIClient  # type: ignore
from solution_handler import SolutionHandler  # type: ignore


class EditorialGenerator:
    """Generate editorials using a sophisticated 3-prompt system."""

    def __init__(self,
                 llm_config: Dict[str,
                                  Any],
                 prompts: Dict[str,
                               str],
                 redis_client: Any,
                 api_client: Optional[OmegaUpAPIClient] = None) -> None:
        """Initialize with LLM configuration and prompt templates."""
        self.llm_config = llm_config
        self.prompts = prompts
        self.redis_client = redis_client

        # Map provider names to what LLMWrapper expects
        provider_mapping = {
            'openai': 'gpt',
            'anthropic': 'claude',
            'google': 'gemini',
            'deepseek': 'deepseek'  # Already matches
        }

        llm_provider = provider_mapping.get(
            llm_config['provider'],
            llm_config['provider']
        )

        # Initialize LLM wrapper with proper provider and API key
        self.llm_wrapper = LLMWrapper(
            provider=llm_provider,
            api_key=llm_config['api_key']
        )

        # Initialize API client if not provided
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
            llm_config['provider'])

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
            reference_source = ac_solution.get('source', '') if ac_solution else ''
            reference_lang = ac_solution.get('language', 'unknown') if ac_solution else 'unknown'
            
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

    def verify_editorial_solution(self,
                                  problem_alias: str,
                                  editorial: str,
                                  problem_data: Dict[str, Any]
                                  ) -> Optional[Dict[str, Any]]:
        """Verify editorial by generating and testing solution (Prompt 2)."""

        try:
            logging.info("Verifying editorial solution for %s", problem_alias)

            # Get solution generation prompt
            prompt_template = self.prompts.get('solution_generation', '')
            if not prompt_template:
                logging.warning(
                    "Solution generation prompt template not found")
                return None

            # Format prompt
            prompt = prompt_template.format(
                editorial=editorial,
                problem_title=problem_data.get('title', 'Unknown'),
                constraints=self.extract_constraints(problem_data),
                sample_input=self.extract_sample_input(problem_data),
                sample_output=self.extract_sample_output(problem_data)
            )

            # Generate solution code
            response = self.llm_wrapper.generate_response(
                prompt=prompt,
                temperature=self.llm_config.get('temperature', 0.7)
            )

            if not response:
                logging.warning("Failed to generate solution for verification")
                return None

            generated_code = response

            # Test the generated solution
            success, message = (
                self.solution_handler.verify_solution_with_retry(
                    problem_alias, generated_code, max_attempts=2))
            verification_result = {'success': success, 'message': message}

            return verification_result  # type: ignore

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception("Error in solution verification: %s", e)
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

            # Format prompt with content and target language
            prompt = ("Translate the following editorial content to " +
                      f"{target_language}:\n\n{content}")

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
            return problem_data
        except ConnectionError as e:
            logging.error(
                'Authentication or connection failed for problem %s: %s',
                problem_alias, str(e))
            raise ValueError(
                f'Authentication failed when fetching problem {problem_alias}'
            ) from e
        except Exception as e:
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
                                    ac_solution: Optional[Dict[str,
                                                               Any]]) -> str:
        """Generate English editorial content."""
        editorial_en = self.generate_editorial_prompt(
            problem_data, ac_solution)
        if not editorial_en:
            raise ValueError('Failed to generate English editorial')
        return editorial_en

    def _verify_solution_if_enabled(self,
                                    problem_alias: str,
                                    editorial_en: str,
                                    problem_data: Dict[str, Any]
                                    ) -> Optional[Dict[str, Any]]:
        """Verify solution if verification is enabled."""
        if self.llm_config.get('enable_solution_verification', False):
            return self.verify_editorial_solution(
                problem_alias, editorial_en, problem_data)
        return None

    def _generate_translations(self, editorial_en: str) -> Dict[str, str]:
        """Generate translations for multiple languages."""
        translations = {}
        if self.llm_config.get('enable_multi_language', True):
            for lang in ['es', 'pt']:
                if lang in self.llm_config.get(
                    'languages', ['en', 'es', 'pt']):
                    translated = self.generate_translation(editorial_en, lang)
                    if translated:
                        translations[lang] = translated
        return translations

    def _add_disclaimers_to_all(
        self, editorials: Dict[str, str]) -> Dict[str, str]:
        """Add AI disclaimers to all editorial versions."""
        for lang, content in editorials.items():
            editorials[lang] = self.add_ai_disclaimer(content, lang)
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

            # Step 3: Generate English editorial (Prompt 1)
            editorial_en = self._generate_english_editorial(
                problem_data, ac_solution)

            # Step 4: Verify solution (Prompt 2) - DISABLED for now
            solution_verification = self._verify_solution_if_enabled(
                problem_alias, editorial_en, problem_data)

            # Step 5: Generate translations (Prompt 3)
            translated_editorials = self._generate_translations(editorial_en)
            all_editorials = {'en': editorial_en, **translated_editorials}

            # Step 6: Add AI disclaimer to all editorials
            final_editorials = self._add_disclaimers_to_all(all_editorials)

            return {
                'success': True,
                'editorials': final_editorials,
                'solution_verification': solution_verification,
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
                True),
            'solution_verification_enabled': self.llm_config.get(
                'enable_solution_verification',
                True)}
