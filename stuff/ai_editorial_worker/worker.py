#!/usr/bin/env python3
"""
AI Editorial Worker for omegaUp

Single worker that polls Redis queue for editorial generation jobs.
Generates English editorials using LLM providers and stores results in Redis.
"""

import argparse
import logging
import sys
import time
from typing import Dict, Any

# Import our components
from redis_client import RedisJobClient
from config_manager import ConfigManager
from editorial_generator import EditorialGenerator


class EditorialWorker:
    """AI Editorial Worker that processes jobs from Redis queue."""

    def __init__(self, args: argparse.Namespace):
        """Initialize worker with configuration and components."""
        self.worker_id = args.worker_id
        self.args = args
        
        # Initialize configuration manager
        self.config_manager = ConfigManager()
        
        # Get configurations
        self.redis_config = self.config_manager.get_redis_config(args)
        self.openai_config = self.config_manager.get_openai_config(args)
        
        # Initialize components
        self.setup_logging()
        self.setup_components()

    def setup_logging(self) -> None:
        """Setup logging configuration."""
        logging.info('Initializing AI Editorial Worker %s', self.worker_id)

    def setup_components(self) -> None:
        """Setup Redis client and editorial generator."""
        try:
            # Initialize Redis client
            self.redis_client = RedisJobClient(self.redis_config)
            
            # Initialize editorial generator with 'gpt' provider for OpenAI
            self.editorial_generator = EditorialGenerator(
                provider='gpt',
                api_key=self.openai_config['api_key'],
                config_manager=self.config_manager
            )
            
            logging.info('All components initialized successfully')
            
        except Exception as e:
            logging.error('Component setup error: %s', e)
            raise

    def run(self) -> None:
        """Main worker loop - poll Redis queue and process jobs."""
        logging.info('AI Editorial Worker %s started', self.worker_id)

        while True:
            try:
                self.poll_and_process_jobs()
            except KeyboardInterrupt:
                logging.info('Worker interrupted by user')
                break
            except Exception as e:
                logging.exception('Worker error: %s', e)
                time.sleep(5)  # Brief pause before retry

        logging.info('AI Editorial Worker %s stopped', self.worker_id)

    def poll_and_process_jobs(self) -> None:
        """Poll Redis queue and process available jobs."""
        # Poll for jobs with 30 second timeout
        job_data = self.redis_client.poll_job_queue(timeout=30)

        if job_data:
            queue_name, job_json = job_data
            logging.info('Received job from queue: %s', queue_name)

            try:
                job = self.redis_client.parse_job_data(job_json)
                self.handle_editorial_job(job)
            except Exception as e:
                logging.exception('Error processing job: %s', e)

    def handle_editorial_job(self, job: Dict[str, Any]) -> None:
        """Handle a single editorial generation job."""
        job_id = job.get('job_id', 'unknown')
        problem_alias = job.get('problem_alias', 'unknown')

        logging.info(
            'Processing editorial job %s for problem %s',
            job_id, problem_alias
        )

        try:
            # Set initial job status
            self.redis_client.set_job_status(job_id, {
                'status': 'processing',
                'worker_id': self.worker_id,
                'problem_alias': problem_alias,
                'started_at': time.time()
            })

            # Generate editorial using the editorial generator
            editorial = self.editorial_generator.generate_editorial(
                problem_alias
            )

            if editorial:
                # Store successful result
                self.redis_client.set_job_status(job_id, {
                    'status': 'completed',
                    'editorial_en': editorial,
                    'completed_at': time.time(),
                    'worker_id': self.worker_id
                })
                logging.info('Successfully completed job %s', job_id)
            else:
                # Store failure result
                self.redis_client.set_job_status(job_id, {
                    'status': 'failed',
                    'error': 'Failed to generate editorial',
                    'failed_at': time.time(),
                    'worker_id': self.worker_id
                })
                logging.error('Failed to generate editorial for job %s', job_id)

        except Exception as e:
            logging.exception('Unexpected error processing job %s', job_id)
            self.redis_client.set_job_status(job_id, {
                'status': 'failed',
                'error': f'Unexpected error: {str(e)}',
                'failed_at': time.time(),
                'worker_id': self.worker_id
            })


def main() -> None:
    """Main entry point following omegaUp cron script patterns."""
    parser = argparse.ArgumentParser(
        description='AI Editorial Worker for omegaUp'
    )

    # Add worker-specific arguments
    parser.add_argument(
        '--worker-id',
        type=int,
        default=0,
        help='Worker ID for identification'
    )

    # Initialize config manager for argument setup
    config_manager = ConfigManager()
    
    # Add configuration arguments
    config_manager.configure_openai_parser(parser)
    config_manager.configure_redis_parser(parser)

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
        logging.exception('Fatal error: %s', e)
        sys.exit(1)


if __name__ == '__main__':
    main()
