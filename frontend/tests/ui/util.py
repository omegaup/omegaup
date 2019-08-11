#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Utils for Selenium tests.'''

import contextlib
import inspect
import logging
import os
import functools
import re
import sys
import traceback

from urllib.parse import urlparse
from typing import NamedTuple, Text
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

        driver.wait.until(
            EC.element_to_be_clickable(submit_locator)).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '%s//a[text()="%s"]' % (container_xpath, user))))


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
    with driver.page_transition():
        driver.browser.find_element_by_css_selector(
            '#submit input[type="submit"]').submit()

    logging.debug('Run submitted.')


def no_javascript_errors(*, path_whitelist=(), message_list=()):
    '''Decorator for javascript errors'''
    def _internal(f):
        @functools.wraps(f)
        def _wrapper(driver, *args, **kwargs):
            '''Wrapper for javascript errors'''
            with assert_no_js_errors(driver, path_whitelist=path_whitelist,
                                     message_list=message_list):
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
def assert_no_js_errors(driver, *, path_whitelist=(), message_list=()):
    '''Shows in a list unexpected errors in javascript console'''
    driver.log_collector.push()
    try:
        yield
    finally:
        unexpected_errors = []
        for entry in driver.log_collector.pop():
            if 'WebSocket' in entry['message']:
                # Travis does not have broadcaster yet.
                continue
            if is_path_whitelisted(entry['message'], path_whitelist):
                continue
            if message_matches(entry['message'], message_list):
                continue
            unexpected_errors.append(entry['message'])
        assert not unexpected_errors, '\n'.join(unexpected_errors)


@contextlib.contextmanager
def assert_js_errors(driver, *, message=()):
    '''Shows in a list unexpected errors in javascript console'''
    expected_error_is_found = True
    driver.log_collector.push()
    try:
        yield
    finally:
        for entry in driver.log_collector.pop():
            if message_matches(entry['message'], message):
                expected_error_is_found = True
        assert expected_error_is_found, '%s was not found' % message


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


def message_matches(message, message_list):
    '''Checks whether string in message is listed.

    It only compares strings between double or single quotes.
    '''

    match = re.search(r'(\'(?:[^\']|\\\')*\'|"(?:[^"]|\\")*")', message)

    if not match:
        return False

    quoted_string = match.group(1)[1:-1]  # Removing quotes of match regex.
    for listed_message in message_list + MESSAGE_WHITELIST:
        if quoted_string == listed_message:
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
            (By.ID, 'nav-contests'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-contests"]'
                  '//a[@href = "/group/"]')))).click()
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
            (By.ID, 'nav-contests'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-contests"]'
                  '//a[@href = "/group/"]')))).click()
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
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//div[contains(concat(" ", normalize-space(@class), " "), '
             '" upload-csv ")]/div/a'))).click()

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
    create_identities_button.click()
    message = driver.wait.until(
        EC.visibility_of_element_located((By.ID, 'status')))
    message_class = message.get_attribute('class')
    assert 'success' in message_class, message_class

    return identities


@contextlib.contextmanager
def create_contest(driver, contest_alias, scoreboard_time_percent=100,
                   has_privileges=True):
    '''Creates a new contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.ID, 'nav-contests'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-contests"]'
                  '//a[@href = "/contest/new/"]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.ID, ('title')))).send_keys(contest_alias)
    driver.browser.find_element_by_id('alias').send_keys(
        contest_alias)
    driver.browser.find_element_by_id('description').send_keys(
        'contest description')
    scoreboard_element = driver.browser.find_element_by_id('scoreboard')
    scoreboard_element.clear()
    scoreboard_element.send_keys(scoreboard_time_percent)

    if has_privileges:
        with driver.page_transition():
            driver.browser.find_element_by_tag_name('form').submit()
    else:
        submit_element = driver.browser.find_element_by_tag_name('form')
        assert_get_alert(driver, submit_element)


@contextlib.contextmanager
def create_course(driver, course_alias, school_name, has_privileges=True):
    '''Creates one course with a new school.'''

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "/schools/"]'))).click()

    if has_privileges is False:
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH, '//a[@href = "/course/"]'))).click()

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, ('//a[@href = "/course/new/"]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CLASS_NAME, ('name')))).send_keys(course_alias)
    driver.browser.find_element_by_class_name('alias').send_keys(
        course_alias)
    driver.typeahead_helper('*[contains(@class, "omegaup-course-details")]',
                            school_name,
                            select_suggestion=False)
    driver.browser.find_element_by_tag_name('textarea').send_keys(
        'course description')

    if has_privileges:
        with driver.page_transition():
            driver.browser.find_element_by_tag_name('form').submit()
        assert (('/course/%s/edit/' % course_alias) in
                driver.browser.current_url), driver.browser.current_url
    else:
        submit_element = driver.browser.find_element_by_tag_name('form')
        assert_get_alert(driver, submit_element)


@contextlib.contextmanager
def create_problem(driver, problem_alias, has_privileges=True):
    '''Create a problem.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.ID, 'nav-problems'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-problems"]'
                  '//a[@href = "/problem/new/"]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//input[@name = "alias"]'))).send_keys(problem_alias)
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//input[@name = "title"]'))).send_keys(problem_alias)
    input_limit = driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//input[@name = "input_limit"]')))
    input_limit.clear()
    input_limit.send_keys('1024')
    # Alias should be set automatically
    driver.browser.find_element_by_name('source').send_keys('test')
    # Make the problem public
    driver.browser.find_element_by_xpath(
        '//input[@name="visibility" and @value = "1"]').click()
    contents_element = driver.browser.find_element_by_name(
        'problem_contents')
    contents_element.send_keys(os.path.join(
        OMEGAUP_ROOT, 'frontend/tests/resources/triangulos.zip'))

    if has_privileges:
        with driver.page_transition(wait_for_ajax=False):
            contents_element.submit()
        assert (('/problem/%s/edit/' % problem_alias) in
                driver.browser.current_url), driver.browser.current_url
    else:
        assert_get_alert(driver, contents_element)


def assert_get_alert(driver, submit_element):
    ''' When an identity tries to create something it should get a message. '''

    submit_element.submit()

    message = driver.wait.until(
        EC.visibility_of_element_located((By.ID, 'status')))
    message_class = message.get_attribute('class')

    assert 'danger' in message_class, message_class


@contextlib.contextmanager
def assert_page_not_found(driver, page):
    ''' Asserts user or identity does not have access to the page. '''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.ID, 'nav-%ss' % (page)))).click()

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-%ss"]'
                  '//a[@href = "/%s/mine/"]' % (page, page))))).click()

    error_page = driver.wait.until(
        EC.visibility_of_element_located((By.XPATH, '//h1/strong')))
    error_symbol = error_page.get_attribute('title')

    assert 'omega' in error_symbol, error_symbol

    error_page = driver.wait.until(
        EC.visibility_of_element_located((By.XPATH, '//h1/span')))
    error_down = error_page.get_attribute('title')

    assert 'Down' in error_down, error_down
