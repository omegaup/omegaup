#!/usr/bin/python3
# -*- coding: utf-8 -*-
# type: ignore

'''Utils for Selenium tests.'''

import contextlib
import inspect
import json
import logging
import os
import functools
import re
import sys
import traceback

from urllib.parse import urlparse
from typing import Iterator, List, NamedTuple, Text, Sequence
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

Identity = NamedTuple('Identity', [('username', Text), ('password', Text)])


class StatusBarIsDismissed:
    """A class that can wait for the status bar to be dismissed."""

    def __init__(self, status_element, message_class, already_opened=False):
        self.status_element = status_element
        self.counter = int(
            self.status_element.get_attribute('data-counter') or '0')
        self.clicked = False
        self.message_class = message_class
        self.already_opened = already_opened

    def _click_button(self):
        if self.clicked:
            return
        message_class = self.status_element.get_attribute('class')
        assert self.message_class in message_class, message_class
        self.status_element.find_element_by_css_selector(
            'button.close').click()
        self.clicked = True

    def __call__(self, driver):
        if self.already_opened:
            # The status was opened since the page was rendered. We can click
            # the button immediately.
            if not self.status_element.is_displayed():
                return self.status_element
            self._click_button()
            return False
        counter = int(self.status_element.get_attribute('data-counter') or '0')
        if counter in (self.counter, self.counter + 1):
            # We're still waiting for the status bar to open.
            return False
        if counter == self.counter + 2:
            # Status has finished animating. Time to click the close button.
            self._click_button()
            return False
        if counter == self.counter + 3:
            # Status is currently closing down.
            return False
        return self.status_element


# pylint: disable=too-many-arguments
def add_students(driver, users, *, tab_xpath,
                 container_xpath, parent_xpath, submit_locator):
    '''Add students to a recently :instance.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, tab_xpath))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, container_xpath)))

    for user in users:
        driver.typeahead_helper(parent_xpath, user)

        with dismiss_status(driver):
            driver.wait.until(
                EC.element_to_be_clickable(submit_locator)).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '%s//a[text()="%s"]' % (container_xpath, user))))


@contextlib.contextmanager
def dismiss_status(driver, *, message_class='', already_opened=False):
    '''Closes the status bar and waits for it to disappear.'''
    status_element = driver.wait.until(
        EC.presence_of_element_located((By.ID, 'status')))
    status_bar_is_dismissed = StatusBarIsDismissed(
        status_element,
        message_class=message_class,
        already_opened=already_opened)
    try:
        yield
    finally:
        driver.wait.until(status_bar_is_dismissed)


def create_run(driver, problem_alias, filename):
    '''Utility function to create a new run.'''
    logging.debug('Trying to submit new run for %s...', problem_alias)

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
    original_url = driver.browser.current_url
    driver.browser.find_element_by_css_selector(
        '#submit input[type="submit"]').submit()
    driver.wait.until(EC.url_changes(original_url))

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
        signature = inspect.signature(f)
        args_names = [param.name for param in signature.parameters.values()]
        string_args = []
        # Skipping the first arg, since it was already captured by driver.
        for param, val in zip(args_names[1:], args):
            string_args.append('%s=%r' % (param, val))
        for k, val in kwargs.items():
            string_args.append('%s=%r' % (k, val))
        funcstring = '%s(%s)' % (f.__name__, ', '.join(string_args))
        driver.annotate('begin %s' % funcstring)
        try:
            return f(driver, *args, **kwargs)
        except:  # noqa: bare-except
            driver.annotate(
                ''.join(traceback.format_exception(*sys.exc_info())).rstrip(),
                level=logging.ERROR)
            raise
        finally:
            driver.annotate('end %s' % funcstring)
    return _wrapper


@contextlib.contextmanager
def assert_js_errors(driver,
                     *,
                     expected_paths: Sequence[str] = (),
                     expected_messages: Sequence[str] = ()) -> Iterator[None]:
    '''Shows in a list unexpected errors in javascript console'''
    assert expected_paths or expected_messages, (
        'Both `expected_paths` and `expected_messages` cannot be empty')
    assert not driver.log_collector.empty(), (
        'assert_js_errors() cannot be called without an assert_no_js_errors()')
    driver.log_collector.push()
    try:
        yield
    finally:
        matched_errors = []
        unmatched_errors = []
        seen_paths: List[bool] = [False] * len(expected_paths)
        seen_messages: List[bool] = [False] * len(expected_messages)
        for entry in driver.log_collector.pop():
            matched = False
            for i, path in enumerate(expected_paths):
                if not path_matches(entry['message'], (path, )):
                    continue
                matched = True
                seen_paths[i] = True
            for i, message in enumerate(expected_messages):
                if not message_matches(entry['message'], (message, )):
                    continue
                matched = True
                seen_messages[i] = True
            if matched:
                matched_errors.append(entry)
            else:
                unmatched_errors.append(entry)
        driver.log_collector.extend(unmatched_errors)

        missed_paths = [
            path for path, seen in zip(expected_paths, seen_paths) if not seen
        ]
        missed_messages = [
            message for message, seen in zip(expected_messages, seen_messages)
            if not seen
        ]
        if missed_paths or missed_messages:
            raise Exception(
                ('Some messages were not matched\n'
                 '\tMatched errors:\n\t\t{matched_errors}\n'
                 '\tUnmatched errors:\n\t\t{unmatched_errors}\n'
                 '\tMissed paths:\n\t\t{missed_paths}\n'
                 '\tMissed messages:\n\t\t{missed_messages}').format(
                     matched_errors='\n'.join(
                         json.dumps(entry) for entry in matched_errors),
                     unmatched_errors='\n'.join(
                         json.dumps(entry) for entry in unmatched_errors),
                     missed_paths='\n'.join(missed_paths),
                     missed_messages='\n'.join(missed_messages)))


@contextlib.contextmanager
def assert_no_js_errors(
        driver,
        *,
        path_whitelist: Sequence[str] = (),
        message_whitelist: Sequence[str] = ()) -> Iterator[None]:
    '''Shows in a list unexpected errors in javascript console'''
    driver.log_collector.push()
    try:
        yield
    finally:
        original_errors = []
        unexpected_errors = []
        for entry in driver.log_collector.pop():
            original_errors.append(entry)
            if path_matches(entry['message'], path_whitelist + PATH_WHITELIST):
                continue
            if message_matches(entry['message'],
                               message_whitelist + MESSAGE_WHITELIST):
                continue
            unexpected_errors.append(entry['message'])
        if unexpected_errors:
            raise Exception(
                ('There were unexpected messages\n'
                 '\tOriginal errors:\n\t\t{original_errors}\n'
                 '\tUnexpected errors:\n\t\t{unexpected_errors}').format(
                     original_errors='\n'.join(
                         json.dumps(message) for message in original_errors),
                     unexpected_errors='\n'.join(unexpected_errors)))


def path_matches(message: str, path_list: Sequence[str]) -> bool:
    '''Checks whether URL in message matches the expected list.'''

    match = re.search(r'(https?://[^\s\'"]+)', message)
    if not match:
        return False
    url = urlparse(match.group(1))
    if not url:
        return False

    for whitelisted_path in path_list:
        if url.path == whitelisted_path:  # Compares params in the url
            return True

    return False


def message_matches(message: str, message_list: Sequence[str]) -> bool:
    '''Checks whether string in message is whitelisted.

    It compares strings between double or single quotes, or the trailing part
    of the message. This last part is needed because SauceLabs for some reason
    sometimes does not quote messages that are manually injected through
    console.error().
    '''

    match = re.search(r'(\'(?:[^\']|\\\')*\'|"(?:[^"]|\\")*")', message)
    if match:
        quoted_string = match.group(1)[1:-1]  # Removing quotes of match regex.
        for whitelisted_message in message_list:
            if quoted_string == whitelisted_message:
                return True

        return False

    # No quoted messages found, so let's try to do a suffix match.
    for whitelisted_message in message_list:
        if message.endswith(whitelisted_message):
            return True

    return False


def check_scoreboard_events(driver, alias, url, *, num_elements, scoreboard):
    '''Verifies chart is correctly generated'''

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//tr/td/a[contains(@href, "%s")][text()="%s"]' %
                 (alias, scoreboard)))).click()
    assert (url in driver.browser.current_url), driver.browser.current_url

    series = 'highcharts-series-group'
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//*[name()="svg"]/*[contains(@class, "%s")]' % (series))))

    scoreboard_events = driver.browser.find_elements_by_xpath(
        '//*[name()="svg"]/*[contains(@class, "%s")]/*[contains(@class'
        ', "highcharts-tracker")]' % series)
    assert len(scoreboard_events) == num_elements, len(scoreboard_events)


def create_group(driver, group_title, description):
    ''' Creates a group as an admin and returns a generated group alias. '''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//div[@id="root"]//li[contains(concat(" ", '
             'normalize-space(@class), " "), " nav-contests ")]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//div[@id="root"]//li[contains(concat(" ", '
                  'normalize-space(@class), " "), " nav-contests ")]//a[@href '
                  '= "/group/"]')))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[@href = "/group/new/"]')))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//input[@name = "title"]'))).send_keys(group_title)
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//textarea[@name = "description"]'))).send_keys(description)

    with driver.page_transition():
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//form[contains(concat(" ", normalize-space(@class), '
                 '" "), " new-group-form ")]'))).submit()

    group_alias = re.search(r'/group/([^/]*)/edit/',
                            driver.browser.current_url).group(1)

    return group_alias


def add_identities_group(driver, group_alias):
    '''Upload csv and add identities into the group'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//div[@id="root"]//li[contains(concat(" ", '
             'normalize-space(@class), " "), " nav-contests ")]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//div[@id="root"]//li[contains(concat(" ", '
                  'normalize-space(@class), " "), " nav-contests ")]//a[@href '
                  '= "/group/"]')))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "/group/%s/edit/")]' %
                  group_alias)))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[contains(@href, "#identities")]'))).click()
    identities_element = driver.browser.find_element_by_name('identities')
    identities_element.send_keys(os.path.join(
        OMEGAUP_ROOT, 'frontend/tests/resources/identities.csv'))

    username_elements = driver.browser.find_elements_by_xpath(
        '//table[contains(concat(" ", normalize-space(@class), " "), " '
        'identities-table ")]/tbody/tr/td[contains(concat(" ", '
        'normalize-space(@class), " "), " username ")]/strong')
    password_elements = driver.browser.find_elements_by_xpath(
        '//table[contains(concat(" ", normalize-space(@class), " "), " '
        'identities-table ")]/tbody/tr/td[contains(concat(" ", '
        'normalize-space(@class), " "), " password ")]')
    usernames = [username.text for username in username_elements]
    passwords = [password.text for password in password_elements]

    identities = [Identity(*x) for x in zip(usernames, passwords)]

    create_identities_button = driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//button[starts-with(@name, "create-identities")]')))
    with dismiss_status(driver, message_class='success'):
        create_identities_button.click()

    return identities
