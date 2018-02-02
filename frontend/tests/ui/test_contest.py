#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium contest tests.'''

from flaky import flaky
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC


@flaky
def test_create_contest(driver):
    '''Tests creating an contest and retrieving it.'''

    run_id = driver.generate_id()
    contest_alias = 'unittest_contest_%s' % run_id
    problem = 'sumas'

    with driver.login_admin():
        create_contest(driver, contest_alias)

        assert (('/contest/%s/edit/' % contest_alias) in
                driver.browser.current_url), driver.browser.current_url

        add_problem_to_contest(driver, problem)

        contest_url = '/arena/%s' % contest_alias
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[starts-with(@href, "%s")]' % contest_url))).click()
        driver.wait_for_page_loaded()

        assert (contest_alias in
                driver.browser.current_url), driver.browser.current_url

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'start-contest-submit'))).click()
        driver.wait_for_page_loaded()

        assert (('%s/#problems' % contest_url) in
                driver.browser.current_url), driver.browser.current_url


def create_contest(driver, contest_alias):
    '''Creates a new contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.ID, 'nav-contests'))).click()
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//li[@id = "nav-contests"]'
              '//a[@href = "/contest/new/"]')))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.ID, ('title')))).send_keys(contest_alias)
    driver.browser.find_element_by_id('alias').send_keys(
        contest_alias)
    driver.browser.find_element_by_id('description').send_keys(
        'contest description')

    with driver.ajax_page_transition():
        driver.browser.find_element_by_tag_name('form').submit()


def add_students(driver, users):
    '''Add students to a recently created contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "#contestants")]')))).click()

    for user in users:
        driver.typeahead_helper('#contestants', user)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CLASS_NAME, ('user-add-single')))).click()
        driver.wait_for_page_loaded()


def add_problem_to_contest(driver, problem):
    '''Add problems to a contest given.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#problems"]'))).click()

    driver.typeahead_helper('#problems', problem)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '#add-problem-form button[type="submit"]'))).click()
    driver.wait_for_page_loaded()


def enter_to_contest(driver, contest_alias):
    '''Enter to contest previously created.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "/arena/"]'))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//a[@href = "/arena/#list-current-contest")]'))).click()

    contest_url = '/arena/%s' % contest_alias
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//a[@href = "%s")]' % contest_url))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.ID, 'start-contest-submit'))).click()
    driver.wait_for_page_loaded()

    assert (contest_url in
            driver.browser.current_url), driver.browser.current_url
