"""Redis client for AI Editorial Worker job management.

"""

import json
import logging
import os
from typing import Any, Dict, List, Optional, Tuple

import redis  # type: ignore


class RedisJobClient:
    """Handles Redis operations for editorial job management."""

    def __init__(self, redis_config: Optional[Dict[str, Any]] = None):
        """Initialize Redis client with configuration or environment variables."""
        if redis_config is None:
            # Use environment variables 
            redis_config = {
                'host': os.getenv('REDIS_HOST', 'redis'),
                'port': int(os.getenv('REDIS_PORT', '6379')),
                'password': os.getenv('REDIS_PASS'),  # Match omegaUp pattern
                'timeout': 30  # Fixed timeout, not configurable
            }
        
        self.config = redis_config
        self.client: Optional[redis.Redis[str]] = None
        self.setup_connection()

    def setup_connection(self) -> None:
        """Setup Redis connection and test connectivity."""
        try:
            self.client = redis.Redis(
                host=self.config['host'],
                port=self.config['port'],
                password=self.config.get('password'),
                decode_responses=True,
                socket_timeout=self.config.get('timeout', 30)
            )

            # Test connection - mypy fix: assert client is not None
            if self.client is not None:
                self.client.ping()
                logging.info(
                    'Connected to Redis at %s:%s',
                    self.config['host'], self.config['port']
                )

        except redis.RedisError as e:
            logging.error('Redis connection error: %s', e)
            raise
        except Exception as e:  # pylint: disable=broad-except
            logging.error('Unexpected Redis setup error: %s', e)
            raise

    def poll_job_queue(self, timeout: int = 10) -> Optional[Tuple[str, str]]:
        """Poll Redis queue for new jobs."""
        if not self.client:
            raise RuntimeError("Redis client not initialized")

        try:
            # Block and wait for jobs from editorial_jobs_queue
            # Redis library has complex typing, use type: ignore
            result = self.client.brpop(
                ['editorial_jobs_queue'],
                timeout=timeout)
            if result and len(result) == 2:  # type: ignore
                queue_name, job_data = str(
                    result[0]), str(  # type: ignore
                    result[1])  # type: ignore
                return queue_name, job_data
            return None

        except redis.RedisError as e:
            logging.error('Redis polling error: %s', e)
            raise

    def get_next_job(self, queues: List[str], timeout: int = 30) -> Optional[Dict[str, Any]]:
        """Get next job from priority queues (first queue = highest priority)."""
        if not self.client:
            raise RuntimeError("Redis client not initialized")

        try:
            # Block and wait for jobs from multiple queues (priority order)
            result = self.client.brpop(queues, timeout=timeout)
            if result and len(result) == 2:
                queue_name, job_data = str(result[0]), str(result[1])
                logging.debug(f'Got job from queue {queue_name}: {job_data}')
                return self.parse_job_data(job_data)
            return None

        except redis.RedisError as e:
            logging.error('Redis polling error: %s', e)
            raise

    def queue_job(self, queue_name: str, job_data: Dict[str, Any]) -> None:
        """Queue a job to the specified Redis queue."""
        if not self.client:
            raise RuntimeError("Redis client not initialized")

        try:
            job_json = json.dumps(job_data)
            self.client.lpush(queue_name, job_json)
            logging.info(f'Queued job {job_data.get("job_id", "unknown")} to {queue_name}')

        except redis.RedisError as e:
            logging.error('Redis job queuing error: %s', e)
            raise

    def set_job_status(self, job_id: str, status_data: Dict[str, Any]) -> None:
        """Set job status in Redis hash."""
        if not self.client:
            raise RuntimeError("Redis client not initialized")

        if not job_id:
            raise ValueError("Job ID cannot be empty")

        try:
            # Convert all values to strings for Redis storage
            string_data = {
                key: str(value) for key, value in status_data.items()
            }
            self.client.hset(f'job:{job_id}', mapping=string_data)

        except redis.RedisError as e:
            logging.error('Failed to set job status for %s: %s', job_id, e)
            raise

    def get_job_status(self, job_id: str) -> Optional[Dict[str, str]]:
        """Get job status from Redis hash."""
        if not self.client:
            raise RuntimeError("Redis client not initialized")

        if not job_id:
            return None

        try:
            # Redis library has complex typing, use type: ignore
            raw_result = self.client.hgetall(f'job:{job_id}')  # type: ignore
            if raw_result:
                # Ensure all values are strings
                result = {str(k): str(v)
                          for k, v in raw_result.items()}  # type: ignore
                return result
            return None

        except redis.RedisError as e:
            logging.error('Failed to get job status for %s: %s', job_id, e)
            return None

    def parse_job_data(self, job_json: str) -> Dict[str, Any]:
        """Parse job JSON data with error handling."""
        if not job_json:
            raise ValueError("Job JSON cannot be empty")

        try:
            # Fix mypy: explicitly cast json.loads result
            parsed_data: Dict[str, Any] = json.loads(job_json)
            return parsed_data
        except json.JSONDecodeError as e:
            logging.error('Invalid job JSON: %s', e)
            raise
