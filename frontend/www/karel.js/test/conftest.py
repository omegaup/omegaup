#!/usr/bin/python3
# -*- coding: utf-8 -*-
# type: ignore

'''Fixtures for Selenium end-to-end tests.'''

import contextlib
import json
import os.path
import sys
import time
import urllib

import pytest
from selenium import webdriver
from selenium.webdriver.support.wait import WebDriverWait


_DEFAULT_TIMEOUT = 3  # seconds
_CI = os.environ.get('CONTINUOUS_INTEGRATION') == 'true'
_DIRNAME = os.path.dirname(__file__)
_ROOT = os.path.abspath(os.path.join(_DIRNAME, '..'))
_SUCCESS = True
_WINDOW_SIZE = (1920, 1080)


class Driver:
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
    try:
        local_driver = pyfuncitem.funcargs['driver']
        try:
            logs = local_driver.browser.get_log('browser')
        # pylint: disable=bare-except
        except:  # noqa: bare-except
            # geckodriver does not support getting logs:
            # https://github.com/mozilla/geckodriver/issues/284
            logs = []
        if _CI:
            print(logs,
                  local_driver.get_screenshot_as_base64(),
                  file=sys.stderr)
            return
        results_dir = os.path.join(_DIRNAME, 'results')
        os.makedirs(results_dir, exist_ok=True)
        local_driver.browser.get_screenshot_as_file(
            os.path.join(results_dir, 'webdriver_%s.png' % pyfuncitem.name))
        with open(
                os.path.join(results_dir,
                             'webdriver_%s.log' % pyfuncitem.name), 'w') as f:
            json.dump(logs, f, indent=2)
    except Exception as ex:  # pylint: disable=broad-except
        print(ex)


def pytest_addoption(parser):
    '''Allow configuration of test invocation.'''

    parser.addoption('--browser', action='append', type=str, dest='browsers',
                     help='The browsers that the test will run against')
    parser.addoption('--disable-headless', action='store_false',
                     dest='headless', help='Show the browser window')


def pytest_generate_tests(metafunc):
    '''Parameterize the tests with the browsers.'''

    if not metafunc.config.option.browsers:
        metafunc.config.option.browsers = ['chrome', 'firefox']

    if 'driver' in metafunc.fixturenames:
        metafunc.parametrize('browser', metafunc.config.option.browsers,
                             scope='session')


@pytest.fixture
def driver(request, browser):
    '''Run tests using the selenium webdriver.'''

    if browser == 'chrome':
        options = webdriver.ChromeOptions()
        options.add_experimental_option('prefs',
                                        {'intl.accept_languages': 'en_US'})
        options.add_argument('--lang=en-US')
        options.headless = True
        browser = webdriver.Chrome(options=options)
    else:
        options = webdriver.firefox.options.Options()
        options.headless = True
        browser = webdriver.Firefox(options=options)
    browser.set_window_size(*_WINDOW_SIZE)

    browser.implicitly_wait(_DEFAULT_TIMEOUT)
    wait = WebDriverWait(browser, _DEFAULT_TIMEOUT,
                         poll_frequency=0.1)

    try:
        yield Driver(browser, wait, 'file://%s/' % _ROOT)
    finally:
        browser.quit()
