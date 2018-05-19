#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import contextlib
import os
import sys
import re

from urllib.parse import urlparse
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

OMEGAUP_ROOT = os.path.normpath(os.path.join(__file__, '../../../..'))

PATH_WHITELIST = ('/api/grader/status/', '/js/error_handler.js')
MESSAGE_WHITELIST = ('http://staticxx.facebook.com/', '/api/grader/status/')

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
def assert_no_javascript_errors(driver, path_whitelist=(),
                                message_whitelist=()):
    ''' Shows in a list unexpected errors in javascript console '''
    previous_logs = get_console_logs(driver, path_whitelist, message_whitelist)
    try:
        yield
    finally:
        current_logs = get_console_logs(driver, path_whitelist,
                                        message_whitelist)
        unexpected_errors = []

        if current_logs[:len(previous_logs)] == previous_logs:
            unexpected_errors = current_logs[len(previous_logs):]

        assert not unexpected_errors, '\n'.join(unexpected_errors)


def get_console_logs(driver, path_whitelist, message_whitelist):
    ''' Checks whether there is an error or warning in javascript console'''

    log = []
    for entry in driver.browser.get_log('browser'):
        if entry['level'] != 'SEVERE':
            continue
        path_matches = match_path(entry['message'], path_whitelist)
        message_matches = match_message(entry['message'], message_whitelist)
        if path_matches or message_matches:
            continue

        log.append(entry['message'])

    return log


def match_path(message, whitelist):
    '''
    Checks whether url in message is present in whitelist, it only compares
    params in the url if full_url is false
    '''
    full_whitelist = whitelist + PATH_WHITELIST

    if not full_whitelist:
        return False

    match = re.search(r'(https?://[^\s\'"]+)', message)
    url = urlparse(match.group(1))

    if not url:
        return False

    for string in full_whitelist:
        if url.path == string:  # Compares params in the url vs whitelist
            return True

    return False


def match_message(message, message_whitelist):
    '''
    Checks whether string in message is present in whitelist, it only compares
    strings between double quote or simple quote
    '''
    full_whitelist = message_whitelist + MESSAGE_WHITELIST

    if not full_whitelist:
        return False

    match = re.search(r'(\'(?:[^\']|\\\')*\'|"(?:[^"]|\\")*")', message)

    if not match:
        return False

    for string in full_whitelist:  # Compares string in quotes
        if match.group(1)[1:-1] == string:  # Removing quotes of match regex
            return True

    return False
