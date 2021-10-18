import unittest
from verification_code import  generate_code

class test_verification_code(unittest.TestCase):
    def test1(self):
        """Test if the checksum digit in created code 
        is calculated correctly"""
        
        indexes_in_alfaphabet = [0,1,2,3,4,5,6,7,8]
        code = generate_code(indexes_in_alfaphabet)
        self.assertEqual(code,'23456789C2')

    def test2(self):
        """Test if the checksum digit in created code 
        is calculated correctly"""
        
        indexes_in_alfaphabet = [2,4,7,8,10,12,14,10,16]
        code = generate_code(indexes_in_alfaphabet)
        self.assertEqual(code,'469CGJPGR9')

if __name__ == '__main__':
    unittest.main()


    