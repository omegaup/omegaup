#!/usr/bin/env python3
"""Configuration manager for AI Editorial Worker.

Handles loading configuration from files (production) and environment variables (dev/testing).
Follows omegaUp's pattern of using config files for secure credential management.
"""

import argparse
import configparser
import json
import os
from typing import Dict, Any, Optional
import logging


class AIConfigManager:
    def __init__(self, config_file: Optional[str] = None, ai_config_file: Optional[str] = None):
        """Initialize config manager with optional config file paths."""
        self.config_file = config_file or self._default_ai_config_file_path()
        self.ai_config_file = ai_config_file or self._default_ai_settings_file_path()
        self.config = {}
        self.ai_settings = {}
        self.prompts = {}
        
    def _default_ai_config_file_path(self) -> Optional[str]:
        """Try to autodetect the AI config file path."""
        # Follow the same pattern as lib/db.py cronjobs use for ~/.my.cnf
        candidates = [
            # Production: ~/.my.cnf (same as database credentials)
            os.path.join(os.getenv('HOME') or '.', '.my.cnf'),
            # Alternative: ~/.ai_config (for AI-specific configs)
            os.path.join(os.getenv('HOME') or '.', '.ai_config'),
        ]
        
        for candidate_path in candidates:
            if os.path.isfile(candidate_path):
                return candidate_path
        return None
    
    def _default_ai_settings_file_path(self) -> str:
        """Get the AI settings JSON file path."""
        script_dir = os.path.dirname(os.path.abspath(__file__))
        return os.path.join(script_dir, 'ai_config.json')
        
    def load_config(self) -> Dict[str, Any]:
        """Load configuration from file and environment."""
        # First load AI settings (model configs, etc.)
        self._load_ai_settings()
        
        # Then load credentials from config file
        self._load_credentials()
        
        # Combine everything
        self.config = {
            **self.ai_settings,
            'credentials': getattr(self, 'credentials', {})
        }
        
        return self.config
    
    def _load_ai_settings(self):
        """Load AI configuration settings from JSON file."""
        if os.path.isfile(self.ai_config_file):
            try:
                with open(self.ai_config_file, 'r', encoding='utf-8') as f:
                    self.ai_settings = json.load(f)
                logging.info(f"Loaded AI settings from {self.ai_config_file}")
            except Exception as e:
                logging.warning(f"Failed to load AI settings from {self.ai_config_file}: {e}")
                self.ai_settings = self._get_default_ai_settings()
        else:
            logging.info("AI settings file not found, using defaults")
            self.ai_settings = self._get_default_ai_settings()
    
    def _load_credentials(self):
        """Load credentials from config file (production) or environment (dev)."""
        self.credentials = {}

        # Try to load from config file first (production) - Following cronjob pattern
        if self.config_file and os.path.isfile(self.config_file):
            try:
                config = configparser.ConfigParser()
                config.read(self.config_file)

                # Follow cronjob pattern: load from sections, strip quotes like database credentials  
                # Check for AI-specific sections first, then fallback to general sections
                if 'ai_openai' in config:
                    self.credentials['openai'] = {
                        'api_key': config['ai_openai'].get('api_key', '').strip("'\""),
                        'model': config['ai_openai'].get('model', 'gpt-4o').strip("'\""),
                        'rpm_limit': int(config['ai_openai'].get('rpm_limit', '500')),
                        'tpm_limit': int(config['ai_openai'].get('tpm_limit', '2000000'))
                    }
                elif 'openai' in config:
                    self.credentials['openai'] = {
                        'api_key': config['openai'].get('api_key', '').strip("'\""),
                        'model': config['openai'].get('model', 'gpt-4o').strip("'\""),
                        'rpm_limit': int(config['openai'].get('rpm_limit', '500')),
                        'tpm_limit': int(config['openai'].get('tpm_limit', '2000000'))
                    }
                
                if 'ai_deepseek' in config:
                    self.credentials['deepseek'] = {
                        'api_key': config['ai_deepseek'].get('api_key', '').strip("'\""),
                        'model': config['ai_deepseek'].get('model', 'deepseek-chat').strip("'\""),
                        'rpm_limit': int(config['ai_deepseek'].get('rpm_limit', '300')),
                        'tpm_limit': int(config['ai_deepseek'].get('tpm_limit', '1000000'))
                    }
                elif 'deepseek' in config:
                    self.credentials['deepseek'] = {
                        'api_key': config['deepseek'].get('api_key', '').strip("'\""),
                        'model': config['deepseek'].get('model', 'deepseek-chat').strip("'\""),
                        'rpm_limit': int(config['deepseek'].get('rpm_limit', '300')),
                        'tpm_limit': int(config['deepseek'].get('tpm_limit', '1000000'))
                    }
                
                if 'ai_anthropic' in config:
                    self.credentials['anthropic'] = {
                        'api_key': config['ai_anthropic'].get('api_key', '').strip("'\""),
                        'model': config['ai_anthropic'].get('model', 'claude-3-sonnet-20240229').strip("'\""),
                        'rpm_limit': int(config['ai_anthropic'].get('rpm_limit', '100')),
                        'tpm_limit': int(config['ai_anthropic'].get('tpm_limit', '500000'))
                    }
                elif 'anthropic' in config:
                    self.credentials['anthropic'] = {
                        'api_key': config['anthropic'].get('api_key', '').strip("'\""),
                        'model': config['anthropic'].get('model', 'claude-3-sonnet-20240229').strip("'\""),
                        'rpm_limit': int(config['anthropic'].get('rpm_limit', '100')),
                        'tpm_limit': int(config['anthropic'].get('tpm_limit', '500000'))
                    }
                
                if 'ai_google' in config:
                    self.credentials['google'] = {
                        'api_key': config['ai_google'].get('api_key', '').strip("'\""),
                        'model': config['ai_google'].get('model', 'gemini-pro').strip("'\""),
                        'rpm_limit': int(config['ai_google'].get('rpm_limit', '60')),
                        'tpm_limit': int(config['ai_google'].get('tpm_limit', '1000000'))
                    }
                elif 'google' in config:
                    self.credentials['google'] = {
                        'api_key': config['google'].get('api_key', '').strip("'\""),
                        'model': config['google'].get('model', 'gemini-pro').strip("'\""),
                        'rpm_limit': int(config['google'].get('rpm_limit', '60')),
                        'tpm_limit': int(config['google'].get('tpm_limit', '1000000'))
                    }
                
                logging.info(f"Loaded credentials from config file: {self.config_file}")
                
            except Exception as e:
                logging.warning(f"Failed to load config file {self.config_file}: {e}")
                self._load_credentials_from_env()
        else:
            logging.info("Config file not found, loading from environment variables")
            self._load_credentials_from_env()
    
    def _load_credentials_from_env(self):
        """Fallback: Load credentials from environment variables (development/testing)."""
        # Determine provider from environment
        provider = os.getenv('LLM_PROVIDER', 'openai').lower()
        
        if provider == 'openai':
            api_key = os.getenv('OPENAI_API_KEY')
            if api_key:
                self.credentials['openai'] = {
                    'api_key': api_key,
                    'model': os.getenv('OPENAI_MODEL', 'gpt-4o'),
                    'rpm_limit': int(os.getenv('OPENAI_RPM_LIMIT', '500')),
                    'tpm_limit': int(os.getenv('OPENAI_TPM_LIMIT', '2000000'))
                }
        
        elif provider == 'deepseek':
            api_key = os.getenv('DEEPSEEK_API_KEY')
            if api_key:
                self.credentials['deepseek'] = {
                    'api_key': api_key,
                    'model': os.getenv('DEEPSEEK_MODEL', 'deepseek-chat'),
                    'rpm_limit': int(os.getenv('DEEPSEEK_RPM_LIMIT', '300')),
                    'tpm_limit': int(os.getenv('DEEPSEEK_TPM_LIMIT', '1000000'))
                }
        
        elif provider == 'anthropic':
            api_key = os.getenv('ANTHROPIC_API_KEY')
            if api_key:
                self.credentials['anthropic'] = {
                    'api_key': api_key,
                    'model': os.getenv('ANTHROPIC_MODEL', 'claude-3-sonnet-20240229'),
                    'rpm_limit': int(os.getenv('ANTHROPIC_RPM_LIMIT', '100')),
                    'tpm_limit': int(os.getenv('ANTHROPIC_TPM_LIMIT', '500000'))
                }
        
        elif provider == 'google':
            api_key = os.getenv('GOOGLE_API_KEY')
            if api_key:
                self.credentials['google'] = {
                    'api_key': api_key,
                    'model': os.getenv('GOOGLE_MODEL', 'gemini-pro'),
                    'rpm_limit': int(os.getenv('GOOGLE_RPM_LIMIT', '60')),
                    'tpm_limit': int(os.getenv('GOOGLE_TPM_LIMIT', '1000000'))
                }
    
    def _get_default_ai_settings(self) -> Dict[str, Any]:
        """Get default AI settings if config file is not available."""
        return {
            "worker_settings": {
                "max_workers": 8,
                "job_timeout": 300,
                "retry_attempts": 3,
                "retry_delay": 5
            },
            "generation_settings": {
                "max_tokens": 4000,
                "temperature": 0.7,
                "enable_solution_verification": True,
                "enable_multi_language": True,
                "languages": ["en", "es", "pt"]
            },
            "redis_settings": {
                "host": "redis",
                "port": 6379,
                "job_queue": "editorial_jobs_queue",
                "session_prefix": "ai_worker_session",
                "job_status_prefix": "job_status"
            }
        }

    def get_llm_config(self, provider: Optional[str] = None) -> Dict[str, Any]:
        """Get LLM configuration for specified provider or auto-detect."""
        if not provider:
            # Auto-detect provider from available credentials
            if 'openai' in self.credentials and self.credentials['openai'].get('api_key'):
                provider = 'openai'  # We'll map this to 'gpt' in editorial_generator
            elif 'deepseek' in self.credentials and self.credentials['deepseek'].get('api_key'):
                provider = 'deepseek'
            elif 'anthropic' in self.credentials and self.credentials['anthropic'].get('api_key'):
                provider = 'anthropic'  # We'll map this to 'claude' in editorial_generator
            elif 'google' in self.credentials and self.credentials['google'].get('api_key'):
                provider = 'google'  # We'll map this to 'gemini' in editorial_generator
            else:
                raise ValueError("No valid LLM provider configuration found")
        
        if provider not in self.credentials:
            raise ValueError(f"No configuration found for provider: {provider}")
        
        cred = self.credentials[provider]
        if not cred.get('api_key'):
            raise ValueError(f"No API key found for provider: {provider}")
        
        # Combine with generation settings
        return {
            'provider': provider,  # Keep original name for mapping in editorial_generator
            'api_key': cred['api_key'],
            'model': cred['model'],
            'rpm_limit': cred.get('rpm_limit', 100),
            'tpm_limit': cred.get('tpm_limit', 1000000),
            **self.ai_settings.get('generation_settings', {})
        }

    def load_all_prompts(self) -> Dict[str, str]:
        """Load all prompt templates from the prompts directory."""
        script_dir = os.path.dirname(os.path.abspath(__file__))
        prompts_dir = os.path.join(script_dir, 'prompts')
        
        prompts = {}
        prompt_files = {
            'editorial_generation': 'editorial_generation.txt',
            'solution_generation': 'solution_generation.txt', 
            'translation': 'translation.txt'
        }
        
        for prompt_name, filename in prompt_files.items():
            file_path = os.path.join(prompts_dir, filename)
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    prompts[prompt_name] = f.read().strip()
                logging.debug(f"Loaded prompt template: {prompt_name}")
            except FileNotFoundError:
                logging.warning(f"Prompt template not found: {file_path}")
                prompts[prompt_name] = self._get_default_prompt(prompt_name)
            except Exception as e:
                logging.error(f"Error loading prompt template {file_path}: {e}")
                prompts[prompt_name] = self._get_default_prompt(prompt_name)
        
        self.prompts = prompts
        return prompts

    def _get_default_prompt(self, prompt_name: str) -> str:
        """Get default prompt if file is not available."""
        defaults = {
            'editorial_generation': "Write a clear editorial for this competitive programming problem.",
            'solution_generation': "Generate a solution for this competitive programming problem.",
            'translation': "Translate this editorial to the target language."
        }
        return defaults.get(prompt_name, "")

    @staticmethod
    def configure_parser(parser: argparse.ArgumentParser) -> None:
        """Add AI config arguments to argument parser (similar to lib.db pattern)."""
        ai_args = parser.add_argument_group('AI Configuration')
        ai_args.add_argument('--ai-config-file',
            type=str,
                            help='AI config file that stores credentials (like .my.cnf)')
        ai_args.add_argument('--ai-settings-file', 
            type=str,
                            help='AI settings JSON file')
        ai_args.add_argument('--llm-provider',
            type=str,
                            choices=['openai', 'deepseek', 'anthropic', 'google'],
                            help='LLM provider to use')


def create_example_config_file():
    """Create an example .ai_config file for production setup."""
    config_content = """# AI Editorial Worker Configuration
# Similar to .my.cnf for database credentials
# Set file permissions to 600 for security: chmod 600 ~/.ai_config

[openai]
api_key=sk-your-openai-api-key-here
model=gpt-4o
rpm_limit=500
tpm_limit=2000000

[deepseek]
api_key=sk-your-deepseek-api-key-here
model=deepseek-chat
rpm_limit=300
tpm_limit=1000000

[anthropic]
api_key=sk-ant-your-anthropic-api-key-here
model=claude-3-sonnet-20240229
rpm_limit=100
tpm_limit=500000

[google]
api_key=your-google-api-key-here
model=gemini-pro
rpm_limit=60
tpm_limit=1000000
"""
    
    example_file = os.path.expanduser('~/.ai_config.example')
    with open(example_file, 'w') as f:
        f.write(config_content)
    
    print(f"Example config file created at: {example_file}")
    print("Copy to ~/.ai_config and set your actual API keys")
    print("Set secure permissions: chmod 600 ~/.ai_config")


if __name__ == '__main__':
    # Create example config file
    create_example_config_file()
