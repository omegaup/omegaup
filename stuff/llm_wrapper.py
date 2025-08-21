"""LLM Wrapper for Multiple Providers"""

from typing import Any
import anthropic  # type: ignore
import openai  # type: ignore
import google.generativeai as genai  # type: ignore
from google.generativeai import types  # type: ignore


class LLMWrapper:
    """Wrapper for different LLM providers to generate responses."""
    def __init__(self, provider: str, api_key: str):
        self.provider = provider.lower()
        self.api_key = api_key
        self.client: Any

        if self.provider == 'claude':
            self.client = anthropic.Anthropic(api_key=self.api_key)

        elif self.provider == 'gpt':
            self.client = openai.OpenAI(api_key=self.api_key)

        elif self.provider == 'deepseek':
            self.client = openai.OpenAI(
                api_key=self.api_key,
                base_url="https://api.deepseek.com"
            )

        elif self.provider == 'gemini':
            self.client = genai.Client(api_key=self.api_key)

        elif self.provider == 'omegaup':
            # Dummy oracle for testing - only works with specific key
            if self.api_key != "omegaup":
                raise ValueError("Invalid API key for omegaup provider")
            self.client = None

        else:
            raise ValueError(f"Unsupported LLM provider: {self.provider}")

    def generate_response(self, prompt: str, temperature: float = 0.0) -> str:
        """Generate a response from the LLM provider based on the prompt."""
        response_text = ""
        try:
            if self.provider == 'claude':
                message = self.client.messages.create(
                    model="claude-sonnet-4-20250514",
                    messages=[
                        {"role": "user", "content": prompt}
                    ],
                    temperature=temperature,
                    max_tokens=500
                )
                response_text = message.content[0].text

            elif self.provider == 'gpt':
                chat_completion = self.client.chat.completions.create(
                    model="gpt-4o",
                    messages=[{"role": "user", "content": prompt}],
                    temperature=temperature,
                    max_tokens=500
                )
                response_text = chat_completion.choices[0].message.content

            elif self.provider == 'gemini':
                response = self.client.models.generate_content(
                    model='gemini-2.0-flash-001',
                    contents=prompt,
                    config=types.GenerateContentConfig(
                        max_output_tokens=500,
                        temperature=temperature,
                    ),
                )
                response_text = response.text

            elif self.provider == 'deepseek':
                chat_completion = self.client.chat.completions.create(
                    model="deepseek-chat",
                    messages=[{"role": "user", "content": prompt}],
                    temperature=temperature,
                    max_tokens=500
                )
                response_text = chat_completion.choices[0].message.content

            elif self.provider == 'omegaup':
                # Dummy oracle for testing
                # always returns the same JSON response
                response_text = (
                    '{"general advices": "This is dummy oracle", '
                    '"1": "The oracle call worked."}'
                )

            else:
                raise ValueError(
                    f"Unknown LLM provider during response generation: "
                    f"{self.provider}"
                )

        except Exception as e:
            print(f"Error generating response from {self.provider}: {e}")
            raise e
        return response_text
