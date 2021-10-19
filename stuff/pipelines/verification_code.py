#!/usr/bin/python3

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
        generated_code = random.choices(range(len(_ALPHABET)), k=9)
    checksum = 0
    for i, xdigit in enumerate(generated_code, start=1):
        checksum += i * xdigit
    generated_code.append(checksum % 20)
    return ''.join(_ALPHABET[digit] for digit in generated_code)
