#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Fixtures for Selenium end-to-end tests.'''

import contextlib
import json
import logging
import os.path
import sys
import time
import urllib

import pytest

from selenium import webdriver
from selenium.common.exceptions import WebDriverException, TimeoutException
from selenium.webdriver.common.by import By
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.wait import WebDriverWait

from ui import util

_DEFAULT_TIMEOUT = 10  # seconds
_DIRNAME = os.path.dirname(__file__)
_SUCCESS = True
_WINDOW_SIZE = (1920, 1080)
_BLANK = '/404.html'  # An path that returns 200 in both Firefox and Chrome.


class JavaScriptLogCollector:
    '''Collects JavaScript errors from the log.'''

    def __init__(self, dr):
        self.driver = dr
        self._log_index = 0
        self._log_stack = [[]]

    def push(self):
        '''Pushes a new error list.'''
        self._log_stack[-1].extend(self._get_last_console_logs())
        self._log_stack.append([])

    def pop(self):
        '''Grabs the last error list.'''
        self._log_stack[-1].extend(self._get_last_console_logs())
        return self._log_stack.pop()

    def _get_last_console_logs(self):
        '''Grabs the latest set of JavaScript logs and clears them.'''
        try:
            browser_logs = self.driver.browser.get_log('browser')
        except WebDriverException:
            # Firefox does not support getting console logs.
            browser_logs = []
        current_index, self._log_index = self._log_index, len(browser_logs)
        for entry in browser_logs[current_index:]:
            if entry['level'] != 'SEVERE':
                continue
            logging.info(entry)
            yield entry


class Driver:  # pylint: disable=too-many-instance-attributes
    '''Wraps the state needed to run a test.'''

    # pylint: disable=too-many-arguments
    def __init__(self, browser, wait, url, worker_id, options):
        self.browser = browser
        self.wait = wait
        self._worker_id = worker_id
        self._next_id = 0
        self._url = url
        self.options = options
        self.user_username = self.create_user()
        self.admin_username = self.create_user(admin=True)
        self.log_collector = JavaScriptLogCollector(self)

    def generate_id(self):
        '''Generates a relatively unique id.'''

        self._next_id += 1
        return '%s_%d_%d' % (self._worker_id, int(time.time()), self._next_id)

    def url(self, path):
        '''Gets the full url for :path.'''

        return urllib.parse.urljoin(self._url, path)

    def mysql_auth(self):
        '''Gets the authentication string for MySQL.'''

        return util.database_utils.authentication(
            config_file=self.options.mysql_config_file,
            username=self.options.username, password=self.options.password)

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
    def page_transition(self, wait_for_ajax=True):
        '''Waits for a page transition to finish.'''

        prev_url = self.browser.current_url
        logging.debug('Waiting for the URL to change from %s', prev_url)
        yield
        self.wait.until(lambda _: self.browser.current_url != prev_url)
        logging.debug('New URL: %s', self.browser.current_url)
        if wait_for_ajax:
            self._wait_for_page_loaded()

    def _wait_for_page_loaded(self):
        '''Waits for the page to be loaded.'''

        try:
            def _is_page_loaded(*_):
                return self.browser.execute_script(
                    'return document.readyState;') == 'complete'
            if _is_page_loaded():
                return
            logging.debug('Waiting for the page to finish loading...')
            self.wait.until(_is_page_loaded)
            logging.debug('Page loaded')
        except TimeoutException as ex:
            raise Exception('document ready state still %s' %
                            self.browser.execute_script(
                                'return document.readyState;')) from ex
        t0 = time.time()
        try:
            def _is_jquery_done(*_):
                return self.browser.execute_script(
                    'return jQuery.active;') == 0
            logging.debug('Waiting for all the pending AJAXcalls to finish...')
            self.wait.until(_is_jquery_done)
            logging.debug('AJAX calls done.')
        except TimeoutException as ex:
            raise Exception('%d AJAX calls still active after %f s' %
                            (self.browser.execute_script(
                                'return jQuery.active;'),
                             time.time() - t0)) from ex

    def typeahead_helper(self, parent_xpath, value, select_suggestion=True):
        '''Helper to interact with Typeahead elements.'''

        tt_input = self.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//%s//input[contains(@class, "tt-input")]' % parent_xpath)))
        tt_input.click()
        tt_input.send_keys(value)

        if not select_suggestion:
            return

        self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//%s//div[@data-value = "%s"]' %
                 (parent_xpath, value)))).click()

    @contextlib.contextmanager
    def login_user(self):
        '''Logs in as a user, and logs out when out of scope.'''

        with self.login(self.user_username, 'user'):
            yield

    @contextlib.contextmanager
    def login_admin(self):
        '''Logs in as an admin, and logs out when out of scope.'''

        with self.login(self.admin_username, 'omegaup'):
            yield

    @contextlib.contextmanager
    def login(self, username, password):
        '''Logs in as :username, and logs out when out of scope.'''

        # Home page
        logging.debug('Logging in as %s...', username)
        home_page_url = self.url('/')
        self.browser.get(home_page_url)
        self._wait_for_page_loaded()
        self.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[starts-with(@href, "/login/")]'))).click()

        # Login screen
        self.wait.until(lambda _: self.browser.current_url != home_page_url)
        self._wait_for_page_loaded()

        self.wait.until(
            EC.visibility_of_element_located(
                (By.ID, 'user'))).send_keys(username)
        self.browser.find_element_by_id('pass').send_keys(password)
        with self.page_transition():
            self.browser.find_element_by_id('login_form').submit()

        try:
            yield
        finally:
            # Wait until there are no more pending requests to avoid races
            # where those requests return 401. Navigate to a blank page just
            # for good measure and to enforce that there are two URL changes.
            self._wait_for_page_loaded()
            with self.page_transition():
                self.browser.get(self.url(_BLANK))
            with self.page_transition():
                self.browser.get(self.url('/logout/?redirect=/'))
            assert self.browser.current_url == home_page_url, (
                'Invalid URL redirect. Expected %s, got %s' % (
                    home_page_url, self.browser.current_url))

    @util.no_javascript_errors()
    @util.annotate
    def register_user(self, user, passw):
        '''Creates user :user and logs out when out of scope.'''

        # Home page
        home_page_url = self.url('/')
        with self.page_transition():
            self.browser.get(self.url(_BLANK))
        with self.page_transition():
            self.browser.get(home_page_url)
        with self.page_transition():
            self.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     '//a[contains(@href, "/login/")]'))).click()

        # Login screen
        self.browser.find_element_by_id('reg_username').send_keys(user)
        self.browser.find_element_by_id('reg_email').send_keys(
            'email_%s@localhost.localdomain' % user)
        self.browser.find_element_by_id('reg_pass').send_keys(passw)
        self.browser.find_element_by_id('reg_pass2').send_keys(passw)
        with self.page_transition():
            self.browser.find_element_by_id('register-form').submit()

        # Home screen
        with self.page_transition():
            self.browser.get(self.url(_BLANK))
        with self.page_transition():
            self.browser.get(self.url('/logout/?redirect=/'))
        assert self.browser.current_url == home_page_url, (
            'Invalid URL redirect. Expected %s, got %s' % (
                home_page_url, self.browser.current_url))

    def annotate(self, message, level=logging.INFO):
        '''Add an annotation to the run's log.'''

        if util.CI:
            self.browser.execute_script("sauce:context=%s" % message)
        logging.log(level, message)

    def update_run_score(self, run_id, verdict, score):
        '''Set verdict and score of specified run'''

        util.database_utils.mysql(
            ('''
            UPDATE
                `Runs`
            SET
                `score` = %s,
                `contest_score` = %s,
                `verdict` = '%s',
                `status` = 'ready'
            WHERE
                `run_id` = %s;
            ''') % (str(score), str(score * 100), verdict, str(run_id)),
            dbname='omegaup', auth=self.mysql_auth())

    def update_score_in_course(self, problem_alias, assignment_alias,
                               verdict='AC', score=1):
        '''Set verdict and score of latest run'''

        run_id = util.database_utils.mysql(
            ('''
            SELECT
                MAX(`r`.`run_id`)
            FROM
                `Runs` AS `r`
            INNER JOIN
                `Problems` AS `p` ON
                `p`.`problem_id` = `r`.`problem_id`
            INNER JOIN
                `Problemsets` AS `ps` ON
                `ps`.`problemset_id` = `r`.`problemset_id`
            INNER JOIN
                `Assignments` AS `a` ON `a`.`acl_id` = `ps`.`acl_id`
            WHERE
                `p`.`alias` = '%s'
                AND `a`.`alias` = '%s';
            ''') % (problem_alias, assignment_alias),
            dbname='omegaup', auth=self.mysql_auth())
        self.update_run_score(int(run_id.strip()), verdict, score)

    def update_score_in_contest(self, problem_alias, contest_alias,
                                verdict='AC', score=1):
        '''Set verdict and score of latest run'''

        run_id = util.database_utils.mysql(
            ('''
            SELECT
                MAX(`r`.`run_id`)
            FROM
                `Runs` AS `r`
            INNER JOIN
                `Problems` AS `p` ON
                `p`.`problem_id` = `r`.`problem_id`
            INNER JOIN
                `Problemsets` AS `ps` ON
                `ps`.`problemset_id` = `r`.`problemset_id`
            INNER JOIN
                `Contests` AS `c` ON `c`.`acl_id` = `ps`.`acl_id`
            WHERE
                `p`.`alias` = '%s'
                AND `c`.`alias` = '%s';
            ''') % (problem_alias, contest_alias),
            dbname='omegaup', auth=self.mysql_auth())
        self.update_run_score(int(run_id.strip()), verdict, score)

    def create_user(self, admin=False):
        '''Create a user, with optional admin privileges.'''

        if admin:
            username = 'admin_%s' % self.generate_id()
            # password = 'omegaup'
            password = (
                '$2a$08$tyE7x/yxOZ1ltM7YAuFZ8OK/56c9Fsr/XDqgPe22IkOORY2kAAg2a')
        else:
            username = 'user_%s' % self.generate_id()
            # password = 'user'
            password = (
                '$2a$08$wxJh5voFPGuP8fUEthTSvutdb1OaWOa8ZCFQOuU/ZxcsOuHGw0Cqy')

        # Add the user directly to the database to make this fast and avoid UI
        # flake.
        user_id = util.database_utils.mysql(
            ('''
            INSERT INTO
                Users(`username`, `password`, `verified`, `name`)
            VALUES
                ('%s', '%s', 1, '%s');
            SELECT LAST_INSERT_ID();
            ''') % (username, password, username),
            dbname='omegaup', auth=self.mysql_auth())
        identity_id = util.database_utils.mysql(
            ('''
            INSERT INTO
                Identities(`username`, `password`, `name`, `user_id`)
            VALUES
                ('%s', '%s', '%s', %s);
            SELECT LAST_INSERT_ID();
            ''') % (username, password, username, user_id),
            dbname='omegaup', auth=self.mysql_auth())
        util.database_utils.mysql(
            ('''
            UPDATE
                Users
            SET
                main_identity_id = %s
            WHERE
                user_id = %s;
            ''') % (identity_id, user_id),
            dbname='omegaup', auth=self.mysql_auth())
        if admin:
            util.database_utils.mysql(
                ('''
                INSERT INTO
                    User_Roles(`user_id`, `role_id`, `acl_id`)
                VALUES
                    (%s, 1, 1);
                ''') % (user_id,),
                dbname='omegaup', auth=self.mysql_auth())
        return username


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
    if util.CI:
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
    parser.addoption('--url', default=('http://localhost/' if not util.CI else
                                       'http://localhost:8000/'),
                     help='The URL that the test will be run against')
    parser.addoption('--disable-headless', action='store_false',
                     dest='headless', help='Show the browser window')
    parser.addoption('--mysql-config-file',
                     default=util.database_utils.default_config_file(),
                     help='.my.cnf file that stores credentials')
    parser.addoption('--username', default='root', help='MySQL root username')
    parser.addoption('--password', default='omegaup', help='MySQL password')


def pytest_generate_tests(metafunc):
    '''Parameterize the tests with the browsers.'''

    if not metafunc.config.option.browsers:
        metafunc.config.option.browsers = ['chrome', 'firefox']

    if 'driver' in metafunc.fixturenames:
        metafunc.parametrize('browser_name', metafunc.config.option.browsers,
                             scope='session')


def _get_browser(request, browser_name):
    '''Gets a browser object from the request parameters.'''

    if util.CI:
        capabilities = {
            'tunnel-identifier': os.environ['TRAVIS_JOB_NUMBER'],
            'name': 'Travis CI run %s[%s]' % (
                os.environ.get('TRAVIS_BUILD_NUMBER', ''), browser_name),
            'build': os.environ.get('TRAVIS_BUILD_NUMBER', ''),
            'tags': [os.environ.get('TRAVIS_PYTHON_VERSION', '3'), 'CI'],
            'extendedDebugging': 'true',
            'loggingPrefs': {'browser': 'ALL'},
        }
        # Add browser configuration
        capabilities.update({
            'browserName': browser_name,
            'version': 'latest',
            'platform': 'Windows 10',
            'screenResolution': '%dx%d' % _WINDOW_SIZE,
        })
        hub_url = 'http://%s:%s@localhost:4445/wd/hub' % (
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
        chrome_capabilities = DesiredCapabilities.CHROME
        chrome_capabilities['loggingPrefs'] = {'browser': 'ALL'}
        chrome_browser = webdriver.Chrome(
            chrome_options=chrome_options,
            desired_capabilities=chrome_capabilities)
        chrome_browser.set_window_size(*_WINDOW_SIZE)
        return chrome_browser
    firefox_capabilities = DesiredCapabilities.FIREFOX
    firefox_capabilities['marionette'] = True
    firefox_capabilities['loggingPrefs'] = {'browser': 'ALL'}
    firefox_options = webdriver.firefox.options.Options()
    firefox_profile = webdriver.FirefoxProfile()
    firefox_profile.set_preference(
        'webdriver.log.file', '/tmp/firefox_console')
    if request.config.option.headless:
        firefox_options.add_argument('-headless')
    firefox_browser = webdriver.Firefox(
        capabilities=firefox_capabilities,
        firefox_options=firefox_options,
        firefox_profile=firefox_profile)
    firefox_browser.set_window_size(*_WINDOW_SIZE)
    return firefox_browser


@pytest.yield_fixture(scope='session')
def driver(request, browser_name):
    '''Run tests using the selenium webdriver.'''

    try:
        browser = _get_browser(request, browser_name)
        if util.CI:
            print(('\n\nYou can see the report at '
                   'https://saucelabs.com/beta/tests/%s/commands') %
                  browser.session_id, file=sys.stderr)

        browser.implicitly_wait(_DEFAULT_TIMEOUT)
        if browser_name != 'firefox':
            # Ensure that getting browser logs is supported in non-Firefox
            # browsers.
            assert isinstance(browser.get_log('browser'), list)
        wait = WebDriverWait(browser, _DEFAULT_TIMEOUT,
                             poll_frequency=0.1)

        try:
            yield Driver(browser, wait, request.config.option.url,
                         os.environ.get('PYTEST_XDIST_WORKER', 'w0'),
                         request.config.option)
        finally:
            if util.CI:
                try:
                    browser.execute_script("sauce:job-result=%s" %
                                           str(_SUCCESS).lower())
                except WebDriverException:
                    # Test is done. Just ignore the error.
                    pass
            browser.quit()
    except:
        logging.exception('Failed to initialize')
        raise
