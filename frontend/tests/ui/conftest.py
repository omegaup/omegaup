#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Fixtures for Selenium end-to-end tests.'''

import contextlib
import json
import os.path
import pytest
import re
import sys
import time
import urllib

from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.support.wait import WebDriverWait

_DEFAULT_TIMEOUT = 2  # seconds
_CI = os.environ.get('CONTINUOUS_INTEGRATION') == 'true'
_DIRNAME = os.path.dirname(__file__)
_SUCCESS = True

class Driver(object):
    '''Wraps the state needed to run a test.'''

    def __init__(self, browser, wait, url):
        self.browser = browser
        self.wait = wait
        self._url = url
        self.id = str(int(time.time()))

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
        self.browser.find_element_by_xpath(
            '//a[contains(@href, "/login/")]').click()

        # Login screen
        self.wait.until(lambda _: self.browser.current_url != home_page_url)
        self.browser.find_element_by_id('user').send_keys(username)
        self.browser.find_element_by_id('pass').send_keys(password)
        self.browser.find_element_by_id('login_form').submit()
        self.wait.until(lambda _: self.browser.current_url == home_page_url)

        yield

        self.browser.get(self.url('/logout/?redirect=/'))
        self.wait.until(lambda _: self.browser.current_url == home_page_url)

@pytest.hookimpl(hookwrapper=True)
def pytest_pyfunc_call(pyfuncitem):
    '''Takes a screenshot and grabs console logs on test failures.'''

    global _SUCCESS

    outcome = yield

    if not outcome.excinfo:
        return
    _SUCCESS = False
    if not 'driver' in pyfuncitem.funcargs:
        return
    if _CI:
        # When running in CI, we have movies, screenshots and logs in Sauce Labs.
        return
    try:
        driver = pyfuncitem.funcargs['driver']
        logs = driver.browser.get_log('browser')
        results_dir = os.path.join(_DIRNAME, 'results')
        os.makedirs(results_dir, exist_ok=True)
        driver.browser.get_screenshot_as_file(
            os.path.join(results_dir, 'webdriver_%s.png' % pyfuncitem.name))
        with open(os.path.join(results_dir, 'webdriver_%s.log' % pyfuncitem.name), 'w') as f:
            json.dump(logs, f, indent=2)
    except Exception as ex:
        print(ex)

def pytest_addoption(parser):
    '''Allow configuration of test invocation.'''

    parser.addoption('--url', default=('http://localhost/' if not _CI else
                                       'http://localhost:8000/'),
                     help='The URL that the test will be run against')
    parser.addoption('--disable-headless', action='store_false',
                     dest='headless', help='Show the browser window')

@pytest.yield_fixture(scope='session')
def driver(request):
    '''Run tests using the selenium webdriver.'''

    if _CI:
        capabilities = {
            'tunnel-identifier': os.environ['TRAVIS_JOB_NUMBER'],
            'name': 'Travis CI run %s' % os.environ.get('TRAVIS_BUILD_NUMBER', ''),
            'build': os.environ.get('TRAVIS_BUILD_NUMBER', ''),
            'tags': [os.environ.get('TRAVIS_PYTHON_VERSION', '3'), 'CI'],
        }
        # Add browser configuration
        capabilities.update({
            'browserName': 'chrome',
            'version': 'latest',
            'platform': 'Windows 10',
            'screenResolution': '1920x1080',
        })
        hub_url = 'http://%s:%s@ondemand.saucelabs.com:80/wd/hub' % (
            os.environ.get('SAUCE_USERNAME', 'lhchavez'),
            os.environ['SAUCE_ACCESS_KEY']
        )
        browser = webdriver.Remote(desired_capabilities=capabilities,
                                   command_executor=hub_url)
    else:
        options = webdriver.ChromeOptions()
        options.binary_location = '/usr/bin/google-chrome'
        options.add_experimental_option('prefs', {'intl.accept_languages': 'en_US'})
        options.add_argument('--lang=en-US')
        options.add_argument('--window-size=1920x1080')
        if request.config.option.headless:
            options.add_argument('--headless')
        browser = webdriver.Chrome(chrome_options=options)

    wait = WebDriverWait(browser, _DEFAULT_TIMEOUT,
                         poll_frequency=0.1)

    yield Driver(browser, wait, request.config.option.url)

    if _CI:
        print(('\n\nYou can see the report at '
               'https://saucelabs.com/beta/tests/%s/commands') % browser.session_id,
              file=sys.stderr)
        try:
            browser.execute_script("sauce:job-result=%s" % str(_SUCCESS).lower())
        except WebDriverException:
            # Test is done. Just ignore the error.
            pass
    browser.quit()
