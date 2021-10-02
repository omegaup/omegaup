#!/usr/bin/python3

'''Create verification code to certificates.'''

import logging
import os
import sys
import random
import MySQLdb
import MySQLdb.cursors


sys.path.insert(
    0,
    os.path.join(
        os.path.dirname(os.path.dirname(os.path.realpath(__file__))), "."))


def generate_code(cur: MySQLdb.cursors.BaseCursor) -> str:
    '''Generate an aleatory code'''
    diccionary_alfabeth = {"2": 0, "3": 1, "4": 2, "5": 3,
                           "6": 4, "7": 5, "8": 6, "9": 7,
                           "C": 8, "F": 9, "G": 10, "H": 11,
                           "J": 12, "M": 13, "P": 14, "Q": 15,
                           "R": 16, "V": 17, "W": 18, "X": 19}
    code_alfabeth = "23456789CFGHJMPQRVWX"
    condition = True
    while condition:
        code_generate = ''.join(random.choices(code_alfabeth, k=9))
        logging.info('appending a check digit')
        sum_values = 0
        for i in range(1, 10):
            sum_values += i * diccionary_alfabeth[code_generate[i - 1]]
        sum_values = sum_values % 20
        code_generate += list(diccionary_alfabeth.keys())[sum_values]
        logging.info('verificate if the generate code already exist')
        cur.execute('''
                SELECT
                    COUNT(*) AS `count`
                FROM
                    `Certificates`
                WHERE
                    `verification_code` = %s;
                ''', [code_generate])
        for row in cur:
            if row['count'] > 0:
                logging.info('Verification_code exist, Chosing other')
            else:
                condition = False
    return code_generate
