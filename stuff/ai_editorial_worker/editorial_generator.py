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
        self.api_client = api_client or OmegaUpAPIClient(
            redis_client=redis_client
        )

        # Initialize solution handler
        self.solution_handler = SolutionHandler(
            config_manager=None,  # Not needed since we pass api_client
            api_client=self.api_client
        )

        logging.info(
            "Editorial generator initialized with provider: %s",
            llm_config['provider'])

    def generate_complete_editorial(
        self, problem_alias: str, job_id: str) -> Dict[str, Any]:
        """Generate complete editorial using 3-prompt system with website
        publishing."""

        logging.info(
            "Starting complete editorial generation for %s", problem_alias)

        try:
            # Step 1: Get problem details
            problem_data = self.api_client.get_problem_details(problem_alias)
            if not problem_data:
                return {
                    'success': False,
                    'error': f'Failed to fetch problem details for '
                    f'{problem_alias}'}

            # Step 2: Get AC solution (if available)
            ac_solution = self.solution_handler.get_best_ac_solution(
                problem_alias)

            # Step 3: Generate English editorial (Prompt 1)
            editorial_en = self.generate_editorial_prompt(
                problem_data, ac_solution)
            if not editorial_en:
                return {
                    'success': False,
                    'error': 'Failed to generate English editorial'
                }

            # Step 4: Verify solution (Prompt 2) - Optional but recommended
            solution_verification = None
            if self.llm_config.get('enable_solution_verification', True):
                solution_verification = self.verify_editorial_solution(
                    problem_alias, editorial_en, problem_data
                )

            # Step 5: Generate translations (Prompt 3)
            editorials = {'en': editorial_en}
            if self.llm_config.get('enable_multi_language', True):
                for lang in ['es', 'pt']:
                    if lang in self.llm_config.get(
                        'languages', ['en', 'es', 'pt']):
                        translated = self.generate_translation(
                            editorial_en, lang)
                        if translated:
                            editorials[lang] = translated

            # Add AI disclaimer to all editorials
            for lang, content in editorials.items():
                editorials[lang] = self.add_ai_disclaimer(content, lang)

            return {
                'success': True,
                'editorials': editorials,
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
            prompt = prompt_template.format(
                problem_title=problem_data.get('title', 'Unknown'),
                problem_statement=problem_data.get('statement', ''),
                constraints=self.extract_constraints(problem_data),
                sample_input=self.extract_sample_input(problem_data),
                sample_output=self.extract_sample_output(problem_data),
                ac_solution=(ac_solution.get('source', '')
                             if ac_solution else ''),
                time_limit=problem_data.get('time_limit', '1s'),
                memory_limit=problem_data.get('memory_limit', '256MB')
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
            verification_result = self.solution_handler.verify_solution(
                problem_alias, generated_code, max_attempts=2
            )

            return verification_result  # type: ignore

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception("Error in solution verification: %s", e)
            return None

    def generate_translation(
        self,
        editorial_en: str,
        target_lang: str) -> Optional[str]:
        """Generate translation using Prompt 3: EN Editorial → Target
        Language."""

        try:
            # Get translation prompt
            prompt_template = self.prompts.get('translation', '')
            if not prompt_template:
                logging.warning("Translation prompt template not found")
                return None

            # Language mapping
            lang_names = {
                'es': 'Spanish',
                'pt': 'Portuguese'
            }

            target_language = lang_names.get(target_lang, target_lang)

            # Format prompt
            prompt = prompt_template.format(
                target_language=target_language,
                editorial_content=editorial_en
            )

            # Generate translation
            response = self.llm_wrapper.generate_response(
                prompt=prompt,
                temperature=self.llm_config.get('temperature', 0.7)
            )

            if response:
                return response

            logging.warning(
                "Failed to generate %s translation", target_lang)
            return None

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception(
                "Error generating %s translation: %s", target_lang, e)
            return None

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
