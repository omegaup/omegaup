#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Fixtures for Selenium end-to-end tests.'''

import contextlib
import json
import os.path
import pytest
import re
import sys
import urllib

from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.wait import WebDriverWait

_DEFAULT_TIMEOUT = 2  # seconds
_HOME_PAGE_URL = 'http://localhost/'
_CI = os.environ.get('CONTINUOUS_INTEGRATION') == 'true'
_DIRNAME = os.path.dirname(__file__)

class Driver(object):
    '''Wraps the state needed to run a test.'''

    def __init__(self, browser, wait):
        self.browser = browser
        self.wait = wait
        self._url = _HOME_PAGE_URL

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
    def login(self, username='user', password='user'):
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
    '''Takes a screenshot and grabs console logs on test faiures.'''

    outcome = yield

    if not outcome.excinfo:
        return
    if not 'driver' in pyfuncitem.funcargs:
        return
    try:
        driver = pyfuncitem.funcargs['driver']
        if _CI:
            # geckodriver does not support getting logs:
            # https://github.com/mozilla/geckodriver/issues/284
            print(pyfuncitem.nodeid, driver.browser.get_screenshot_as_base64(),
                  file=sys.stderr)
        else:
            logs = driver.browser.get_log('browser')
            results_dir = os.path.join(_DIRNAME, 'results')
            os.makedirs(results_dir, exist_ok=True)
            driver.browser.get_screenshot_as_file(
                os.path.join(results_dir, 'webdriver_%s.png' % pyfuncitem.name))
            with open(os.path.join(results_dir, 'webdriver_%s.log' % pyfuncitem.name), 'w') as f:
                json.dump(logs, f, indent=2)
    except Exception as ex:
        print(ex)

@pytest.yield_fixture(scope='session')
def driver():
    '''Run tests using the selenium webdriver.'''

    if _CI:
        capabilities = {
            'tunnel-identifier': os.environ['TRAVIS_JOB_NUMBER'],
            'build': os.environ.get('TRAVIS_BUILD_NUMBER', ''),
            'tags': [os.environ.get('TRAVIS_PYTHON_VERSION', '3'), 'CI'],

            'browserName': 'chrome',
            'version': 'latest',
            'platform': 'Windows 10',
            'screenResolution': '1920x1080',
        }
        hub_url = 'http://%s:%s@ondemand.saucelabs.com:80/wd/hub' % (
            os.environ['SAUCE_USERNAME'],
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
        options.add_argument('--headless')
        browser = webdriver.Chrome(chrome_options=options)

    wait = WebDriverWait(browser, _DEFAULT_TIMEOUT,
                         poll_frequency=0.1)

    yield Driver(browser, wait)

    browser.quit()
