#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium contest tests.'''

import os
import sys

_OMEGAUP_ROOT = os.path.normpath(os.path.join(__file__, '../../../..'))
sys.path.append('%s/frontend/tests/ui/' % _OMEGAUP_ROOT)
# pylint: disable=wrong-import-position
import util  # NOQA
from flaky import flaky  # NOQA
from selenium.webdriver.common.by import By  # NOQA
from selenium.webdriver.support import expected_conditions as EC  # NOQA
from selenium.webdriver.support.select import Select  # NOQA


@flaky
def test_create_contest(driver):
    '''Tests creating an contest and retrieving it.'''

    run_id = driver.generate_id()
    contest_alias = 'unittest_contest_%s' % run_id
    problem = 'sumas'
    user = 'user'
    user1 = 'unittest_user_1_%s' % run_id
    user2 = 'unittest_user_2_%s' % run_id
    password = 'P@55w0rd'
    users = '%s, %s' % (user1, user2)

    driver.register_user(user1, password)
    driver.register_user(user2, password)

    with driver.login_admin():
        create_contest(driver, contest_alias)

        assert (('/contest/%s/edit/' % contest_alias) in
                driver.browser.current_url), driver.browser.current_url

        add_problem_to_contest(driver, problem)

        add_students_mass(driver, users)
        util.add_students(driver, [user], 'contest')

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

        assert ((contest_url) in
                driver.browser.current_url), driver.browser.current_url

    with driver.login(user1, password):
        enter_contest(driver, contest_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "#problems/%s")]' % problem)))).click()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "new-run")]')))).click()

        language = 'C++11'

        Select(driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//select[@name = "language"]')))).select_by_visible_text(
                     language)

        contents_element = driver.browser.find_element_by_css_selector(
            '#submit input[type="file"]')
        contents_element.send_keys(os.path.join(
            util.OMEGAUP_ROOT, 'frontend/tests/resources/Main.cpp11'))
        with driver.ajax_page_transition():
            contents_element.submit()

        driver.update_score_in_contest(problem, contest_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 'button.details'))).click()

        assert (('show-run:') in
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


def add_students_mass(driver, users):
    '''Add students to a recently created contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "#contestants")]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, (
                '//textarea[contains(@name, "usernames")]')))).send_keys(users)

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CLASS_NAME, ('user-add-bulk')))).click()

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


def enter_contest(driver, contest_alias):
    '''Enter contest previously created.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "/arena/"]'))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[contains(@href, "#list-past-contest")]'))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//a[contains(@href, "#list-current-contest")]'))).click()
    driver.wait_for_page_loaded()

    contest_url = '/arena/%s' % contest_alias
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '#current-contests a[href="%s"]' % contest_url))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.ID, 'start-contest-submit'))).click()
    driver.wait_for_page_loaded()

    assert (contest_url in
            driver.browser.current_url), driver.browser.current_url
