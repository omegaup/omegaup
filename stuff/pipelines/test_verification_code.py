#!/usr/bin/env python3

'''test verification_code module.'''

from typing import Any, List
import pytest
from verification_code import _ALPHABET, generate_code


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


def test_does_not_mutate_caller_list() -> None:
    '''Test that the caller input list is not modified in-place.'''
    params = [0, 1, 2, 3, 4, 5, 6, 7, 8]
    original = list(params)
    result1 = generate_code(params)
    assert params == original
    assert len(params) == 9

    # Calling generate_code a second time should be idempotent
    result2 = generate_code(params)
    assert result1 == result2
    assert params == original


def test_generate_random_code() -> None:
    '''Test random 10-digit code generation when no argument is passed.'''
    code = generate_code()
    assert len(code) == 10
    assert all(char in _ALPHABET for char in code)


@pytest.mark.parametrize(
    "invalid_length_params",
    [
        [],
        [1],
        [0, 1, 2, 3, 4, 5, 6, 7],
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    ],
)  # type: ignore
def test_invalid_length(invalid_length_params: List[int]) -> None:
    '''Test that passing a list whose length is not 9 raises ValueError.'''
    with pytest.raises(ValueError, match=r'must contain exactly 9 digits'):
        generate_code(invalid_length_params)


@pytest.mark.parametrize(
    "out_of_range_params",
    [
        [-1, 1, 2, 3, 4, 5, 6, 7, 8],
        [0, 1, 2, 3, 4, 5, 6, 7, 20],
        [0, 1, 2, 3, 4, 5, 6, 7, 99],
    ],
)  # type: ignore
def test_out_of_range_digits(out_of_range_params: List[int]) -> None:
    '''Test that digits out of the alphabet range raise ValueError.'''
    with pytest.raises(
        ValueError, match=r'All digits must be integers between 0 and 19'
    ):
        generate_code(out_of_range_params)


@pytest.mark.parametrize(
    "invalid_type_params",
    [
        [0, 1, 2, 3, 4, 5, 6, 7, True],
        [0, 1, 2, 3, 4, 5, 6, 7, False],
        [0, 1, 2, 3, 4, 5, 6, 7, '8'],
        [0, 1, 2, 3, 4, 5, 6, 7, 8.5],
    ],
)  # type: ignore
def test_invalid_types(invalid_type_params: List[Any]) -> None:
    '''Test that non-integer elements raise ValueError.'''
    with pytest.raises(
        ValueError, match=r'All digits must be integers between 0 and 19'
    ):
        generate_code(invalid_type_params)
