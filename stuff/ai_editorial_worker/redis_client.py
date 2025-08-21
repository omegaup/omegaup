"""Redis client for AI Editorial Worker job management."""

import json
import logging
from typing import Any, Dict, Optional, Tuple

import redis  # type: ignore


class RedisJobClient:
    """Handles Redis operations for editorial job management."""

    def __init__(self, redis_config: Dict[str, Any]):
        """Initialize Redis client with configuration."""
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
                db=self.config.get('db', 0),
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
                ['editorial_jobs_user', 'editorial_jobs_batch'],
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
