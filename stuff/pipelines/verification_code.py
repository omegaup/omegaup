#!/usr/bin/python3

'''Create verification code to certificates.'''

import os
import sys
import random
from typing import List


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))

_ALPHABET = "23456789CFGHJMPQRVWX"


def generate_code(generated_code: List[int] =
                  random.choices(range(len(_ALPHABET)), k=9)) -> str:
    '''Función que crea el código de verificación de 10 digitos
       que llevaran los certificados.
       El alfabeto usado es "23456789CFGHJMPQRVWX"
       Se agrega un digito al final que sirve de checksum para
       distinguir fácilmente códigos mal copiados
       usando el Noid Check Digit Algorithm (NCDA)'''
    checksum = 0
    for i, xdigit in enumerate(generated_code, start=1):
        checksum += i * xdigit
    generated_code.append(checksum % 20)
    return ''.join(_ALPHABET[digit] for digit in generated_code)
