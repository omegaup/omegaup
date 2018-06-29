#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import contextlib
import logging
import os
import sys
import re
import functools

from urllib.parse import urlparse
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.select import Select

CI = os.environ.get('CONTINUOUS_INTEGRATION') == 'true'
OMEGAUP_ROOT = os.path.normpath(os.path.join(__file__, '../../../..'))

PATH_WHITELIST = ('/api/grader/status/', '/js/error_handler.js')
MESSAGE_WHITELIST = ('http://staticxx.facebook.com/', '/api/grader/status/')

# This contains all the Python path-hacking to a single file instead of
# spreading it throughout all the files.
sys.path.append(os.path.join(OMEGAUP_ROOT, 'stuff'))
# pylint: disable=wrong-import-position,unused-import
import database_utils  # NOQA


def add_students(driver, users, container_id, parent_xpath, submit_locator):
    '''Add students to a recently :instance.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "%s")]' % container_id)))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '#%s' % container_id)))

    for user in users:
        driver.typeahead_helper(parent_xpath, user)

        driver.wait.until(
            EC.element_to_be_clickable(submit_locator)).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//*[@id="%s"]//a[text()="%s"]' % (container_id, user))))


def create_run(driver, problem_alias, filename):
    '''Utility function to create a new run.'''
    logging.debug('Trying to submit new run for %s...', problem_alias)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[contains(@href, "#problems/%s")]' %
              problem_alias)))).click()
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[contains(@href, "new-run")]')))).click()

    _, language = os.path.splitext(filename)
    language = language.lstrip('.')
    Select(driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//select[@name = "language"]')))).select_by_value(language)

    resource_path = os.path.join(OMEGAUP_ROOT,
                                 'frontend/tests/resources/%s' % filename)
    with open(resource_path, 'r') as f:
        driver.browser.execute_script(
            'document.querySelector("#submit .CodeMirror")'
            '.CodeMirror.setValue(arguments[0]);',
            f.read())
    with driver.page_transition():
        driver.browser.find_element_by_css_selector(
            '#submit input[type="submit"]').submit()

    logging.debug('Run submitted.')


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


def annotate(f):
    '''Decorator to add annotations around the function call.'''
    @functools.wraps(f)
    def _wrapper(driver, *args, **kwargs):
        string_args = []
        for arg in args:
            string_args.append(repr(arg))
        for k, val in kwargs.items():
            string_args.append('%s=%r' % (k, val))
        funcstring = '%s(%s)' % (f.__name__, ', '.join(string_args))
        driver.annotate('begin %s' % funcstring)
        try:
            return f(driver, *args, **kwargs)
        finally:
            driver.annotate('end %s' % funcstring)
    return _wrapper


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
    try:
        browser_logs = driver.browser.get_log('browser')
    except WebDriverException:
        # Firefox does not support getting console logs.
        browser_logs = []
    for entry in browser_logs:
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
