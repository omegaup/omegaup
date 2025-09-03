#!/usr/bin/env python3

"""
AI Editorial Worker

This worker processes editorial generation jobs from Redis queue and uses the
currently logged-in user's session for authentication (passed via auth_token).\
"""

import argparse
import configparser
import logging
import os
import re
import sys
import time
import traceback
from typing import Dict, Any, Optional, Tuple

# Import worker components at module level
try:
    from .config_manager import ConfigManager
    from .editorial_generator import EditorialGenerator
    from .solution_handler import SolutionHandler
    from .website_uploader import WebsiteUploader
    from .redis_client import RedisJobClient
    PACKAGE_MODE = True
except ImportError:
    # Add parent directory to path for llm_wrapper and API client
    sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from config_manager import ConfigManager  # type: ignore
from omegaup_api_client import OmegaUpAPIClient  # type: ignore
from editorial_generator import EditorialGenerator  # type: ignore
from solution_handler import SolutionHandler  # type: ignore
from website_uploader import WebsiteUploader  # type: ignore
from redis_client import RedisJobClient  # type: ignore
PACKAGE_MODE = False

# Import existing omegaUp libraries
sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))),
        "."
    )
)
import lib.db
import lib.logs

# Components are now imported at module level


def main() -> None:
    """Main entry point for the AI Editorial Worker."""
    parser = argparse.ArgumentParser(description='AI Editorial Worker')
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    parser.add_argument('--worker-id', type=int, default=0,
                        help='Worker ID for multi-worker deployments')
    parser.add_argument('--test-mode', action='store_true',
                        help='Run in test mode with enhanced logging')

    args = parser.parse_args()
    lib.logs.init(parser.prog, args)

    worker = EditorialWorker(args)
    worker.run()


class EditorialWorker:
    """
    AI Editorial Worker that processes jobs from Redis queue.

    Features:
    - Uses logged-in user's auth_token for API calls (no credential prompts)
    - Complete 3-prompt editorial generation pipeline
    - Multi-language support (English, Spanish, Portuguese)
    - AC solution verification and enhancement
    - Website publishing with error handling
    - Comprehensive job status tracking
    """

    def __init__(self, args: argparse.Namespace) -> None:
        self.worker_id = args.worker_id
        self.test_mode = args.test_mode

        # Components are now available at module level
        # No need to store classes in instance variables

        # Initialize configuration manager
        self.config_manager = ConfigManager()
        self.config = self.config_manager.load_ai_config()

        # Initialize Redis connection using environment variables (like
        # cronjobs)
        redis_config = {
            'host': os.getenv('REDIS_HOST', 'localhost'),
            'port': int(os.getenv('REDIS_PORT', '6379')),
            'password': os.getenv('REDIS_PASSWORD', None),
            'db': int(os.getenv('REDIS_DB', '0')),
            'timeout': int(os.getenv('REDIS_TIMEOUT', '30'))
        }

        # Redis client is now imported at module level
        self.redis_client = RedisJobClient(redis_config)

        # Load all prompts for editorial generation
        ai_config = self.config_manager.load_ai_config()
        prompt_configs = ai_config.get('prompts', {})

        self.prompts = {}
        for prompt_type, prompt_path in prompt_configs.items():
            prompt_file = os.path.join(
                os.path.dirname(__file__), prompt_path
            )
            template = self.config_manager.load_prompt_template(prompt_file)
            self.prompts[prompt_type] = template

        # Verify we have at least one LLM provider configured (following
        # cronjob pattern like lib.db.py)
        deepseek_key = self._load_api_key_from_config(
            'ai_deepseek', 'api_key') or os.getenv('DEEPSEEK_API_KEY', '')
        openai_key = self._load_api_key_from_config(
            'ai_openai', 'api_key') or os.getenv('OPENAI_API_KEY', '')

        if deepseek_key:
            logging.info('Using LLM provider: deepseek')
        elif openai_key:
            logging.info('Using LLM provider: openai')
        else:
            logging.warning(
                'No LLM provider configured - set DEEPSEEK_API_KEY or '
                'OPENAI_API_KEY environment variable, or configure in '
                '~/.my.cnf')

        # Initialize API client attribute for job processing
        self._current_api_client = None

        logging.info('AI Editorial Worker %s initialized', self.worker_id)

    def _load_api_key_from_config(
        self,
        section: str,
        key: str) -> Optional[str]:
        """Load API key from config file using the same pattern as lib.db.py.

        Follows omegaUp cronjob pattern:
        1. Try ~/.my.cnf file first (production)
        2. Strip quotes like database credentials
        3. Support both INI sections and raw key content for SOPS secrets
        4. Return None if not found (fallback to env vars)
        """
        # Use same config file path as database credentials
        config_file_path = os.path.join(os.getenv('HOME', '.'), '.my.cnf')

        if not os.path.isfile(config_file_path):
            return None

        try:
            # First try to read as INI file
            config = configparser.ConfigParser()
            config.read(config_file_path)

            # Traditional INI section approach [ai_deepseek]
            if section in config and key in config[section]:
                api_key = config[section][key].strip("'\"")
                logging.info(
                    'Loaded %s API key from %s section', section,
                    config_file_path)
                return api_key

            # Try direct key access for SOPS-style secrets
            direct_key_mapping = {
                'ai_deepseek': 'deepseek',
                'ai_openai': 'openai'
            }

            if section in direct_key_mapping:
                direct_key = direct_key_mapping[section]
                # Try DEFAULT section or any section that has the direct key
                for section_name in config.sections() + ['DEFAULT']:
                    if (section_name in config and
                        direct_key in config[section_name]):
                        api_key = config[section_name][direct_key].strip("'\"")
                        logging.info(
                            'Loaded %s API key from %s as direct key',
                            section, config_file_path)
                        return api_key

        except configparser.Error:
            # If INI parsing fails, try reading as raw content (SOPS mount)
            try:
                with open(config_file_path, 'r', encoding='utf-8') as f:
                    content = f.read().strip()
                    # Check if this looks like an API key for the requested
                    # section
                    if section == 'ai_deepseek' and (
                        content.startswith('sk-') or
                        content.startswith('deepseek-')):
                        logging.info(
                            'Loaded %s API key from raw content in %s',
                            section, config_file_path)
                        return content.strip("'\"")
                    if section == 'ai_openai' and content.startswith('sk-'):
                        logging.info(
                            'Loaded %s API key from raw content in %s',
                            section, config_file_path)
                        return content.strip("'\"")
            except (OSError, IOError) as e:
                logging.warning(
                    'Error reading raw content from %s: %s',
                    config_file_path, e)
        except (OSError, IOError) as e:
            logging.warning(
                'Error reading config file %s: %s', config_file_path, e)

        return None

    def run(self) -> None:
        """Main worker loop - processes jobs from Redis queue."""
        logging.info('AI Editorial Worker %s started', self.worker_id)

        while True:
            try:
                self.process_jobs()
            except KeyboardInterrupt:
                logging.info('Worker shutting down...')
                break
            except (ImportError, AttributeError, TypeError, ValueError) as e:
                logging.exception('Worker error occurred: %s', e)
                time.sleep(5)  # Brief pause before retry

    def process_jobs(self) -> None:
        """
        Process jobs from Redis queue.

        Supports multiple queue priorities:
        - editorial_jobs_user: High priority (interactive users)
        - editorial_jobs_batch: Low priority (batch processing)
        """
        # Get job from Redis queue using RedisJobClient
        queue_result = self.redis_client.poll_job_queue(timeout=30)

        if queue_result:
            try:
                queue_name, job_json = queue_result
                job = self.redis_client.parse_job_data(job_json)
                job_id = job.get("job_id", "unknown")
                logging.info(
                    'Worker %s processing job %s from %s',
                    self.worker_id, job_id, queue_name)
                self.handle_editorial_job(job)
            except (KeyboardInterrupt, SystemExit):
                logging.info('Job processing interrupted')
                raise
            except (ImportError, AttributeError, TypeError, ValueError,
                    KeyError, ConnectionError) as e:
                logging.exception('Error processing job: %s', e)

    def _validate_job_parameters(self,
                                 job_id: str,
                                 problem_alias: Optional[str],
                                 auth_token: Optional[str]) -> tuple[bool,
                                                                     str,
                                                                     str]:
        """Validate required job parameters and return validated values."""
        if not problem_alias:
            logging.error('Job %s: Missing problem_alias', job_id)
            self._update_job_status(job_id, 'failed', 'Missing problem_alias')
            return False, '', ''

        if not auth_token:
            logging.error('Job %s: Missing auth_token', job_id)
            self._update_job_status(
                job_id, 'failed', 'Missing authentication token')
            return False, '', ''

        # Validate auth token format (basic security check)
        if not self._validate_auth_token_format(auth_token):
            logging.error('Job %s: Invalid auth_token format', job_id)
            self._update_job_status(
                job_id, 'failed', 'Invalid authentication token format')
            return False, '', ''

        return True, str(problem_alias), str(auth_token)

    def _validate_auth_token_format(self, auth_token: str) -> bool:
        """Validate auth token format for basic security."""
        if len(auth_token) < 10:
            return False
        if len(auth_token) > 200:  # Reasonable upper limit
            return False
        # Check for basic alphanumeric + common auth token characters
        if not re.match(r'^[a-zA-Z0-9_\-+=/.]+$', auth_token):
            return False
        return True

    def _initialize_components(
        self, auth_token: str) -> Tuple[Any, Any, Any, Any]:
        """Initialize all components needed for editorial generation."""
        # Initialize API client with user's auth token
        # Default to production, override for local development
        base_url = os.getenv('OMEGAUP_BASE_URL', 'https://omegaup.com')
        api_client = OmegaUpAPIClient(auth_token=auth_token, base_url=base_url)

        # Test API connection with auth token (basic validation)
        try:
            # This will validate the auth token works for API calls
            logging.info('Testing API authentication for job processing')
            # We'll validate this when we actually make API calls
        except Exception as e:
            logging.error('Auth token validation failed: %s', str(e))
            raise ValueError(f'Authentication failed: {str(e)}') from e

        # Initialize components with proper dependencies
        solution_handler = SolutionHandler(self.config_manager, api_client)
        website_uploader = WebsiteUploader(self.config_manager, api_client)

        # Get LLM configuration (following cronjob pattern)
        # Primary: DeepSeek, Fallback: OpenAI
        deepseek_key = self._load_api_key_from_config(
            'ai_deepseek', 'api_key') or os.getenv('DEEPSEEK_API_KEY', '')
        openai_key = self._load_api_key_from_config(
            'ai_openai', 'api_key') or os.getenv('OPENAI_API_KEY', '')
        if deepseek_key:
            llm_config = {
                'provider': 'deepseek',
                'api_key': deepseek_key,
                'model': 'deepseek-chat',
                'max_tokens': 2000,
                'temperature': 0.7
            }
            logging.info('Using DeepSeek as primary LLM provider')
        elif openai_key:
            llm_config = {
                'provider': 'openai',
                'api_key': openai_key,
                'model': 'gpt-4o',
                'max_tokens': 2000,
                'temperature': 0.7
            }
            logging.info('Using OpenAI as fallback LLM provider')
        else:
            raise ValueError(
                'No AI provider configured. Set DEEPSEEK_API_KEY or '
                'OPENAI_API_KEY environment variable, or configure in '
                '~/.my.cnf'
            )
        editorial_generator = EditorialGenerator({
            'llm_config': llm_config,
            'prompts': self.prompts,
            'redis_client': self.redis_client,
            'api_client': api_client,
            'full_config': self.config
        })

        return (api_client, solution_handler, website_uploader,
                editorial_generator)

    def _find_and_verify_solution(self, job_id: str, problem_alias: str,
                                  solution_handler: Any) -> None:
        """Find and verify AC solution for the problem."""
        logging.info('Job %s: Finding AC solution', job_id)
        ac_solution_source, ac_language = (
            solution_handler.get_reference_ac_solution(problem_alias))

        if ac_solution_source:
            # Verify solution works
            logging.info('Job %s: Verifying AC solution', job_id)
            verification_success, verification_msg = (
                solution_handler.verify_solution_with_retry(
                    problem_alias, ac_language, ac_solution_source,
                    max_attempts=2))

            if not verification_success:
                logging.warning(
                    'Job %s: AC solution verification failed: %s',
                    job_id, verification_msg)
        else:
            logging.warning('Job %s: No AC solution found', job_id)

    def _publish_editorial(self, job_id: str, problem_alias: str,
                           editorial_result: Dict[str, Any],
                           website_uploader: Any) -> Tuple[bool,
                                                           Dict[str, Any]]:
        """Publish editorial to website and return success status."""
        logging.info('Job %s: Publishing editorial to website', job_id)
        editorials = editorial_result.get('editorials', {})

        if editorials:
            upload_results = website_uploader.upload_with_validation(
                problem_alias, editorials)
            upload_success = any(upload_results.values())

            if upload_success:
                logging.info(
                    'Job %s: Editorial published successfully', job_id)
            else:
                logging.error('Job %s: Failed to publish editorial', job_id)
        else:
            upload_success = False
            upload_results = {}
            logging.error('Job %s: No editorials generated', job_id)

        return upload_success, upload_results

    def handle_editorial_job(self, job: Dict[str, Any]) -> None:
        """Handle a single editorial generation job."""
        job_id = job.get('job_id', 'unknown')
        problem_alias = job.get('problem_alias')
        auth_token = job.get('auth_token')

        # Validate required job parameters
        is_valid, validated_alias, validated_token = (
            self._validate_job_parameters(job_id, problem_alias, auth_token))
        if not is_valid:
            return

        # Use validated parameters (guaranteed to be strings)
        problem_alias = validated_alias
        auth_token = validated_token

        try:
            # Update job status to processing
            self._update_job_status(job_id, 'processing', None, {
                'worker_id': self.worker_id,
                'started_at': str(time.time())
            })

            # Initialize components for editorial generation
            components = self._initialize_components(auth_token)
            (api_client, solution_handler, website_uploader,
             editorial_generator) = components

            # Store API client for database updates
            self._current_api_client = api_client

            # Step 1: Get problem details
            logging.info(
                'Job %s: Fetching problem details for %s',
                job_id,
                problem_alias)
            # problem_data = api_client.get_problem_details(problem_alias)  #
            # Reserved for future use

            # Step 2: Find AC solution (verification disabled)
            logging.info('Job %s: Finding AC solution for reference', job_id)
            _ = solution_handler.find_working_solution(problem_alias)

            # Step 3: Generate complete editorial
            logging.info('Job %s: Generating editorial', job_id)
            editorial_result = editorial_generator.generate_complete_editorial(
                problem_alias=problem_alias,
                job_id=job_id
            )

            if not editorial_result.get('success'):
                error_msg = editorial_result.get(
                    'error', 'Editorial generation failed')
                self._update_job_status(job_id, 'failed', error_msg)
                return

            # Step 4: Publish to website
            upload_success, upload_results = self._publish_editorial(
                job_id, problem_alias, editorial_result, website_uploader)

            # Step 5: Update job status to completed
            editorials = editorial_result.get('editorials', {})
            result_data = {
                'editorial_en': editorials.get('en', ''),
                'editorial_es': editorials.get('es', ''),
                'editorial_pt': editorials.get('pt', ''),
                'upload_results': str(upload_results if editorials else {}),
                'verification_result': self._extract_verification_verdict(
                    editorial_result.get('solution_verification', {})),
                'completed_at': str(time.time()),
                'success': str(upload_success)
            }

            final_status = 'completed' if upload_success else 'failed'
            final_error = (None if upload_success else
                           'Failed to publish editorial to website')

            self._update_job_status(
                job_id, final_status, final_error, result_data)
            logging.info(
                'Job %s: Processing completed with status: %s',
                job_id, final_status)

        except (ImportError, AttributeError, TypeError, ValueError) as e:
            error_msg = f'Editorial generation failed: {str(e)}'
            logging.exception('Job %s: %s', job_id, error_msg)

            # Store detailed error information
            error_data = {
                'error_type': type(e).__name__,
                'error_message': str(e),
                'traceback': traceback.format_exc(),
                'failed_at': str(time.time())
            }

            self._update_job_status(job_id, 'failed', error_msg, error_data)

        finally:
            # Clean up API client reference to avoid memory leaks
            if hasattr(self, '_current_api_client'):
                delattr(self, '_current_api_client')

    def _update_job_status(self,
                           job_id: str,
                           status: str,
                           error: Optional[str] = None,
                           extra_data: Optional[Dict[str,
                                                     Any]] = None) -> None:
        """Update job status in Redis and Database."""
        try:
            # 1. Update Redis (existing functionality)
            update_data = {
                'status': status,
                'updated_at': str(time.time()),
                'worker_id': str(self.worker_id)
            }

            if error:
                update_data['error'] = error

            if extra_data:
                # Convert all values to strings for Redis storage
                for key, value in extra_data.items():
                    update_data[key] = str(value) if value is not None else ''

            self.redis_client.set_job_status(job_id, update_data)

            # 2. Update Database (new functionality)
            self._update_database_status(job_id, status, error, extra_data)

        except (ConnectionError, TypeError, ValueError) as e:
            logging.exception(
                'Failed to update job status for %s: %s', job_id, e)

    def _extract_verification_verdict(self, verification_data: Any) -> str:
        """
        Extract verdict string from verification result for database storage.

        Args:
            verification_data: Verification result dict or other data

        Returns:
            Short verdict string (e.g., "AC", "WA") suitable for database
            storage
        """
        if not verification_data:
            return ''

        if isinstance(verification_data, dict):
            verdict = verification_data.get('verdict', '')
            return str(verdict) if verdict else ''

        if isinstance(verification_data, str):
            return verification_data[:10]  # Limit length for database

        return str(verification_data)[:10]

    def _update_database_status(
            self,
            job_id: str,
            status: str,
            error: Optional[str] = None,
            extra_data: Optional[Dict[str, Any]] = None) -> None:
        """Update job status in omegaUp database via API."""
        try:
            # Extract editorials from extra_data if available
            editorials = None
            validation_verdict = None

            if extra_data:
                editorial_en = extra_data.get('editorial_en', '').strip()
                editorial_es = extra_data.get('editorial_es', '').strip()
                editorial_pt = extra_data.get('editorial_pt', '').strip()

                # Create editorials dict if we have content
                if editorial_en or editorial_es or editorial_pt:
                    editorials = {}
                    if editorial_en:
                        editorials['en'] = editorial_en
                    if editorial_es:
                        editorials['es'] = editorial_es
                    if editorial_pt:
                        editorials['pt'] = editorial_pt

                validation_verdict = extra_data.get(
                    'verification_result', '').strip()
                if not validation_verdict:
                    validation_verdict = None

            # Get API client with user's auth token
            api_client = getattr(self, '_current_api_client', None)
            if not api_client:
                logging.warning(
                    'No API client available for database update of job %s',
                    job_id)
                return

            # Update database via API
            success = api_client.update_job_status(
                job_id=job_id,
                status=status,
                editorials=editorials,
                error_message=error,
                validation_verdict=validation_verdict
            )

            if success:
                logging.info(
                    'Successfully updated database status for job %s: %s',
                    job_id, status)
            else:
                logging.warning(
                    'Failed to update database status for job %s', job_id)

        except (ConnectionError, TypeError, ValueError, KeyError,
                AttributeError) as e:
            # Don't fail the entire job if database update fails
            # Redis update is sufficient for worker operation
            logging.warning(
                'Database status update failed for job %s: %s', job_id, e)


if __name__ == '__main__':
    main()
