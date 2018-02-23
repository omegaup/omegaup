#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import os
import sys

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

OMEGAUP_ROOT = os.path.normpath(os.path.join(__file__, '../../../..'))

# This contains all the Python path-hacking to a single file instead of
# spreading it throughout all the files.
sys.path.append(os.path.join(OMEGAUP_ROOT, 'stuff'))
# pylint: disable=wrong-import-position,unused-import
import database_utils  # NOQA


def add_students(driver, users, instance):
    '''Add students to a recently :instance.'''

    if instance == 'course':
        selector = 'students'
        typeahead_helper = '.omegaup-course-addstudent'
        submit_button = '.omegaup-course-addstudent form button[type=submit]'
    else:
        selector = '#contestants'
        typeahead_helper = selector
        submit_button = 'user-add-single'

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "%s")]' % selector)))).click()

    for user in users:
        driver.typeahead_helper(typeahead_helper, user)

        if instance == 'course':
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, (submit_button)))).click()
        else:
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CLASS_NAME, (submit_button)))).click()
        driver.wait_for_page_loaded()
