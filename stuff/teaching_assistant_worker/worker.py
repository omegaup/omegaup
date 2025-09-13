#!/usr/bin/env python3

"""
Teaching Assistant Worker

This worker processes teaching assistant jobs from Redis queue and provides
AI-powered feedback for student submissions.
"""

import argparse
import configparser
import json
import logging
import os
import sys
import time
from typing import Dict, Any, Optional

sys.path.append(os.path.join(os.path.dirname(__file__), '..'))

from teaching_assistant_worker.redis_client import RedisJobClient
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'teaching_assistant'))
from teaching_assistant_v2 import TeachingAssistantClient
try:
    sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
    from omegaup_api_client import OmegaUpAPIClient  # type: ignore
    
    try:
        import lib.db  # type: ignore
        import lib.logs  # type: ignore
    except (ImportError, AttributeError):
        lib = None
except ImportError:
    OmegaUpAPIClient = None
    lib = None


def main() -> None:
    """Main entry point for the Teaching Assistant Worker."""
    parser = argparse.ArgumentParser(description='Teaching Assistant Worker')
    
    if lib:
        lib.db.configure_parser(parser)
        lib.logs.configure_parser(parser)
    
    parser.add_argument('--worker-id', type=int, default=0,
                        help='Worker ID for multi-worker deployments')
    parser.add_argument('--test-mode', action='store_true',
                        help='Run in test mode with enhanced logging')

    args = parser.parse_args()
    
    if lib:
        lib.logs.init(parser.prog, args)

    worker = TeachingAssistantWorker(args)
    worker.run()


class TeachingAssistantWorker:
    """
    Teaching Assistant Worker that processes feedback jobs from Redis queue.

    Features:
    - Uses logged-in user's auth_token for API calls
    - AI-powered submission feedback generation
    - Multi-language support
    - Integration with existing submission feedback system
    - Comprehensive job status tracking
    """

    def __init__(self, args: argparse.Namespace) -> None:
        self.worker_id = args.worker_id
        self.test_mode = args.test_mode

        redis_config = {
            'host': os.getenv('REDIS_HOST', 'localhost'),
            'port': int(os.getenv('REDIS_PORT', '6379')),
            'password': os.getenv('REDIS_PASSWORD', 'redis'),
            'db': int(os.getenv('REDIS_DB', '0')),
            'timeout': int(os.getenv('REDIS_TIMEOUT', '30'))
        }

        self.redis_client = RedisJobClient(redis_config)

        deepseek_key = self._load_api_key_from_config(
            'ai_deepseek', 'api_key') or os.getenv('DEEPSEEK_API_KEY', '')
        openai_key = self._load_api_key_from_config(
            'ai_openai', 'api_key') or os.getenv('OPENAI_API_KEY', '')
        omegaup_key = self._load_api_key_from_config(
            'ai_omegaup', 'api_key') or os.getenv('OMEGAUP_API_KEY', '')

        if deepseek_key:
            self.llm_provider = 'deepseek'
            self.api_key = deepseek_key
        elif openai_key:
            self.llm_provider = 'openai'
            self.api_key = openai_key
        elif omegaup_key:
            self.llm_provider = 'omegaup'
            self.api_key = omegaup_key
        else:
            raise RuntimeError('No LLM provider configured. Please set DEEPSEEK_API_KEY, OPENAI_API_KEY, or OMEGAUP_API_KEY environment variable or configure ~/.my.cnf')

        self._current_api_client = None
        self._current_bridge = None

        logging.info('Teaching Assistant Worker %s initialized with %s', 
                    self.worker_id, self.llm_provider)

    def _load_api_key_from_config(self, section: str, key: str) -> Optional[str]:
        """Load API key from ~/.my.cnf following the same pattern as db credentials."""
        config_file_path = os.path.join(os.getenv('HOME', '.'), '.my.cnf')

        if not os.path.isfile(config_file_path):
            return None

        try:
            config = configparser.ConfigParser()
            config.read(config_file_path)


            if section in config and key in config[section]:
                api_key = config[section][key].strip("'\"")
                logging.info('Loaded %s API key from %s section', section, config_file_path)
                return api_key

            direct_key_mapping = {
                'ai_deepseek': 'deepseek',
                'ai_openai': 'openai'
            }

            if section in direct_key_mapping:
                direct_key = direct_key_mapping[section]
                for section_name in config.sections() + ['DEFAULT']:
                    if (section_name in config and
                        direct_key in config[section_name]):
                        api_key = config[section_name][direct_key].strip("'\"")
                        logging.info('Loaded %s API key from %s as direct key',
                                    section, config_file_path)
                        return api_key

        except configparser.Error:
            try:
                with open(config_file_path, 'r', encoding='utf-8') as f:
                    content = f.read().strip()
                    if section == 'ai_deepseek' and (
                        content.startswith('sk-') or
                        content.startswith('deepseek-')):
                        logging.info('Loaded %s API key from raw content in %s',
                                    section, config_file_path)
                        return content.strip("'\"")
                    if section == 'ai_openai' and content.startswith('sk-'):
                        logging.info('Loaded %s API key from raw content in %s',
                                    section, config_file_path)
                        return content.strip("'\"")
            except (OSError, IOError) as e:
                logging.warning('Error reading raw content from %s: %s',
                               config_file_path, e)
        except (OSError, IOError) as e:
            logging.warning('Error reading config file %s: %s', config_file_path, e)

        return None

    def run(self) -> None:
        """Main worker loop - processes jobs from Redis queue."""
        logging.info('Teaching Assistant Worker %s started', self.worker_id)

        while True:
            try:
                self.process_jobs()
            except KeyboardInterrupt:
                logging.info('Worker shutting down...')
                break
            except Exception as e:
                logging.exception('Worker error occurred: %s', e)
                time.sleep(5)

    def process_jobs(self) -> None:
        """Process jobs from Redis queue."""
        queue_result = self.redis_client.poll_job_queue(timeout=5)

        if queue_result:
            try:
                queue_name, job_json = queue_result
                job = self.redis_client.parse_job_data(job_json)
                job_id = job.get("job_id", "unknown")
                logging.info('Processing job %s from queue %s', job_id, queue_name)

                if queue_name == 'teaching_assistant_jobs':
                    self.handle_teaching_assistant_job(job)
                elif queue_name in ['editorial_jobs_user', 'editorial_jobs_batch']:
                    logging.warning('Editorial jobs not supported by this worker. Skipping job %s', job_id)
                    self._update_job_status(job_id, 'failed', 'Editorial jobs not supported by teaching assistant worker')
                else:
                    logging.error('Unknown queue: %s for job %s', queue_name, job_id)
                    self._update_job_status(job_id, 'failed', f'Unknown queue: {queue_name}')

            except Exception as e:
                logging.exception('Error processing job: %s', e)
                if 'job_id' in locals():
                    self._update_job_status(
                        job_id, 'failed',
                        f'Job processing error: {str(e)}'
                    )

    def handle_teaching_assistant_job(self, job: Dict[str, Any]) -> None:
        """Handle a teaching assistant feedback job using the main client directly."""
        job_id = job.get('job_id')
        course_alias = job.get('course_alias')
        assignment_alias = job.get('assignment_alias')
        submission_id = job.get('run_id')
        student_name = job.get('student_name')
        language = job.get('language', 'English')
        ta_indicator = job.get('ta_feedback_indicator', 'AI-generated')
        
        if not job_id:
            logging.error('Missing job_id parameter')
            return
            
        if not course_alias or not assignment_alias:
            self._update_job_status(job_id, 'failed', 'Missing course_alias or assignment_alias parameter')
            return

        self._update_job_status(job_id, 'processing')
        
        try:
            username = job.get('username') or os.getenv('OMEGAUP_USERNAME')
            password = job.get('password') or os.getenv('OMEGAUP_PASSWORD')
            
            if not username or not password:
                raise ValueError("Username and password required for omegaUp authentication")
            
            ta_client = TeachingAssistantClient(username, password)
            
            if not ta_client.login():
                raise Exception("Failed to login to omegaUp")
            
            ta_client.initialize_llm(self.llm_provider, self.api_key)
            
            ta_client.process_submissions(
                course_alias=course_alias,
                assignment_alias=assignment_alias,
                language=language,
                ta_indicator=ta_indicator,
                skip_confirm=True,  # Always skip confirm in worker mode
                submission_id=submission_id,
                student_name=student_name
            )
            
            self._update_job_status(job_id, 'completed', f'Processed feedback for course {course_alias}')
            logging.info('Successfully completed TA job %s', job_id)

        except Exception as e:
            error_msg = f'TA job processing failed: {str(e)}'
            logging.error('Error in TA job %s: %s', job_id, error_msg)
            self._update_job_status(job_id, 'failed', error_msg)

    def _update_job_status(self, job_id: str, status: str, 
                          error: Optional[str] = None,
                          extra_data: Optional[Dict[str, Any]] = None) -> None:
        """Update job status in Redis."""

        status_data = {
            'status': status,
            'updated_at': time.time()
        }
        
        if error:
            status_data['error'] = error
            
        if extra_data:
            status_data.update(extra_data)
            
        self.redis_client.set_job_status(job_id, status_data)
        
        logging.info('Updated job %s status to %s', job_id, status)


if __name__ == '__main__':
    main()
