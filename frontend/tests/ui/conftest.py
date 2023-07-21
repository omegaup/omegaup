#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# type: ignore

'''Fixtures for Selenium end-to-end tests.'''

import contextlib
import json
import logging
import os.path
import time
import urllib

from typing import Optional, Sequence

import pytest

from selenium import webdriver
from selenium.common.exceptions import WebDriverException, TimeoutException
from selenium.webdriver import Firefox
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.remote.webelement import WebElement
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.wait import WebDriverWait

from ui import util  # pylint: disable=no-name-in-module

_DEFAULT_TIMEOUT = 10  # seconds
_DIRNAME = os.path.dirname(__file__)
_SUCCESS = True
_WINDOW_SIZE = (1920, 1080)
_BLANK = '/404.html'  # An path that returns 200 in both Firefox and Chrome.


def _mysql_auth() -> Sequence[str]:
    '''Gets the authentication string for MySQL.'''

    return ['--defaults-file=/home/ubuntu/.my.cnf']


class JavaScriptLogCollector:
    '''Collects JavaScript errors from the log.'''

    def __init__(self, driver):  # pylint: disable=redefined-outer-name
        self.driver = driver
        self._log_index = 0
        self._log_stack = [[]]

    def empty(self) -> bool:
        '''Returns whether the stack is empty.'''
        # There is one catch-all frame at the bottom of the stack when nobody
        # has called push().
        return len(self._log_stack) <= 1

    def extend(self, errors):
        '''Injects errors into the current log frame.'''
        self._log_stack[-1].extend(errors)

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
            if 'WebSocket' in entry['message']:
                # Travis does not have broadcaster yet.
                continue
            if 'https://www.facebook.com/' in entry['message']:
                # Let's not block submissions when Facebook is
                # having a bad day.
                continue

            yield entry


class Driver:  # pylint: disable=too-many-instance-attributes
    # pylint: disable=too-many-public-methods
    '''Wraps the state needed to run a test.'''

    # pylint: disable=too-many-arguments
    def __init__(self, browser, browser_name, wait, url, worker_id, options):
        self.browser = browser
        self.browser_name = browser_name
        self.wait = wait
        self._worker_id = worker_id
        self._next_id = 0
        self._screenshot_index = 0
        self._url = url
        self.options = options
        self.user_username = self.create_user()
        self.admin_username = self.create_user(admin=True)
        self.log_collector = JavaScriptLogCollector(self)
        self.test_name = ''

    def generate_id(self):
        '''Generates a relatively unique id.'''

        self._next_id += 1
        return '%s_%d_%d' % (self._worker_id, int(time.time()), self._next_id)

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
    def page_transition(self, wait_for_ajax=True, target_url=None):
        '''Waits for a page transition to finish.'''

        html_node = self.browser.find_element(By.TAG_NAME, 'html')
        prev_url = self.browser.current_url
        logging.debug('Waiting for a page transition on %s', prev_url)
        yield
        self.wait.until(EC.staleness_of(html_node))
        logging.debug('New URL: %s', self.browser.current_url)
        if target_url:
            self.wait.until(EC.url_to_be(target_url))
            logging.debug('Target URL: %s', self.browser.current_url)
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

    def typeahead_helper(self, parent_selector, value):
        '''Helper to interact with Typeahead elements.'''

        tt_input = self.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR,
                 '%s .tags-input input[type="text"]' % parent_selector)))
        tt_input.click()
        tt_input.send_keys(value)
        self.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 '%s ul.typeahead-dropdown li:first-of-type' %
                 (parent_selector)))).click()

    def send_keys(self,
                  element: WebElement,
                  value: str,
                  retries: int = 10) -> None:
        '''Helper to _really_ send keys to an element.

        For some yet unexplained reason when running in non-headless mode, the
        interactions with text elements do not always register by the browser.
        This causes input elements to remain empty even after sending the keys.

        This method sends the keys and then ensures that the value of the
        element is the expected string, retrying if necessary.
        '''

        for _ in range(retries):
            element.clear()
            element.send_keys(value)
            if element.get_attribute('value') == value:
                return
        logging.error('Failed to send keys to the element')

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
    def login(self, username, password, is_main_user_identity=True):
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

        self.browser.find_element(By.NAME,
                                  'login_username').send_keys(username)
        self.browser.find_element(By.NAME,
                                  'login_password').send_keys(password)
        with self.page_transition():
            self.browser.find_element(By.NAME, 'login').click()

        if is_main_user_identity:
            self.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'button[aria-label="Close"]'))).click()
        try:
            yield
        except:  # noqa: bare-except
            self.screenshot()
        finally:
            # Wait until there are no more pending requests to avoid races
            # where those requests return 401. Navigate to a blank page just
            # for good measure and to enforce that there are two URL changes.
            self._wait_for_page_loaded()
            with self.page_transition():
                self.browser.get(self.url(_BLANK))
            with self.page_transition(target_url=home_page_url):
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
        self.browser.find_element(By.NAME, 'reg_username').send_keys(user)
        self.browser.find_element(By.NAME, 'reg_email').send_keys(
            'email_%s@localhost.localdomain' % user)
        self.browser.find_element(By.NAME, 'reg_password').send_keys(passw)
        self.browser.find_element(By.NAME,
                                  'reg_password_confirmation').send_keys(passw)
        with self.page_transition():
            self.browser.find_element(By.NAME, 'sign_up').click()

        # Enable experiment
        user_id = util.database_utils.mysql(
            ('''
            SELECT
                `u`.`user_id`
            FROM
                `Users` `u`
            INNER JOIN
                `Identities` `i`
            ON
                `u`.`main_identity_id` = `i`.`identity_id`
            WHERE
                `i`.`username` = '%s';
            ''') % (user),
            dbname='omegaup', auth=_mysql_auth())
        self.enable_experiment_identities_to_user(user_id)

        # Home screen
        with self.page_transition():
            self.browser.get(self.url(_BLANK))
        with self.page_transition(target_url=home_page_url):
            self.browser.get(self.url('/logout/?redirect=/'))
        assert self.browser.current_url == home_page_url, (
            'Invalid URL redirect. Expected %s, got %s' % (
                home_page_url, self.browser.current_url))

    def annotate(self,
                 message: str,
                 level=logging.INFO) -> None:
        '''Add an annotation to the run's log.'''

        logging.log(level, message)

    def update_run_score(self,
                         run_id,
                         verdict,
                         score) -> None:
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
            dbname='omegaup', auth=_mysql_auth())

    def update_score_in_course(self, problem_alias, assignment_alias,
                               verdict='AC', score=1):
        '''Set verdict and score of latest run in a course'''

        run_id = util.database_utils.mysql(
            ('''
            SELECT
                MAX(`r`.`run_id`)
            FROM
                `Submissions` AS `s`
            INNER JOIN
                `Runs` AS `r` ON
                `r`.`run_id` = `s`.`current_run_id`
            INNER JOIN
                `Problems` AS `p` ON
                `p`.`problem_id` = `s`.`problem_id`
            INNER JOIN
                `Problemsets` AS `ps` ON
                `ps`.`problemset_id` = `s`.`problemset_id`
            INNER JOIN
                `Assignments` AS `a` ON `a`.`acl_id` = `ps`.`acl_id`
            WHERE
                `p`.`alias` = '%s'
                AND `a`.`alias` = '%s';
            ''') % (problem_alias, assignment_alias),
            dbname='omegaup', auth=_mysql_auth())
        self.update_run_score(int(run_id.strip()), verdict, score)

    def update_score_in_contest(self, problem_alias, contest_alias,
                                verdict='AC', score=1):
        '''Set verdict and score of latest run in a contest'''

        run_id = util.database_utils.mysql(
            ('''
            SELECT
                MAX(`r`.`run_id`)
            FROM
                `Submissions` AS `s`
            INNER JOIN
                `Runs` AS `r` ON
                `r`.`run_id` = `s`.`current_run_id`
            INNER JOIN
                `Problems` AS `p` ON
                `p`.`problem_id` = `s`.`problem_id`
            INNER JOIN
                `Problemsets` AS `ps` ON
                `ps`.`problemset_id` = `s`.`problemset_id`
            INNER JOIN
                `Contests` AS `c` ON `c`.`acl_id` = `ps`.`acl_id`
            WHERE
                `p`.`alias` = '%s'
                AND `c`.`alias` = '%s';
            ''') % (problem_alias, contest_alias),
            dbname='omegaup', auth=_mysql_auth())
        self.update_run_score(int(run_id.strip()), verdict, score)

    def update_score(self, problem_alias, verdict='AC', score=1):
        '''Set verdict and score of latest run doesn't belong to problemset.'''

        run_id = util.database_utils.mysql(
            ('''
            SELECT
                MAX(`r`.`run_id`)
            FROM
                `Submissions` AS `s`
            INNER JOIN
                `Runs` AS `r` ON
                `r`.`run_id` = `s`.`current_run_id`
            INNER JOIN
                `Problems` AS `p` ON
                `p`.`problem_id` = `s`.`problem_id`
            WHERE
                `p`.`alias` = '%s';
            ''') % (problem_alias),
            dbname='omegaup', auth=_mysql_auth())
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
        identity_id = util.database_utils.mysql(
            ('''
            INSERT INTO
                Identities(`username`, `password`, `name`)
            VALUES
                ('%s', '%s', '%s');
            SELECT LAST_INSERT_ID();
            ''') % (username, password, username),
            dbname='omegaup', auth=_mysql_auth())
        user_id = util.database_utils.mysql(
            ('''
            INSERT INTO
                Users(`main_identity_id`, `verified`)
            VALUES
                (%s, 1);
            SELECT LAST_INSERT_ID();
            ''') % (identity_id),
            dbname='omegaup', auth=_mysql_auth())
        util.database_utils.mysql(
            ('''
            UPDATE
                Identities
            SET
                user_id = %s
            WHERE
                identity_id = %s;
            ''') % (user_id, identity_id),
            dbname='omegaup', auth=_mysql_auth())

        # Enable experiment
        self.enable_experiment_identities_to_user(user_id)

        if admin:
            util.database_utils.mysql(
                ('''
                INSERT INTO
                    User_Roles(`user_id`, `role_id`, `acl_id`)
                VALUES
                    (%s, 1, 1);
                ''') % (user_id,),
                dbname='omegaup', auth=_mysql_auth())
        return username

    def enable_experiment_identities_to_user(
            self,
            user_id,
    ) -> None:
        ''' Enable identities experiment to users can use functions of
        identity refactor
        '''
        util.database_utils.mysql(
            ('''
            INSERT INTO
                Users_Experiments(`user_id`, `experiment`)
            VALUES
                ('%s', 'identities');
            ''') % (user_id),
            dbname='omegaup', auth=_mysql_auth())

    def screenshot(self, name: Optional[str] = None) -> None:
        '''Takes a screenshot.'''
        results_dir = os.path.join(_DIRNAME, 'results')
        os.makedirs(results_dir, exist_ok=True)
        idx = self._screenshot_index
        self._screenshot_index += 1
        self.browser.get_screenshot_as_file(
            os.path.join(results_dir,
                         f'webdriver_{name or self.test_name}.{idx:03}.png'))


@pytest.hookimpl(hookwrapper=True)
def pytest_pyfunc_call(pyfuncitem):
    '''Takes a screenshot and grabs console logs on test failures.'''

    global _SUCCESS  # pylint: disable=global-statement

    current_driver: Optional[Driver] = pyfuncitem.funcargs.get('driver')
    if current_driver:
        current_driver.test_name = pyfuncitem.name

    outcome = yield

    if not outcome.excinfo:
        return
    _SUCCESS = False
    if not current_driver:
        return
    try:
        try:
            logs = current_driver.browser.get_log('browser')
        except:  # noqa: bare-except
            # geckodriver does not support getting logs:
            # https://github.com/mozilla/geckodriver/issues/284
            logs = []
        results_dir = os.path.join(_DIRNAME, 'results')
        os.makedirs(results_dir, exist_ok=True)
        current_driver.screenshot(pyfuncitem.name)
        logpath = os.path.join(results_dir,
                               'webdriver_%s.log' % pyfuncitem.name)
        with open(logpath, 'w', encoding='utf-8') as logfile:
            json.dump(logs, logfile, indent=2)
    except Exception as ex:  # pylint: disable=broad-except
        print(ex)


def pytest_addoption(parser):
    '''Allow configuration of test invocation.'''

    parser.addoption('--browser', action='append', type=str, dest='browsers',
                     help='The browsers that the test will run against')
    parser.addoption('--url', default='http://localhost:8001/',
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

    if browser_name == 'chrome':
        chrome_options = webdriver.ChromeOptions()
        chrome_options.add_experimental_option(
            'prefs', {'intl.accept_languages': 'en_US'})
        chrome_options.add_argument('--lang=en-US')
        if request.config.option.headless:
            chrome_options.add_argument('--headless')
        chrome_browser = webdriver.Chrome(
            options=chrome_options)
        chrome_browser.set_window_size(*_WINDOW_SIZE)
        return chrome_browser
    firefox_options = Options()
    firefox_options.set_capability('marionette', True)
    firefox_options.set_capability('loggingPrefs', {'browser': 'ALL'})
    firefox_options.set_preference(
        'webdriver.log.file', '/tmp/firefox_console')
    firefox_options.headless = request.config.option.headless
    firefox_browser = Firefox(options=firefox_options)
    firefox_browser.set_window_size(*_WINDOW_SIZE)
    return firefox_browser


@pytest.fixture(scope='session')
def driver(request, browser_name):
    '''Run tests using the selenium webdriver.'''

    try:
        browser = _get_browser(request, browser_name)

        browser.implicitly_wait(_DEFAULT_TIMEOUT)
        if browser_name != 'firefox':
            # Ensure that getting browser logs is supported in non-Firefox
            # browsers.
            assert isinstance(browser.get_log('browser'), list)
        wait = WebDriverWait(browser, _DEFAULT_TIMEOUT,
                             poll_frequency=0.1)

        try:
            yield Driver(browser, browser_name, wait,
                         request.config.option.url,
                         os.environ.get('PYTEST_XDIST_WORKER', 'w0'),
                         request.config.option)
        finally:
            browser.quit()
    except:  # noqa: bare-except
        logging.exception('Failed to initialize')
        raise
