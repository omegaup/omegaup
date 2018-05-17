#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import contextlib
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


def add_students(driver, users, selector, typeahead_helper, submit_locator):
    '''Add students to a recently :instance.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "%s")]' % selector)))).click()

    for user in users:
        driver.typeahead_helper(typeahead_helper, user)

        driver.wait.until(
            EC.element_to_be_clickable(submit_locator)).click()
        driver.wait_for_page_loaded()


@contextlib.contextmanager
def assert_no_javascript_errors(driver):
    ''' Shows in a list unexpected errors in javascript console '''
    previous_logs = get_console_logs(driver)
    try:
        yield
    finally:
        current_logs = get_console_logs(driver)
        unexpected_errors = list(set(previous_logs) - set(current_logs))
        if len(unexpected_errors) != 0:
            assert False, '\n'.join(unexpected_errors)


def get_console_logs(driver):
    ''' Checks whether there is an error or warning in javascript console'''
    log = []
    for entry in driver.browser.get_log('browser'):
        if entry['level'] != 'SEVERE':
            continue
        log.append((driver.browser.current_url, entry['message']))

    return log
