#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Fixtures for Selenium end-to-end tests.'''

import contextlib
import json
import os.path
import sys
import time
import urllib

import pytest

from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.wait import WebDriverWait

_DEFAULT_TIMEOUT = 4  # seconds
_CI = os.environ.get('CONTINUOUS_INTEGRATION') == 'true'
_DIRNAME = os.path.dirname(__file__)
_SUCCESS = True
_WINDOW_SIZE = (1920, 1080)


class Driver(object):
    '''Wraps the state needed to run a test.'''

    def __init__(self, browser, wait, url):
        self.browser = browser
        self.wait = wait
        self._next_id = 0
        self._url = url

    def generate_id(self):
        '''Generates a relatively unique id.'''

        self._next_id += 1
        return '%d_%d' % (int(time.time()), self._next_id)

    def url(self, path):
        '''Gets the full url for :path.'''

        return urllib.parse.urljoin(self._url, path)

    def eval_script(self, script):
        '''Returns the evaluation of the JavaScript expression |script|'''

        return self.browser.execute_script('return (%s);' % script)

    def assert_script(self, script):
        '''Asserts that evaluating the JavaScript |script| returns true.'''

        assert self.browser.execute_script('return !!(%s);' % script), \
            'Evaluation of `%s` returned false' % script

    def assert_script_equal(self, script, value):
        '''Asserts that evaluating the JavaScript |script| returns true.'''

        assert self.eval_script(script) == value, script

    @contextlib.contextmanager
    def ajax_page_transition(self):
        '''Waits for an AJAX-initiated page transition to finish.'''

        prev_url = self.browser.current_url
        yield
        self.wait.until(lambda _: self.browser.current_url != prev_url)
        self.wait_for_page_loaded()

    def wait_for_page_loaded(self):
        '''Waits for the page to be loaded.'''

        self.wait.until(
            lambda _: self.browser.execute_script(
                'return document.readyState;') == 'complete')
        self.wait.until(
            lambda _: self.browser.execute_script('return !jQuery.active;'))

    def typeahead_helper(self, parent_selector, value, select_suggestion=True):
        '''Helper to interact with Typeahead elements.'''

        tt_input = self.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR,
                 '%s input.tt-input' % parent_selector)))
        for value_char in value:
            tt_input.send_keys(value_char)

        if not select_suggestion:
            return

        self.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 '%s .tt-suggestion.tt-selectable' % parent_selector)
            )).click()

    @contextlib.contextmanager
    def login_user(self):
        '''Logs in as a user, and logs out when out of scope.'''

        with self.login('user', 'user'):
            yield

    @contextlib.contextmanager
    def login_admin(self):
        '''Logs in as an admin, and logs out when out of scope.'''

        with self.login('omegaup', 'omegaup'):
            yield

    @contextlib.contextmanager
    def login(self, username, password):
        '''Logs in as :username, and logs out when out of scope.'''

        # Home page
        home_page_url = self.url('/')
        self.browser.get(home_page_url)
        self.wait_for_page_loaded()
        self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[starts-with(@href, "/login/")]'))).click()

        # Login screen
        self.wait.until(lambda _: self.browser.current_url != home_page_url)
        self.wait_for_page_loaded()

        self.wait.until(
            EC.visibility_of_element_located(
                (By.ID, 'user'))).send_keys(username)
        self.browser.find_element_by_id('pass').send_keys(password)
        with self.ajax_page_transition():
            self.browser.find_element_by_id('login_form').submit()

        try:
            yield
        finally:
            self.browser.get(self.url('/logout/?redirect=/'))
            self.wait.until(lambda _: self.browser.current_url ==
                            home_page_url)
            self.wait_for_page_loaded()


@pytest.hookimpl(hookwrapper=True)
def pytest_pyfunc_call(pyfuncitem):
    '''Takes a screenshot and grabs console logs on test failures.'''

    global _SUCCESS  # pylint: disable=global-statement

    outcome = yield

    if not outcome.excinfo:
        return
    _SUCCESS = False
    if 'driver' not in pyfuncitem.funcargs:
        return
    if _CI:
        # When running in CI, we have movies, screenshots and logs in
        # Sauce Labs.
        return
    try:
        current_driver = pyfuncitem.funcargs['driver']
        try:
            logs = current_driver.browser.get_log('browser')
        except:  # pylint: disable=bare-except
            # geckodriver does not support getting logs:
            # https://github.com/mozilla/geckodriver/issues/284
            logs = []
        results_dir = os.path.join(_DIRNAME, 'results')
        os.makedirs(results_dir, exist_ok=True)
        current_driver.browser.get_screenshot_as_file(
            os.path.join(results_dir, 'webdriver_%s.png' % pyfuncitem.name))
        logpath = os.path.join(results_dir,
                               'webdriver_%s.log' % pyfuncitem.name)
        with open(logpath, 'w') as logfile:
            json.dump(logs, logfile, indent=2)
    except Exception as ex:  # pylint: disable=broad-except
        print(ex)


def pytest_addoption(parser):
    '''Allow configuration of test invocation.'''

    parser.addoption('--browser', action='append', type=str, dest='browsers',
                     help='The browsers that the test will run against')
    parser.addoption('--url', default=('http://localhost/' if not _CI else
                                       'http://localhost:8000/'),
                     help='The URL that the test will be run against')
    parser.addoption('--disable-headless', action='store_false',
                     dest='headless', help='Show the browser window')


def pytest_generate_tests(metafunc):
    '''Parameterize the tests with the browsers.'''

    if not metafunc.config.option.browsers:
        metafunc.config.option.browsers = ['chrome', 'firefox']

    if 'driver' in metafunc.fixturenames:
        metafunc.parametrize('browser_name', metafunc.config.option.browsers,
                             scope='session')


def _get_browser(request, browser_name):
    '''Gets a browser object from the request parameters.'''

    if _CI:
        capabilities = {
            'tunnel-identifier': os.environ['TRAVIS_JOB_NUMBER'],
            'name': 'Travis CI run %s[%s]' % (
                os.environ.get('TRAVIS_BUILD_NUMBER', ''), browser_name),
            'build': os.environ.get('TRAVIS_BUILD_NUMBER', ''),
            'tags': [os.environ.get('TRAVIS_PYTHON_VERSION', '3'), 'CI'],
        }
        # Add browser configuration
        capabilities.update({
            'browserName': browser_name,
            'version': 'latest',
            'platform': 'Windows 10',
            'screenResolution': '%dx%d' % _WINDOW_SIZE,
        })
        hub_url = 'http://%s:%s@ondemand.saucelabs.com:80/wd/hub' % (
            os.environ.get('SAUCE_USERNAME', 'lhchavez'),
            os.environ['SAUCE_ACCESS_KEY']
        )
        return webdriver.Remote(desired_capabilities=capabilities,
                                command_executor=hub_url)
    if browser_name == 'chrome':
        chrome_options = webdriver.ChromeOptions()
        chrome_options.binary_location = '/usr/bin/google-chrome'
        chrome_options.add_experimental_option(
            'prefs', {'intl.accept_languages': 'en_US'})
        chrome_options.add_argument('--lang=en-US')
        if request.config.option.headless:
            chrome_options.add_argument('--headless')
        chrome_browser = webdriver.Chrome(chrome_options=chrome_options)
        chrome_browser.set_window_size(*_WINDOW_SIZE)
        return chrome_browser
    # pylint: disable=line-too-long
    firefox_capabilities = webdriver.common.desired_capabilities.DesiredCapabilities.FIREFOX  # NOQA
    firefox_capabilities['marionette'] = True
    firefox_options = webdriver.firefox.options.Options()
    if request.config.option.headless:
        firefox_options.add_argument('-headless')
    firefox_browser = webdriver.Firefox(
        capabilities=firefox_capabilities, firefox_options=firefox_options)
    firefox_browser.set_window_size(*_WINDOW_SIZE)
    return firefox_browser


@pytest.yield_fixture(scope='session')
def driver(request, browser_name):
    '''Run tests using the selenium webdriver.'''

    browser = _get_browser(request, browser_name)
    browser.implicitly_wait(_DEFAULT_TIMEOUT)
    wait = WebDriverWait(browser, _DEFAULT_TIMEOUT,
                         poll_frequency=0.1)

    try:
        yield Driver(browser, wait, request.config.option.url)
    finally:
        if _CI:
            print(('\n\nYou can see the report at '
                   'https://saucelabs.com/beta/tests/%s/commands') %
                  browser.session_id, file=sys.stderr)
            try:
                browser.execute_script("sauce:job-result=%s" %
                                       str(_SUCCESS).lower())
            except WebDriverException:
                # Test is done. Just ignore the error.
                pass
        browser.quit()
