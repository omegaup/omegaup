#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import contextlib
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
def assert_no_javascript_errors(driver, whitelist=None):
    ''' Shows in a list unexpected errors in javascript console '''
    previous_logs = get_console_logs(driver, whitelist)
    try:
        yield
    finally:
        current_logs = get_console_logs(driver, whitelist)
        unexpected_errors = []

        if current_logs[:len(previous_logs)] == previous_logs:
            unexpected_errors = current_logs[len(previous_logs):]

        assert not unexpected_errors, '\n'.join(unexpected_errors)


def get_console_logs(driver, whitelist):
    ''' Checks whether there is an error or warning in javascript console'''

    log = []
    for entry in driver.browser.get_log('browser'):
        if entry['level'] != 'SEVERE' or matches(entry['message'], whitelist):
            continue
        log.append(entry['message'])

    return log


def matches(message, whitelist):
    ''' Gets whether string is in whitelist '''
    if not whitelist:
        return False

    match = re.search(r'((https?:)\/\/)([^:\/\s]+)(?::(\d*))?(?:([^\s?#]+)?)',
                      message)
    if match:
        for string in whitelist:
            if match.group(5) == string:
                return True

    match = re.search(r' * *["\']([^"\']*)', message)
    if not match:
        return False
    for string in whitelist:
        if match.group(1) == string:
            return True

    return False
