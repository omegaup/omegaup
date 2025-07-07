#!/usr/bin/env python3
"""
AI Editorial Worker for omegaUp

Single worker that polls Redis queue for editorial generation jobs.
Generates English editorials using GPT-4o and stores results in Redis.
"""

import argparse
import configparser
import json
import logging
import os
import sys
import time
from typing import Optional, Dict, Any

# API-only worker - no lib.db or lib.logs needed

# Import Redis and OpenAI
try:
    import redis
    import openai
    from openai import OpenAI
except ImportError as e:
    print(f"Missing required dependency: {e}")
    print("Please install: pip install openai redis")
    sys.exit(1)


def load_ai_config(config_file: str = None) -> Dict[str, Any]:
    """Load AI configuration from JSON file."""
    if config_file is None:
        config_file = os.path.join(os.path.dirname(__file__), 'ai_config.json')

    try:
        with open(config_file, 'r') as f:
            return json.load(f)
    except FileNotFoundError:
        logging.warning(
            f"AI config file not found: {config_file}, using defaults")
        return {
            "openai": {
                "model": "gpt-4",
                "max_tokens": 2000,
                "temperature": 0.3,
                "top_p": 1.0,
                "frequency_penalty": 0.0,
                "presence_penalty": 0.0,
                "timeout": 60
            },
            "system_message": "You are an expert competitive programming assistant who generates clear, educational editorials without any code.",
            "prompts": {
                "editorial_file": "prompts/editorial.txt"
            }
        }
    except json.JSONDecodeError as e:
        logging.error(f"Invalid JSON in AI config file: {e}")
        raise


def load_prompt_template(prompt_file: str) -> str:
    """Load prompt template from file."""
    try:
        with open(prompt_file, 'r') as f:
            return f.read().strip()
    except FileNotFoundError:
        logging.error(f"Prompt file not found: {prompt_file}")
        raise
    except Exception as e:
        logging.error(f"Error loading prompt file: {e}")
        raise


class EditorialWorker:
    """AI Editorial Worker that processes jobs from Redis queue."""

    def __init__(self, args: argparse.Namespace):
        self.worker_id = args.worker_id
        self.args = args

        # Get configuration using new patterns (following lib.db approach)
        self.openai_config = get_openai_config(args)
        self.redis_config = get_redis_config(args)

        # Load AI configuration and prompts
        self.ai_config = load_ai_config()
        self.prompt_template = self.load_prompt_template()

        self.setup_logging()
        self.setup_redis()
        self.setup_openai()

    def setup_logging(self) -> None:
        """Setup logging configuration."""
        logging.info(f'Initializing AI Editorial Worker {self.worker_id}')

    def setup_redis(self) -> None:
        """Setup Redis connection using configuration from arguments/config/env."""
        try:
            # Use Redis configuration from command line args/config/env
            self.redis_client = redis.Redis(
                host=self.redis_config['host'],
                port=self.redis_config['port'],
                decode_responses=True,
                password=self.redis_config['password']
            )

            # Test connection
            self.redis_client.ping()
            logging.info(
                f'Connected to Redis at {
                    self.redis_config["host"]}:{
                    self.redis_config["port"]}')

        except Exception as e:
            logging.error(f'Redis connection error: {e}')
            raise

    def setup_openai(self) -> None:
        """Setup OpenAI client using configuration from arguments/config/env."""
        try:
            self.openai_client = OpenAI(api_key=self.openai_config['api_key'])
            self.openai_model = self.openai_config['model']
            logging.info(
                f'OpenAI client initialized (model: {
                    self.openai_model})')

        except Exception as e:
            logging.error(f'OpenAI setup error: {e}')
            raise

    def run(self) -> None:
        """Main worker loop - poll Redis queue and process jobs."""
        logging.info(f'AI Editorial Worker {self.worker_id} started')

        while True:
            try:
                self.poll_and_process_jobs()
            except KeyboardInterrupt:
                logging.info('Worker interrupted by user')
                break
            except Exception as e:
                logging.exception(f'Worker error: {e}')
                time.sleep(5)  # Brief pause before retry

        logging.info(f'AI Editorial Worker {self.worker_id} stopped')

    def poll_and_process_jobs(self) -> None:
        """Poll Redis queue and process available jobs."""
        # Block waiting for job from Redis queue (30 second timeout)
        job_data = self.redis_client.brpop(
            ['editorial_jobs_queue'], timeout=30)

        if job_data:
            queue_name, job_json = job_data
            logging.info(f'Received job from queue: {queue_name}')

            try:
                job = json.loads(job_json)
                self.handle_editorial_job(job)
            except json.JSONDecodeError as e:
                logging.error(f'Invalid job JSON: {e}')
            except Exception as e:
                logging.exception(f'Error processing job: {e}')

    def handle_editorial_job(self, job: Dict[str, Any]) -> None:
        """Handle a single editorial generation job."""
        job_id = job.get('job_id', 'unknown')
        problem_alias = job.get('problem_alias', 'unknown')

        logging.info(
            f'Processing editorial job {job_id} for problem {problem_alias}')

        try:
            # Set initial job status
            self.set_job_status(job_id, {
                'status': 'processing',
                'worker_id': self.worker_id,
                'problem_alias': problem_alias,
                'started_at': time.time()
            })

            # Generate editorial
            editorial = self.generate_editorial(problem_alias)

            if editorial:
                # Store successful result
                self.set_job_status(job_id, {
                    'status': 'completed',
                    'editorial_en': editorial,
                    'completed_at': time.time(),
                    'worker_id': self.worker_id
                })
                logging.info(f'Successfully completed job {job_id}')
            else:
                # Store failure result
                self.set_job_status(job_id, {
                    'status': 'failed',
                    'error': 'Failed to generate editorial',
                    'failed_at': time.time(),
                    'worker_id': self.worker_id
                })
                logging.error(f'Failed to generate editorial for job {job_id}')

        except openai.RateLimitError as e:
            logging.warning(f'Rate limit hit for job {job_id}: {e}')
            self.set_job_status(job_id, {
                'status': 'rate_limited',
                'error': str(e),
                'retry_after': time.time() + 60,  # Retry in 1 minute
                'worker_id': self.worker_id
            })

        except openai.APIError as e:
            logging.error(f'OpenAI API error for job {job_id}: {e}')
            self.set_job_status(job_id, {
                'status': 'failed',
                'error': f'OpenAI API error: {str(e)}',
                'failed_at': time.time(),
                'worker_id': self.worker_id
            })

        except Exception as e:
            logging.exception(f'Unexpected error processing job {job_id}')
            self.set_job_status(job_id, {
                'status': 'failed',
                'error': f'Unexpected error: {str(e)}',
                'failed_at': time.time(),
                'worker_id': self.worker_id
            })

    def set_job_status(self, job_id: str, status_data: Dict[str, Any]) -> None:
        """Set job status in Redis."""
        try:
            self.redis_client.hset(f'job:{job_id}', mapping=status_data)
        except Exception as e:
            logging.error(f'Failed to set job status for {job_id}: {e}')

    def generate_editorial(self, problem_alias: str) -> Optional[str]:
        """Generate editorial for a problem using GPT-4o."""
        try:
            # For PR #1, we use a simple problem alias-based prompt
            # In PR #2, this will be replaced with actual API calls to get
            # problem details
            logging.info(f'Generating editorial for problem: {problem_alias}')

            # Use prompt template from file
            prompt = self.prompt_template.format(problem_alias=problem_alias)

            # Get AI parameters from config
            ai_params = self.ai_config['openai']

            # Call OpenAI API with configuration from file
            response = self.openai_client.chat.completions.create(
                model=ai_params.get('model', self.openai_model),
                messages=[
                    {
                        "role": "system",
                        "content": self.ai_config['system_message']
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                max_tokens=ai_params.get('max_tokens', 2000),
                temperature=ai_params.get('temperature', 0.3),
                top_p=ai_params.get('top_p', 1.0),
                frequency_penalty=ai_params.get('frequency_penalty', 0.0),
                presence_penalty=ai_params.get('presence_penalty', 0.0),
                timeout=ai_params.get('timeout', 60)
            )

            # Extract editorial content
            editorial = response.choices[0].message.content

            if editorial and isinstance(editorial, str):
                editorial = editorial.strip()
                logging.info(f'Generated editorial ({len(editorial)} chars)')
                self.log_editorial(editorial)
                return editorial
            else:
                logging.error('Empty or invalid editorial response')
                return None

        except openai.RateLimitError as e:
            logging.warning(f'Rate limit error: {e}')
            raise  # Re-raise to be handled by caller

        except openai.APIError as e:
            logging.error(f'OpenAI API error: {e}')
            raise  # Re-raise to be handled by caller

        except Exception as e:
            logging.exception(f'Unexpected error generating editorial: {e}')
            return None

    def log_editorial(self, editorial: str) -> None:
        """Log the generated editorial for debugging."""
        logging.info("=" * 60)
        logging.info("*** GENERATED EDITORIAL ***")
        logging.info("=" * 60)
        for i, line in enumerate(editorial.split('\n'), 1):
            logging.info("%3d | %s", i, line)
        logging.info("=" * 60)

    def load_prompt_template(self) -> str:
        """Load the editorial prompt template."""
        prompt_file = self.ai_config['prompts']['editorial_file']

        # Handle relative path from worker directory
        if not os.path.isabs(prompt_file):
            prompt_file = os.path.join(os.path.dirname(__file__), prompt_file)

        return load_prompt_template(prompt_file)


def default_openai_config_file_path() -> Optional[str]:
    """Try to autodetect the OpenAI config file path."""
    for candidate_path in (
            # ~/.openai.conf
            os.path.join(os.getenv('HOME') or '.', '.openai.conf'),
            # ~/.config/openai/config
            os.path.join(
                os.getenv('HOME') or '.',
                '.config',
                'openai',
                'config'),
    ):
        if os.path.isfile(candidate_path):
            return candidate_path
    return None


def configure_openai_parser(parser: argparse.ArgumentParser) -> None:
    """Add OpenAI-related arguments to parser (following lib.db pattern)."""
    openai_args = parser.add_argument_group('OpenAI Configuration')

    openai_args.add_argument(
        '--openai-config-file',
        type=str,
        default=default_openai_config_file_path(),
        help='Config file that stores OpenAI credentials'
    )

    openai_args.add_argument(
        '--openai-api-key',
        type=str,
        help='OpenAI API key'
    )

    openai_args.add_argument(
        '--openai-model',
        type=str,
        default='gpt-4',
        help='OpenAI model to use for editorial generation'
    )


def configure_redis_parser(parser: argparse.ArgumentParser) -> None:
    """Add Redis-related arguments to parser (following lib.db pattern)."""
    redis_args = parser.add_argument_group('Redis Configuration')

    redis_args.add_argument(
        '--redis-config-file',
        type=str,
        default=default_openai_config_file_path(),  # Can share same config file
        help='Config file that stores Redis credentials'
    )

    redis_args.add_argument(
        '--redis-host',
        type=str,
        default='redis',
        help='Redis server hostname'
    )

    redis_args.add_argument(
        '--redis-port',
        type=int,
        default=6379,
        help='Redis server port'
    )

    redis_args.add_argument(
        '--redis-password',
        type=str,
        help='Redis server password'
    )


def get_openai_config(args: argparse.Namespace) -> Dict[str, str]:
    """Get OpenAI configuration from arguments, config file, or environment (following lib.db pattern)."""
    api_key = args.openai_api_key
    model = args.openai_model

    # Try to read from config file if API key not provided via CLI
    if (api_key is None and args.openai_config_file
            and os.path.isfile(args.openai_config_file)):
        config = configparser.ConfigParser()
        config.read(args.openai_config_file)

        if 'openai' in config:
            # Handle quoted configuration entries (like in lib.db)
            if 'api_key' in config['openai']:
                api_key = config['openai']['api_key'].strip("'\"")
            if 'model' in config['openai']:
                model = config['openai']['model'].strip("'\"")

    # Fallback to environment variable
    if api_key is None:
        api_key = os.getenv('OPENAI_API_KEY')

    # Validate required configuration
    if not api_key:
        raise ValueError(
            'OpenAI API key is required. Provide it via:\n'
            '  --openai-api-key command line argument, or\n'
            '  [openai] section in config file, or\n'
            '  OPENAI_API_KEY environment variable'
        )

    return {
        'api_key': api_key,
        'model': model
    }


def get_redis_config(args: argparse.Namespace) -> Dict[str, Any]:
    """Get Redis configuration from arguments, config file, or environment (following lib.db pattern)."""
    host = args.redis_host
    port = args.redis_port
    password = args.redis_password

    # Try to read from config file if not provided via CLI
    if (args.redis_config_file and os.path.isfile(args.redis_config_file)):
        config = configparser.ConfigParser()
        config.read(args.redis_config_file)

        if 'redis' in config:
            # Handle quoted configuration entries (like in lib.db)
            if 'host' in config['redis'] and not args.redis_host:
                host = config['redis']['host'].strip("'\"")
            if 'port' in config['redis'] and args.redis_port == 6379:  # Default port
                port = int(config['redis']['port'].strip("'\""))
            if 'password' in config['redis'] and password is None:
                password = config['redis']['password'].strip("'\"")

    # Fallback to environment variables
    if host == 'redis':  # Still default
        host = os.getenv('REDIS_HOST', 'redis')
    if port == 6379:  # Still default
        port = int(os.getenv('REDIS_PORT', '6379'))
    if password is None:
        password = os.getenv('REDIS_PASSWORD', 'redis')

    # Validate required configuration
    if not host:
        raise ValueError('Redis host is required')
    if not port or port <= 0:
        raise ValueError('Redis port must be a positive integer')

    return {
        'host': host,
        'port': port,
        'password': password
    }


def main() -> None:
    """Main entry point following omegaUp cron script patterns."""
    parser = argparse.ArgumentParser(
        description='AI Editorial Worker for omegaUp'
    )

    # No database or logging arguments needed - API-only worker

    # Add worker-specific arguments
    parser.add_argument(
        '--worker-id',
        type=int,
        default=0,
        help='Worker ID for identification'
    )

    # Add OpenAI-related arguments
    configure_openai_parser(parser)

    # Add Redis-related arguments
    configure_redis_parser(parser)

    args = parser.parse_args()

    # Initialize basic logging
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s:%(filename)s:%(message)s'
    )

    try:
        # Create and run worker
        worker = EditorialWorker(args)
        worker.run()

    except KeyboardInterrupt:
        logging.info('Interrupted by user')
    except Exception as e:
        logging.exception(f'Fatal error: {e}')
        sys.exit(1)


if __name__ == '__main__':
    main()
