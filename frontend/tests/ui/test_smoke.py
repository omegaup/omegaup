#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium end-to-end tests.'''

import os

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

from ui import util  # pylint: disable=no-name-in-module


@util.no_javascript_errors()
@util.annotate
def test_create_user(driver):
    '''Tests basic functionality.'''

    username = 'unittest_user_%s' % driver.generate_id()
    password = 'p@ssw0rd'
    driver.register_user(username, password)

    with driver.login(username, password):
        pass


@util.no_javascript_errors()
@util.annotate
def test_login(driver):
    '''Tests login with a normal and an admin user.'''

    with driver.login_user():
        pass

    with driver.login_admin():
        pass


@util.no_javascript_errors()
@util.annotate
def test_create_problem(driver):
    '''Tests creating a public problem and retrieving it.'''

    problem_alias = 'ut_problem_%s' % driver.generate_id()
    create_problem(driver, problem_alias)

    with driver.login_user():
        prepare_run(driver, problem_alias)
        assert (problem_alias in driver.browser.find_element_by_xpath(
            '//h1[@class="title"]').get_attribute('innerText'))

        runs_before_submit = driver.browser.find_elements_by_xpath(
            '//td[@class="status"]')

        filename = 'Main.java'
        util.create_run(driver, problem_alias, filename)

        runs_after_submit = driver.browser.find_elements_by_xpath(
            '//td[@class="status"]')

        assert len(runs_before_submit) + 1 == len(runs_after_submit)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//button[contains(@class, "details")]'))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, '//form[@class="run-details-view"]')))

        textarea = driver.browser.find_element_by_xpath(
            '//form[@class="run-details-view"]//div[@class="CodeMirror-code"]')

        assert textarea.text is not None

        resource_path = os.path.join(util.OMEGAUP_ROOT,
                                     'frontend/tests/resources', filename)
        # The text of the CodeMirror editor contains the line number.
        # Non-exact match is needed.
        with open(resource_path, 'r') as f:
            for row in f.read().splitlines():
                if row is not None:
                    assert (row in textarea.text), row

        driver.browser.find_element_by_id('overlay').click()
        driver.update_score(problem_alias)

    with driver.login_user():
        prepare_run(driver, problem_alias)
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//div[contains(concat(" ", normalize-space(@class), " "), '
                 '" qualitynomination-popup ")]/form[contains(concat(" ", '
                 'normalize-space(@class), " "), " popup ")]')))


@util.annotate
@util.no_javascript_errors()
def prepare_run(driver, problem_alias):
    ''' Entering to a problem page to create a submission.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//li[contains(concat(" ", normalize-space(@class), " "'
                       '), " nav-problems ")]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[contains(concat(" ", normalize-space(@class), " "), " '
                  'nav-problems ")]//a[@href = "/problem/"]')))).click()

    search_box_element = driver.wait.until(
        EC.visibility_of_element_located(
            (By.ID, 'problem-search-box')))
    search_box_element.send_keys(problem_alias)
    with driver.page_transition():
        search_box_element.submit()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//a[text() = "%s"]' % problem_alias))).click()


@util.annotate
@util.no_javascript_errors()
def create_problem(driver, problem_alias):
    '''Create a problem.'''
    with driver.login_admin():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//li[contains(concat(" ", normalize-space(@class),'
                           ' " "), " nav-problems ")]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//li[contains(concat(" ", normalize-space(@class), " ")'
                      ', " nav-problems ")]//a[@href = "/problem/new/"]'
                      )))).click()

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
            util.OMEGAUP_ROOT, 'frontend/tests/resources/triangulos.zip'))
        with driver.page_transition(wait_for_ajax=False):
            contents_element.submit()
        assert (('/problem/%s/edit/' % problem_alias) in
                driver.browser.current_url), driver.browser.current_url
