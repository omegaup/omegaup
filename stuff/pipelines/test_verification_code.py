#!/usr/bin/python3

'''test verification_code module.'''

from typing import List
import pytest
from verification_code import generate_code


# mypy has conflict with pytest decorations
@pytest.mark.parametrize(
    "params, expected",
    [
        ([0, 1, 2, 3, 4, 5, 6, 7, 8], '23456789C2'),
        ([2, 4, 7, 8, 10, 12, 14, 10, 16], '469CGJPGR9'),
    ],
)  # type: ignore
def test_checksum_digit(params: List[int], expected: str) -> None:
    '''Test checksum digit'''
    assert generate_code(params) == expected
