#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium problem tests.'''

import os

from flaky import flaky
from selenium.webdriver.common.by import By
from selenium.common.exceptions import UnexpectedAlertPresentException
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.select import Select

import ui.util as util
import ui.test_contest as contest
import ui.test_course as course
import ui.test_smoke as smoke


@flaky
@util.no_javascript_errors(path_whitelist=('/api/problem/details/',),
                           message_whitelist=('/api/problem/details/',))
def test_single_problem(driver):
    '''Creates one submission of a single problem'''

    problem_alias = 'unittest_problem_%s' % driver.generate_id()

    smoke.create_problem(driver, problem_alias)

    with driver.login_user():
        smoke.enter_problem(driver, problem_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "new-run")]')))).click()

        Select(driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//select[@name = "language"]')))).select_by_visible_text(
                     'Java')

        contents_element = driver.browser.find_element_by_css_selector(
            '#submit input[type="file"]')
        contents_element.send_keys(os.path.join(
            util.OMEGAUP_ROOT, 'frontend/tests/resources/Main.2Kb.java'))

        try:
            with driver.ajax_page_transition():
                contents_element.submit()

        except UnexpectedAlertPresentException:
            print('Automatically accepting alert')
            alert = driver.browser.switch_to.alert
            alert.accept()

        contents_element.send_keys(os.path.join(
            util.OMEGAUP_ROOT, 'frontend/tests/resources/Main.java'))
        with driver.ajax_page_transition():
            contents_element.submit()

        new_run = driver.browser.find_element_by_xpath(
            '//td[@class="status"]/span')

        assert new_run.text == 'new', new_run


@flaky
@util.no_javascript_errors(path_whitelist=('/js/dist/omegaup.js',))
def test_contest_problem(driver):
    '''Tests creating one submission of a problem in a contest.'''

    run_id = driver.generate_id()
    contest_alias = 'unittest_contest_%s' % run_id
    problem = 'sumas'
    user = 'user'
    password = 'user'

    contest.create_contest_admin(driver, contest_alias, problem, [user])

    with driver.login(user, password):
        contest.create_run_user(driver, contest_alias, problem, 'Main.cpp11',
                                verdict='AC', score=1)

    contest.update_scoreboard_for_contest(driver, contest_alias)

    with driver.login_admin():

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'nav-contests'))).click()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-contests"]'
                  '//a[@href = "/contest/mine/"]')))).click()
        driver.wait_for_page_loaded()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "/arena/%s/scoreboard/")]' %
                  contest_alias)))).click()
        driver.wait_for_page_loaded()

        run_accepted_user = driver.browser.find_element_by_xpath(
            '//td[@class="accepted"]/preceding-sibling::td[1]')
        assert user in run_accepted_user.text, run_accepted_user


@flaky
@util.no_javascript_errors(path_whitelist=('/api/course/assignmentScoreboard/',
                                           '/js/dist/omegaup.js'))
def test_course_problem(driver):
    '''Tests creating one submission of a problem in a course.'''

    run_id = driver.generate_id()
    course_alias = 'ut_rank_course_%s' % run_id
    school_name = 'ut_rank_school_%s' % run_id
    assignment_alias = 'ut_rank_homework_%s' % run_id
    problem = 'sumas'
    user = 'user'

    with driver.login_admin():
        course.create_course(driver, course_alias, school_name)
        course.add_students_course(driver, [user])
        course.add_assignment(driver, assignment_alias)
        course.add_problem_to_assignment(driver, assignment_alias, problem)

    with driver.login(user, user):
        course.enter_course(driver, course_alias, assignment_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "#problems/%s")]' % problem)))).click()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "new-run")]')))).click()

        Select(driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//select[@name = "language"]')))).select_by_visible_text(
                     'C++11')

        contents_element = driver.browser.find_element_by_css_selector(
            '#submit input[type="file"]')
        contents_element.send_keys(os.path.join(
            util.OMEGAUP_ROOT, 'frontend/tests/resources/Main.cpp11'))
        with driver.ajax_page_transition():
            contents_element.submit()

        driver.update_score_in_course(problem, assignment_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 'button.details'))).click()

        assert (('show-run:') in
                driver.browser.current_url), driver.browser.current_url

    with driver.login_admin():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "/schools/"]'))).click()
        driver.wait_for_page_loaded()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, ('//a[@href = "/course/"]')))).click()
        driver.wait_for_page_loaded()

        course_url = '/course/%s' % course_alias
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[@href = "%s"]' % course_url))).click()
        driver.wait_for_page_loaded()

        progress_url = '/course/%s/students/' % course_alias
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[@href = "%s"]' % progress_url)))).click()
        driver.wait_for_page_loaded()

        assert driver.browser.find_element_by_css_selector(
            'td.score').text == '100'
