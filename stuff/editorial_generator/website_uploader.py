#!/usr/bin/env python3
"""
Website Uploader for omegaUp Editorial System

This module handles uploading editorials to the omegaUp website
via API calls for multiple languages.
"""

import logging
from datetime import datetime
from typing import Dict

import requests

# Import with fallback for relative imports
try:
    from .ai_editorial_generator import (  # type: ignore
        EditorialGeneratorConfig
    )
except ImportError:
    from ai_editorial_generator import EditorialGeneratorConfig

logger = logging.getLogger(__name__)


class WebsiteUploader:
    """Handles uploading editorials to the omegaUp website."""

    def __init__(
        self, config: EditorialGeneratorConfig, session: requests.Session
    ) -> None:
        """Initialize website uploader."""
        self.config = config
        self.session = session

    def upload_editorial_for_language(
        self,
        problem_alias: str,
        editorial_content: str,
        language_code: str
    ) -> bool:
        """Upload editorial to omegaUp for specific language."""
        try:
            language_name = self.config.target_languages.get(
                language_code, 'Unknown'
            )
            logger.info("[%s] Uploading %s editorial to omegaUp",
                        problem_alias, language_name)
            logger.info("[%s] Editorial length: %d chars",
                        problem_alias, len(editorial_content))

            commit_message = (
                f"AI-generated {language_name} editorial on "
                f"{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}"
            )
            logger.info("[%s] Commit message: %s", problem_alias,
                        commit_message)

            # Prepare data for the API call using lang parameter
            # to specify the language
            data = {
                'problem_alias': problem_alias,
                'solution': editorial_content,
                'message': commit_message,
                'lang': language_code  # Use language code (en, es, pt)
            }

            url = f"{self.config.api_url}/problem/updateSolution"
            logger.info("POST %s", url)

            # Log the data being sent (but truncate content for readability)
            log_data = data.copy()
            if len(log_data['solution']) > 200:
                log_data['solution'] = (
                    log_data['solution'][:200] +
                    f"... (total {len(data['solution'])} chars)"
                )
            logger.info("Request data: %s", log_data)

            response = self.session.post(url, data=data, timeout=(10, 60))

            logger.info("Response status code: %d", response.status_code)

            if response.status_code != 200:
                logger.error("HTTP Error %d: %s", response.status_code,
                             response.text)
                return False

            try:
                result = response.json()
                logger.info("API Response: %s", result)
            except ValueError:
                logger.error("Invalid JSON response: %s", response.text)
                return False

            if result.get("status") == "ok":
                logger.info("[%s] %s editorial uploaded successfully!",
                            problem_alias, language_name)
                return True

            error_msg = result.get('error', 'Unknown error')
            logger.error("[%s] %s editorial upload failed: %s",
                         problem_alias, language_name, error_msg)
            return False

        except requests.RequestException as e:
            logger.error("[%s] %s editorial upload request failed: %s",
                         problem_alias, language_name, str(e))
            return False

    def upload_editorials_for_all_languages(
        self,
        problem_alias: str,
        editorials: Dict[str, str]
    ) -> Dict[str, bool]:
        """Upload editorials for all languages and return results."""
        logger.info(
            "Uploading separate editorials for each language to website..."
        )

        results = {}
        successful_uploads = 0

        for lang_code, editorial_content in editorials.items():
            lang_name = self.config.target_languages[lang_code]
            logger.info("\nUploading %s editorial...", lang_name)

            upload_success = self.upload_editorial_for_language(
                problem_alias, editorial_content, lang_code
            )

            results[lang_code] = upload_success

            if upload_success:
                successful_uploads += 1
                logger.info("✓ %s editorial uploaded successfully", lang_name)
            else:
                logger.error("✗ %s editorial upload failed", lang_name)

        logger.info("Upload results: %s", results)
        return results
