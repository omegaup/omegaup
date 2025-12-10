"""
Editorial Generator Module

Handles multi-language editorial generation using OpenAI GPT-4.
"""

import logging
from typing import Any, Dict, Optional

from openai import OpenAI  # type: ignore

# Import from absolute path instead of relative import
try:
    from ai_editorial_generator import EditorialGeneratorConfig
except ImportError:
    # Fallback for when running as module
    # pylint: disable=relative-beyond-top-level
    from .ai_editorial_generator import (  # type: ignore[import,no-redef]
        EditorialGeneratorConfig
    )

logger = logging.getLogger(__name__)


class EditorialGenerator:
    """Handle multi-language editorial generation."""

    def __init__(self, config: EditorialGeneratorConfig) -> None:
        self.config = config
        self.openai_client = OpenAI(api_key=config.openai_api_key)

    def generate_multilanguage_editorial(
        self,
        problem_details: Dict[str, Any],
        ac_solution: str,
        language: str
    ) -> Optional[Dict[str, str]]:
        """Generate editorial in English, then translate."""
        try:
            # Step 1: Generate English editorial first
            english_editorial = self._generate_english_editorial(
                problem_details, ac_solution, language
            )
            if not english_editorial:
                logger.error("Failed to generate English editorial")
                return None

            # Step 2: Translate English editorial to Spanish and Portuguese
            translations = self._translate_editorial_to_languages(
                english_editorial
            )
            if not translations:
                logger.error(
                    "Failed to translate editorial to other languages"
                )
                return None

            # Step 3: Combine with hardcoded AI disclaimers
            editorials = self._add_ai_disclaimers({
                'en': english_editorial,
                'es': translations['es'],
                'pt': translations['pt']
            })

            logger.info("Successfully generated all 3 language editorials")
            for lang_code, content in editorials.items():
                lang_name = self.config.target_languages[lang_code]
                logger.info("  %s: %d characters", lang_name, len(content))

            return editorials

        except (AttributeError, KeyError, IndexError) as e:
            logger.error(
                "Failed to generate multi-language editorials: %s", str(e)
            )
            return None
        except OSError as e:
            logger.error(
                "Network error generating multi-language editorials: %s",
                str(e)
            )
            return None

    def _generate_english_editorial(
        self,
        problem_details: Dict[str, Any],
        ac_solution: str,
        language: str
    ) -> Optional[str]:
        """Generate English editorial using AC solution as reference but
        WITHOUT including solution code."""
        try:
            statement = problem_details.get('statement', {})
            problem_statement = statement.get('markdown', '')
            problem_title = problem_details.get('title', 'Unknown Problem')

            # Trim problem statement if too long (max 5k characters)
            max_statement_length = 5000
            if len(problem_statement) > max_statement_length:
                problem_statement = problem_statement[:max_statement_length]

            # Use AC solution as reference but don't include it
            # in the final editorial
            prompt = self._create_editorial_prompt(
                problem_title, problem_statement, ac_solution, language
            )

            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {
                        "role": "system",
                        "content": (
                            "You are an expert competitive programmer. "
                            "Generate only clean, working source code without "
                            "any explanations or markdown formatting."
                        )
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                max_tokens=3000,
                temperature=0.3
            )

            editorial = response.choices[0].message.content.strip()
            logger.info("Generated English editorial (%d chars)",
                        len(editorial))
            return str(editorial)  # type: ignore

        except (AttributeError, KeyError, IndexError) as e:
            logger.error("Failed to generate English editorial: %s", str(e))
            return None
        except OSError as e:
            logger.error("Network error generating English editorial: %s",
                         str(e))
            return None

    def _create_editorial_prompt(
        self,
        problem_title: str,
        problem_statement: str,
        ac_solution: str,
        language: str
    ) -> str:
        """Create the prompt for editorial generation."""
        return f"""You are an expert competitive programming writer.
Create a comprehensive editorial for this problem.

Problem Title: {problem_title}

Problem Statement:
{problem_statement}

REFERENCE SOLUTION (Language: {language}) - FOR UNDERSTANDING ONLY:
```{language}
{ac_solution}
```

Please create a comprehensive editorial that includes:

1. **Problem Analysis**: Explain the problem in simple terms
2. **Key Insights**: Main insights needed to solve the problem
3. **Algorithm/Approach**: Step-by-step solution with detailed flow
4. **Implementation**: Strategy and key considerations
5. **Complexity**: Time and space analysis
6. **Alternatives**: Other possible solution methods

Requirements:
- Use clear, educational English for competitive programmers
- Focus on logic/approach, no code
- Use reference solution to understand approach, explain conceptually
- Detailed algorithm explanation (no code)
- Step-by-step solution flow
- Use proper markdown formatting for the editorial
- Include complexity analysis
- Explain math/algorithms used
- Do not add extra section headers beyond what's requested

Provide a complete editorial in markdown format WITHOUT any code."""

    def _translate_editorial_to_languages(
        self,
        english_editorial: str
    ) -> Optional[Dict[str, str]]:
        """Translate English editorial to Spanish and Portuguese."""
        try:
            prompt = self._create_translation_prompt(english_editorial)

            logger.info(
                "Translating English editorial to Spanish and Portuguese..."
            )

            response = self.openai_client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {
                        "role": "system",
                        "content": (
                            "You are an expert translator specializing "
                            "in competitive programming and technical content."
                        )
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                max_tokens=4000,
                temperature=0.2
            )

            translation_response = response.choices[0].message.content.strip()
            logger.info(
                "Generated translations (%d characters)",
                len(translation_response)
            )

            # Parse the translations
            translations = self._parse_translation_response(
                translation_response
            )

            if translations and len(translations) == 2:
                logger.info(
                    "Successfully parsed Spanish and Portuguese translations"
                )
                logger.info("  Spanish: %d characters",
                            len(translations['es']))
                logger.info("  Portuguese: %d characters",
                            len(translations['pt']))
                return translations

            logger.error(
                "Failed to parse translations. Got %d translations",
                len(translations) if translations else 0
            )
            return None

        except (AttributeError, KeyError, IndexError) as e:
            logger.error("Failed to translate editorial: %s", str(e))
            return None
        except OSError as e:
            logger.error("Network error translating editorial: %s", str(e))
            return None

    def _create_translation_prompt(self, english_editorial: str) -> str:
        """Create the prompt for translation."""
        return f"""You are an expert translator specializing
in competitive programming content.

Please translate the following English competitive programming
editorial to Spanish and Portuguese. Maintain the same technical
accuracy, markdown formatting, and educational tone.

ENGLISH EDITORIAL TO TRANSLATE:
{english_editorial}

Format your response EXACTLY like this with clear delimiters:

=== SPANISH TRANSLATION ===
[Complete Spanish translation here]

=== PORTUGUESE TRANSLATION ===
[Complete Portuguese translation here]

Requirements:
- Maintain all markdown formatting exactly
- Keep technical terms accurate in each language
- Preserve the educational tone and structure
- Ensure competitive programming terminology is correct
- Do not add any additional content, just translate"""

    def _parse_translation_response(
        self,
        response: str
    ) -> Optional[Dict[str, str]]:
        """Parse the translation response to extract Spanish and Portuguese."""
        try:
            translations = {}

            # Define the delimiters
            delimiters = {
                'es': '=== SPANISH TRANSLATION ===',
                'pt': '=== PORTUGUESE TRANSLATION ==='
            }

            # Find delimiter positions
            sections = {}
            for lang_code, delimiter in delimiters.items():
                start_pos = response.find(delimiter)
                if start_pos == -1:
                    logger.error(
                        "Missing delimiter for %s: %s", lang_code, delimiter
                    )
                    return None
                sections[lang_code] = start_pos + len(delimiter)

            # Extract Spanish translation
            spanish_start = sections['es']
            portuguese_start = sections['pt']

            if spanish_start < portuguese_start:
                spanish_content = response[
                    spanish_start:portuguese_start - len(delimiters['pt'])
                ].strip()
                portuguese_content = response[portuguese_start:].strip()
            else:
                portuguese_content = response[
                    portuguese_start:spanish_start - len(delimiters['es'])
                ].strip()
                spanish_content = response[spanish_start:].strip()

            if spanish_content and portuguese_content:
                translations['es'] = spanish_content
                translations['pt'] = portuguese_content
                return translations

            logger.error("Empty translation content")
            return None

        except (AttributeError, ValueError, TypeError) as e:
            logger.error("Error parsing translation response: %s", str(e))
            return None

    def _add_ai_disclaimers(
        self,
        editorials: Dict[str, str]
    ) -> Dict[str, str]:
        """Add hardcoded AI disclaimers to all editorials."""
        disclaimers = {
            'en': """*This editorial was generated using an AI model*

---

""",
            'es': """*Este editorial fue generado usando un modelo de IA*

---

""",
            'pt': """*Este editorial foi gerado usando um modelo de IA*

---

"""
        }

        final_editorials = {}
        for lang_code, content in editorials.items():
            final_editorials[lang_code] = disclaimers[lang_code] + content

        return final_editorials
