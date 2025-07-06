"""
Configuration for AI Editorial Worker
"""

import os


# OpenAI Configuration
OPENAI_API_KEY = os.getenv('OPENAI_API_KEY')
OPENAI_MODEL = os.getenv('OPENAI_MODEL', 'gpt-4')

# Redis Configuration
REDIS_HOST = os.getenv('REDIS_HOST', 'redis')
REDIS_PORT = int(os.getenv('REDIS_PORT', '6379'))
REDIS_PASS = os.getenv('REDIS_PASS', 'redis')

# Worker Configuration
WORKER_TIMEOUT = int(os.getenv('WORKER_TIMEOUT', '30'))
WORKER_ENABLED = os.getenv('AI_WORKER_ENABLED', 'false').lower() == 'true'

# Validation
if not OPENAI_API_KEY:
    raise ValueError('OPENAI_API_KEY environment variable is required') 