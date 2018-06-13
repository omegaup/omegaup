#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import contextlib
import os
import sys
import re
import functools

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


def no_javascript_errors(*, path_whitelist=(), message_whitelist=()):
    '''Decorator for javascript errors'''
    def _internal(f):
        @functools.wraps(f)
        def _wrapper(driver, *args, **kwargs):
            '''Wrapper for javascript errors'''
            with assert_no_js_errors(driver, path_whitelist=path_whitelist,
                                     message_whitelist=message_whitelist):
                return f(driver, *args, **kwargs)
        return _wrapper
    return _internal


@contextlib.contextmanager
def assert_no_js_errors(driver, *, path_whitelist=(), message_whitelist=()):
    '''Shows in a list unexpected errors in javascript console'''
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
    '''Checks whether there is an error or warning in javascript console'''

    log = []
    for entry in driver.browser.get_log('browser'):
        if entry['level'] != 'SEVERE':
            continue
        if is_path_whitelisted(entry['message'], path_whitelist):
            continue
        if is_message_whitelisted(entry['message'], message_whitelist):
            continue

        log.append(entry['message'])

    return log


def is_path_whitelisted(message, path_whitelist):
    '''Checks whether URL in message is whitelisted.'''

    match = re.search(r'(https?://[^\s\'"]+)', message)
    url = urlparse(match.group(1))

    if not url:
        return False

    for whitelisted_path in path_whitelist + PATH_WHITELIST:
        if url.path == whitelisted_path:  # Compares params in the url
            return True

    return False


def is_message_whitelisted(message, message_whitelist):
    '''Checks whether string in message is whitelisted.

    It only compares strings between double or single quotes.
    '''

    match = re.search(r'(\'(?:[^\']|\\\')*\'|"(?:[^"]|\\")*")', message)

    if not match:
        return False

    quoted_string = match.group(1)[1:-1]  # Removing quotes of match regex.
    for whitelisted_message in message_whitelist + MESSAGE_WHITELIST:
        if whitelisted_message in quoted_string:
            return True

    return False
