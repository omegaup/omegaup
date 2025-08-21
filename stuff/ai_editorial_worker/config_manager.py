"""Configuration management for AI Editorial Worker."""

import argparse
import configparser
import json
import logging
import os
from typing import Any, Dict, Optional


class ConfigManager:
    """Manages configuration for AI Editorial Worker."""

    def __init__(self) -> None:
        """Initialize configuration manager."""
        self.ai_config: Optional[Dict[str, Any]] = None
        self.prompt_template: Optional[str] = None

    def load_ai_config(
        self, config_file: Optional[str] = None
    ) -> Dict[str, Any]:
        """Load AI configuration from JSON file."""
        if config_file is None:
            config_file = os.path.join(
                os.path.dirname(__file__), 'ai_config.json'
            )

        try:
            with open(config_file, 'r', encoding='utf-8') as f:
                loaded_config: Dict[str, Any] = json.load(f)
                self.ai_config = loaded_config
                return loaded_config
        except FileNotFoundError:
            logging.warning(
                'AI config file not found: %s, using defaults', config_file
            )
            default_config: Dict[str, Any] = {
                "openai": {
                    "model": "gpt-4o-mini",
                    "temperature": 0.7,
                    "max_tokens": 1500,
                    "top_p": 0.9,
                    "frequency_penalty": 0.0,
                    "presence_penalty": 0.1,
                    "timeout": 60
                },
                "system_message": (
                    "You are an expert competitive programming assistant "
                    "who generates clear, educational editorials without "
                    "any code."
                ),
                "prompts": {
                    "editorial_file": "prompts/editorial.txt"
                }
            }
            self.ai_config = default_config
            return default_config
        except json.JSONDecodeError as e:
            logging.error('Invalid JSON in AI config file: %s', e)
            raise

    def load_prompt_template(self, prompt_file: Optional[str] = None) -> str:
        """Load prompt template from file."""
        if prompt_file is None:
            ai_config = self.ai_config or self.load_ai_config()
            prompt_file = os.path.join(
                os.path.dirname(__file__),
                ai_config.get('prompts', {}).get(
                    'editorial_generation', 'prompts/editorial.txt'
                )
            )

        try:
            with open(prompt_file, 'r', encoding='utf-8') as f:
                self.prompt_template = f.read().strip()
                return self.prompt_template
        except FileNotFoundError:
            logging.error('Prompt file not found: %s', prompt_file)
            raise
        except IOError as e:
            logging.error('Error reading prompt file: %s', e)
            raise

    def get_redis_config(self, args: argparse.Namespace) -> Dict[str, Any]:
        """Get Redis config from arguments, config file, or environment."""
        host = args.redis_host
        port = args.redis_port
        password = args.redis_password

        # Try config file if specified
        if args.redis_config_file:
            try:
                config = configparser.ConfigParser()
                config.read(args.redis_config_file)

                if 'redis' in config:
                    if 'host' in config['redis'] and not args.redis_host:
                        host = config['redis']['host'].strip("'\"")
                    if ('port' in config['redis'] and
                            args.redis_port == 6379):  # Default port
                        port = int(config['redis']['port'].strip("'\""))
                    if ('password' in config['redis'] and
                            password is None):
                        password = config['redis']['password'].strip("'\"")

            except configparser.Error as e:
                logging.warning('Error reading Redis config file: %s', e)
            except (ValueError, OSError) as e:
                logging.warning('Error processing Redis config file: %s', e)

        # Environment variable fallbacks
        if not host:
            host = os.getenv('REDIS_HOST', 'localhost')
        if port == 6379:  # Default port
            port = int(os.getenv('REDIS_PORT', '6379'))
        if password is None:
            password = os.getenv('REDIS_PASSWORD')

        return {
            'host': host,
            'port': port,
            'password': password,
            'db': 0,
            'timeout': 30
        }

    def get_openai_config(self, args: argparse.Namespace) -> Dict[str, str]:
        """Get OpenAI config from arguments, config file, or environment."""
        api_key = args.openai_api_key
        model = args.openai_model

        # Try config file if specified
        if args.openai_config_file:
            try:
                config = configparser.ConfigParser()
                config.read(args.openai_config_file)

                if 'openai' in config:
                    if 'api_key' in config['openai'] and not api_key:
                        api_key = config['openai']['api_key'].strip("'\"")
                    if 'model' in config['openai'] and not model:
                        model = config['openai']['model'].strip("'\"")

            except configparser.Error as e:
                logging.warning('Error reading OpenAI config file: %s', e)
            except OSError as e:
                logging.warning('Error accessing OpenAI config file: %s', e)

        # Environment variable fallbacks
        if not api_key:
            api_key = os.getenv('OPENAI_API_KEY')
        if not model:
            model = os.getenv('OPENAI_MODEL', 'gpt-4o-mini')

        if not api_key:
            raise ValueError(
                "OpenAI API key required (--openai-api-key, config file, "
                "or OPENAI_API_KEY env var)"
            )

        return {
            'api_key': api_key,
            'model': model
        }

    def configure_redis_parser(self, parser: argparse.ArgumentParser) -> None:
        """Add Redis configuration arguments to parser."""
        parser.add_argument(
            '--redis-host',
            type=str,
            help='Redis host (default: localhost)'
        )
        parser.add_argument(
            '--redis-port',
            type=int,
            default=6379,
            help='Redis port (default: 6379)'
        )
        parser.add_argument(
            '--redis-password',
            type=str,
            help='Redis password'
        )
        parser.add_argument(
            '--redis-config-file',
            type=str,
            default=self._default_config_file_path(),
            help='Config file that stores Redis credentials'
        )

    def configure_openai_parser(self, parser: argparse.ArgumentParser) -> None:
        """Add OpenAI configuration arguments to parser."""
        parser.add_argument(
            '--openai-api-key',
            type=str,
            help='OpenAI API key'
        )
        parser.add_argument(
            '--openai-model',
            type=str,
            help='OpenAI model (default: gpt-4o-mini)'
        )
        parser.add_argument(
            '--openai-config-file',
            type=str,
            default=self._default_config_file_path(),
            help='Config file that stores OpenAI credentials'
        )

    def _default_config_file_path(self) -> str:
        """Get default configuration file path."""
        return os.path.join(
            os.path.dirname(os.path.dirname(__file__)),
            'config.php'
        )
