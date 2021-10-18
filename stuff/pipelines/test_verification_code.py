#!/usr/bin/python3

'''test verification_code module.'''

import unittest
from verification_code import generate_code


class TestChecksumDigit(unittest.TestCase):
    """Test checksum digit creation."""
    def test1(self) -> None:
        """Test1"""
        code = generate_code([0, 1, 2, 3, 4, 5, 6, 7, 8])
        self.assertEqual(code, '23456789C2')

    def test2(self) -> None:
        """Test2"""
        code = generate_code([2, 4, 7, 8, 10, 12, 14, 10, 16])
        self.assertEqual(code, '469CGJPGR9')


if __name__ == '__main__':
    unittest.main()
