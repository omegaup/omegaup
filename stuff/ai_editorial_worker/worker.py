#!/usr/bin/env python3
"""
AI Editorial Worker for omegaUp

Single worker that polls Redis queue for editorial generation jobs.
Generates English editorials using GPT-4o and stores results in Redis.
"""

import argparse
import json
import logging
import os
import sys
import time
from typing import Optional, Dict, Any

# Add parent directory to path for lib imports (same pattern as cron scripts)
sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))

import lib.db   # pylint: disable=wrong-import-position
import lib.logs  # pylint: disable=wrong-import-position

# Import Redis and OpenAI
try:
    import redis
    import openai
    from openai import OpenAI
except ImportError as e:
    print(f"Missing required dependency: {e}")
    print("Please install: pip install openai redis")
    sys.exit(1)


class EditorialWorker:
    """Single AI Editorial Worker for generating editorials from Redis queue."""

    def __init__(self, args: argparse.Namespace):
        """Initialize worker with configuration."""
        self.worker_id = getattr(args, 'worker_id', 0)
        self.setup_logging()
        self.setup_redis()
        self.setup_openai()
        
        logging.info(f'AI Editorial Worker {self.worker_id} initialized')

    def setup_logging(self) -> None:
        """Setup logging configuration."""
        # Logging already initialized by lib.logs.init() in main()
        pass

    def setup_redis(self) -> None:
        """Setup Redis connection."""
        try:
            redis_host = os.getenv('REDIS_HOST', 'redis')
            redis_port = int(os.getenv('REDIS_PORT', '6379'))
            redis_pass = os.getenv('REDIS_PASS', 'redis')
            
            self.redis_client = redis.Redis(
                host=redis_host, 
                port=redis_port, 
                password=redis_pass,
                decode_responses=True,
                socket_connect_timeout=10,
                socket_timeout=10
            )
            
            # Test connection
            self.redis_client.ping()
            logging.info(f'Connected to Redis at {redis_host}:{redis_port}')
            
        except redis.ConnectionError as e:
            logging.error(f'Failed to connect to Redis: {e}')
            raise
        except Exception as e:
            logging.error(f'Redis setup error: {e}')
            raise

    def setup_openai(self) -> None:
        """Setup OpenAI client."""
        try:
            api_key = os.getenv('OPENAI_API_KEY')
            if not api_key:
                raise ValueError('OPENAI_API_KEY environment variable is required')
            
            self.openai_client = OpenAI(api_key=api_key)
            logging.info('OpenAI client initialized')
            
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
        job_data = self.redis_client.brpop(['editorial_jobs_queue'], timeout=30)
        
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
        
        logging.info(f'Processing editorial job {job_id} for problem {problem_alias}')
        
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
            # In PR #2, this will be replaced with actual API calls to get problem details
            logging.info(f'Generating editorial for problem: {problem_alias}')
            
            # Generate editorial using OpenAI API
            prompt = f"""You are an expert competitive programming editorial writer.

Generate a comprehensive editorial for the omegaUp problem with alias: {problem_alias}

Please create a general editorial that covers:

1. **Problem Understanding**: Explain what the problem is likely asking for based on the alias
2. **Key Insights**: Identify potential algorithmic approaches that might be needed
3. **Solution Strategy**: Describe a step-by-step approach to solve the problem
4. **Implementation Notes**: Important considerations for coding the solution
5. **Complexity Analysis**: Time and space complexity of the solution

Requirements:
- Write in clear, educational English
- NO CODE in the editorial - only algorithmic explanation
- Make it comprehensive but accessible
- Focus on teaching the problem-solving approach

Generate the editorial:"""

            # Call OpenAI API
            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {
                        "role": "system",
                        "content": (
                            "You are an expert competitive programming assistant "
                            "who generates clear, educational editorials without any code."
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


def main() -> None:
    """Main entry point following omegaUp cron script patterns."""
    parser = argparse.ArgumentParser(
        description='AI Editorial Worker for omegaUp'
    )
    
    # Add database and logging arguments (following existing pattern)
    lib.db.configure_parser(parser)
    lib.logs.configure_parser(parser)
    
    # Add worker-specific arguments
    parser.add_argument(
        '--worker-id', 
        type=int, 
        default=0,
        help='Worker ID for identification'
    )
    
    args = parser.parse_args()
    
    # Initialize logging (following existing pattern)
    lib.logs.init(parser.prog, args)
    
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