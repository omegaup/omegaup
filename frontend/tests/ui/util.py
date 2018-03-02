#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import os
import sys
import re

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

OMEGAUP_ROOT = os.path.normpath(os.path.join(__file__, '../../../..'))

# This contains all the Python path-hacking to a single file instead of
# spreading it throughout all the files.
sys.path.append(os.path.join(OMEGAUP_ROOT, 'stuff'))
# pylint: disable=wrong-import-position,unused-import
import database_utils  # NOQA


def add_students(driver, users, selector, typeahead_helper, submit_button):
    '''Add students to a recently :instance.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "%s")]' % selector)))).click()

    pattern = re.compile(r'[. #\/]')
    if pattern.search(submit_button):
        locator = By.CSS_SELECTOR
    else:
        locator = By.CLASS_NAME

    for user in users:
        driver.typeahead_helper(typeahead_helper, user)

        driver.wait.until(
            EC.element_to_be_clickable(
                (locator, (submit_button)))).click()
        driver.wait_for_page_loaded()
