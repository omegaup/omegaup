"""Website uploader for publishing editorials to omegaUp."""

import logging
import os
import sys
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
from typing import Dict, Any

from omegaup_api_client import OmegaUpAPIClient  # type: ignore


class WebsiteUploader:
    """Handles uploading editorials to omegaUp website."""

    def __init__(self, config_manager: Any,
                 api_client: OmegaUpAPIClient) -> None:
        """Initialize website uploader."""
        self.config_manager = config_manager
        self.api_client = api_client

    def upload_editorials(self, problem_alias: str,
                          editorials: Dict[str, str]) -> bool:
        """Upload editorials for all languages."""
        try:
            success = True
            for language, content in editorials.items():
                if content and content.strip():
                    result = self.upload_editorial_for_language(
                        problem_alias, language, content)
                    if not result:
                        success = False
                        logging.error(
                            "Failed to upload %s editorial for %s",
                            language,
                            problem_alias)
                else:
                    logging.warning(
                        "Empty content for %s editorial of %s",
                        language,
                        problem_alias)

            return success

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error("Error uploading editorials for %s: %s",
                          problem_alias, e)
            return False

    def upload_editorial_for_language(self, problem_alias: str,
                                      language: str, content: str) -> bool:
        """Upload editorial for specific language."""
        try:
            # Validate content
            if not self.validate_editorial_content(content):
                logging.error(
                    "Invalid content for %s editorial of %s",
                    language,
                    problem_alias)
                return False

            # Check permissions
            if not self.check_upload_permissions(problem_alias):
                logging.error(
                    "No permission to upload editorial for %s",
                    problem_alias)
                return False

            # Prepare content for upload
            prepared_content = self.prepare_editorial_for_upload(content)

            # Upload via API with authentication
            try:
                success = self.api_client.update_problem_solution(
                    problem_alias, prepared_content, language)

                if success:
                    logging.info(
                        "Successfully uploaded %s editorial for %s",
                        language,
                        problem_alias)
                    return True
            except ConnectionError as e:
                logging.error(
                    "Authentication failed for editorial upload %s/%s: %s",
                    problem_alias, language, str(e))
                return False

            logging.error(
                "API call failed for %s editorial of %s",
                language,
                problem_alias)
            return False

        except (ConnectionError, TypeError, ValueError) as e:
            logging.error(
                "Error uploading %s editorial for %s: %s",
                language,
                problem_alias,
                e)
            return False

    def validate_editorial_content(self, content: str) -> bool:
        """Validate editorial content before upload."""
        if not content or not content.strip():
            return False

        # Check minimum length
        if len(content.strip()) < 100:
            logging.warning("Editorial content too short (< 100 chars)")
            return False

        # Check for required sections (basic validation)
        # Support English, Spanish, and Portuguese keywords
        content_lower = content.lower()
        has_approach = any(keyword in content_lower for keyword in [
            # English keywords
            'approach', 'solution', 'algorithm', 'strategy', 'method',
            'solve', 'problem', 'explanation',
            # Spanish keywords
            'enfoque', 'solución', 'algoritmo', 'estrategia', 'método',
            'resolver', 'problema', 'explicación',
            # Portuguese keywords
            'abordagem', 'solução', 'algoritmo', 'estratégia', 'método',
            'resolver', 'problema', 'explicação'
        ])

        if not has_approach:
            logging.warning("Editorial missing approach/solution section")
            return False

        return True

    def prepare_editorial_for_upload(self, content: str) -> str:
        """Prepare editorial content for website upload."""
        # Clean up content
        cleaned_content = content.strip()

        # Normalize line endings
        cleaned_content = cleaned_content.replace(
            '\r\n', '\n').replace('\r', '\n')

        # Remove excessive blank lines
        lines = cleaned_content.split('\n')
        cleaned_lines = []
        blank_line_count = 0

        for line in lines:
            if line.strip() == '':
                blank_line_count += 1
                # Allow max 2 consecutive blank lines
                if blank_line_count <= 2:
                    cleaned_lines.append(line)
            else:
                blank_line_count = 0
                cleaned_lines.append(line)

        cleaned_content = '\n'.join(cleaned_lines)

        # Ensure content ends with newline
        if not cleaned_content.endswith('\n'):
            cleaned_content += '\n'

        return cleaned_content

    def check_upload_permissions(self, problem_alias: str) -> bool:
        """Check if we have permissions to upload editorial for this
        problem."""
        try:
            has_access = self.api_client.check_admin_access(problem_alias)
            if has_access:
                logging.info(
                    "Confirmed upload permissions for %s",
                    problem_alias)
                return bool(has_access)

            logging.warning(
                "No admin access for %s",
                problem_alias)
            return False
        except (ConnectionError, TypeError, ValueError) as e:
            logging.error(
                "Error checking permissions for %s: %s",
                problem_alias,
                e)
            return False

    def upload_with_validation(self, problem_alias: str,
                               editorials: Dict[str, str]) -> Dict[str, Any]:
        """Upload editorials with comprehensive validation."""
        results: Dict[str, Any] = {
            'success': False,
            'uploaded_languages': [],
            'failed_languages': [],
            'errors': []
        }

        try:
            # Check permissions first
            if not self.check_upload_permissions(problem_alias):
                error_msg = f"No upload permissions for {problem_alias}"
                results['errors'].append(error_msg)
                return results

            # Upload each language
            for language, content in editorials.items():
                try:
                    if self.upload_editorial_for_language(
                        problem_alias, language, content):
                        results['uploaded_languages'].append(language)
                    else:
                        results['failed_languages'].append(language)
                        results['errors'].append(
                            f"Failed to upload {language} editorial")
                except (ConnectionError, TypeError, ValueError) as e:
                    results['failed_languages'].append(language)
                    error_msg = f"Error uploading {language}: {e}"
                    results['errors'].append(error_msg)
                    logging.error(error_msg)

            # Overall success if at least one language uploaded
            results['success'] = len(results['uploaded_languages']) > 0

            if results['success']:
                logging.info(
                    "Upload completed for %s: %d languages successful",
                    problem_alias,
                    len(results['uploaded_languages']))
            else:
                logging.error(
                    "All uploads failed for %s",
                    problem_alias)

            return results

        except (ConnectionError, TypeError, ValueError) as e:
            error_msg = f"Validation error for {problem_alias}: {e}"
            results['errors'].append(error_msg)
            logging.error(error_msg)
            return results

    def get_upload_stats(self) -> Dict[str, Any]:
        """Get statistics about editorial uploads."""
        return {
            'total_uploads': getattr(self, '_total_uploads', 0),
            'successful_uploads': getattr(self, '_successful_uploads', 0),
            'failed_uploads': getattr(self, '_failed_uploads', 0)
        }
