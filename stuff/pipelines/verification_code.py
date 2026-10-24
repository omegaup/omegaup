#!/usr/bin/env python3

'''Create verification code to certificates.'''

import os
import sys
import random
from typing import Optional, List


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))

_ALPHABET = "23456789CFGHJMPQRVWX"


def generate_code(generated_code: Optional[List[int]] = None) -> str:
    '''Function to create a 10-digit verification code.

    To create a 10-digit code, the alphabet "23456789CFGHJMPQRVWX" is used.
    A digit is added at the end which serves as a checksum to easily
    distinguish badly copied codes using the Noid Check Digit
    Algorithm (NCDA).

    generated_code: Optional list of indexes that will
      determine the first 9 digits from the alphabet.
    '''
    if generated_code is None:
        code_digits = random.choices(range(len(_ALPHABET)), k=9)
    else:
        if len(generated_code) != 9:
            raise ValueError(
                f'generated_code must contain exactly 9 digits, got '
                f'{len(generated_code)}'
            )
        if any(
            isinstance(digit, bool)
            or not isinstance(digit, int)
            or not 0 <= digit < len(_ALPHABET)
            for digit in generated_code
        ):
            raise ValueError(
                f'All digits must be integers between 0 and '
                f'{len(_ALPHABET) - 1}'
            )
        code_digits = list(generated_code)

    checksum = 0
    for i, xdigit in enumerate(code_digits, start=1):
        checksum += i * xdigit
    code_digits.append(checksum % 20)
    return ''.join(_ALPHABET[digit] for digit in code_digits)
