#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Utility for omegaUp token management."""

import sys
from pathlib import Path
from typing import Optional


def get_token(provided_token: Optional[str] = None) -> str:
    """Get omegaUp API token with the following priority.

    1. Token provided as parameter
    2. Token from .token file
    3. Request token via input

    Args:
        provided_token: Token provided directly

    Returns:
        Valid API token
    """
    token_file = Path('.token')

    # 1. If token is provided, save and use it
    if provided_token:
        print("🔑 Using provided token")
        with open(token_file, 'w', encoding='utf-8') as f:
            f.write(provided_token.strip())
        return provided_token.strip()

    # 2. Try to load from .token file
    if token_file.exists():
        try:
            with open(token_file, 'r', encoding='utf-8') as f:
                file_token = f.read().strip()
            if file_token:
                print("🔑 Using token from .token file")
                return file_token
        except (OSError, IOError) as e:
            print(f"⚠️  Error reading .token: {e}")

    # 3. Request token via input
    print("🔑 No token found. Please provide your omegaUp "
          "API token:")
    print("   (You can get it at: "
          "https://omegaup.com/profile/edit/#api-tokens)")

    while True:
        user_token = input("Token: ").strip()
        if user_token:
            # Save for future use
            with open(token_file, 'w', encoding='utf-8') as f:
                f.write(user_token)
            print("✅ Token saved in .token for future use")
            return user_token
        print("❌ Empty token. Please try again.")


def parse_token_from_args() -> Optional[str]:
    """Parse token from command line arguments.

    Usage:
        script.py --token abc123
        script.py -t abc123

    Returns:
        Token if provided in arguments
    """
    if len(sys.argv) >= 3:
        for i, arg in enumerate(sys.argv[1:], 1):
            if arg in ['--token', '-t'] and i + 1 < len(sys.argv):
                return sys.argv[i + 1]

    return None


def parse_url_from_args() -> Optional[str]:
    """Parse base URL from command line arguments.

    Usage:
        script.py --url https://omegaup.com
        script.py -u https://omegaup.com

    Returns:
        URL if provided in arguments
    """
    if len(sys.argv) >= 3:
        for i, arg in enumerate(sys.argv[1:], 1):
            if arg in ['--url', '-u'] and i + 1 < len(sys.argv):
                return sys.argv[i + 1]

    return None


if __name__ == "__main__":
    # Test the utility
    test_token = get_token(parse_token_from_args())
    print(f"Token obtained: {test_token[:10]}...")
