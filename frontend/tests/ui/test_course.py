#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium course tests.'''

import urllib

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.select import Select

from ui import util


# Assignment scoreboard is still not completely working.
@util.no_javascript_errors()
@util.annotate
def test_user_ranking_course(driver):
    '''Creates a course and students to participate make submits to problems'''

    run_id = driver.generate_id()
    course_alias = 'ut_rank_course_%s' % run_id
    school_name = 'ut_rank_school_%s' % run_id
    assignment_alias = 'ut_rank_hw_%s' % run_id
    problem = 'sumas'

    with driver.login_admin():
        create_course(driver, course_alias, school_name)
        add_students_course(driver, [driver.user_username])
        add_assignment(driver, assignment_alias)
        add_problem_to_assignment(driver, assignment_alias, problem)

    with driver.login_user():
        enter_course(driver, course_alias, assignment_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, "#problems/%s")]' %
                  problem)))).click()

        util.create_run(driver, problem, 'Main.cpp11')
        driver.update_score_in_course(problem, assignment_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 'button.details'))).click()

        assert (('show-run:') in
                driver.browser.current_url), driver.browser.current_url

    update_scoreboard_for_assignment(driver, assignment_alias, course_alias)

    with driver.login_admin():
        enter_course_scoreboard_page(driver, course_alias, assignment_alias)
        run_user = driver.browser.find_element_by_xpath(
            '//td[@class="accepted"]/preceding-sibling::td[1]')
        assert run_user.text == driver.user_username, run_user

        enter_course_scoreboard_page(driver, course_alias, assignment_alias)
        public = '//tr[@class="%s"]/td/a[text()="Public"]' % assignment_alias
        util.check_scoreboard_events(driver, public, number_ac_or_pa_runs=1)

        enter_course_scoreboard_page(driver, course_alias, assignment_alias)
        admin = '//tr[@class="%s"]/td/a[text()="Admin"]' % assignment_alias
        util.check_scoreboard_events(driver, admin, number_ac_or_pa_runs=1)


def enter_course_scoreboard_page(driver, course_alias, assignment_alias):
    '''Steps to enter into scoreboard page'''

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "/schools/"]'))).click()

    with driver.page_transition():
        course_url = '/course/%s' % course_alias
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[@href = "%s"]' % course_url))).click()

    with driver.page_transition():
        driver.wait.until(EC.element_to_be_clickable(
            (By.XPATH,
             '//a[contains(@href, "/assignment/%s/scoreboard/")]' %
             assignment_alias))).click()


@util.annotate
def create_course(driver, course_alias, school_name):
    '''Creates one course with a new school.'''

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "/schools/"]'))).click()

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, ('//a[@href = "/course/new/"]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CLASS_NAME, ('name')))).send_keys(course_alias)
    driver.browser.find_element_by_class_name('alias').send_keys(
        course_alias)
    driver.typeahead_helper('*[contains(@class, "omegaup-course-details")]',
                            school_name,
                            select_suggestion=False)
    driver.browser.find_element_by_tag_name('textarea').send_keys(
        'course description')

    with driver.page_transition():
        driver.browser.find_element_by_tag_name('form').submit()
    assert (('/course/%s/edit/' % course_alias) in
            driver.browser.current_url), driver.browser.current_url


@util.annotate
def add_assignment(driver, assignment_alias):
    '''Add assignments to a recently created course.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, (
                '//a[contains(@href, "#assignments")]')))).click()
    driver.wait.until(
        EC.visibility_of_element_located((By.CSS_SELECTOR, '#assignments')))

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, ('#assignments .new button')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '.omegaup-course-assignmentdetails')))
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, ('.schedule .name')))).send_keys(
                assignment_alias)
    assignments_tab = driver.browser.find_element_by_css_selector(
        '.tab-pane.active')
    new_assignment_form = assignments_tab.find_element_by_css_selector(
        '.schedule')
    new_assignment_form.find_element_by_css_selector('.alias').send_keys(
        assignment_alias)
    new_assignment_form.find_element_by_css_selector('textarea').send_keys(
        'homework description')

    new_assignment_form.find_element_by_css_selector(
        'button[type=submit]').click()
    driver.wait.until(
        EC.invisibility_of_element_located(
            (By.CSS_SELECTOR, '.omegaup-course-assignmentdetails')))
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//*[contains(@class, "omegaup-course-assignmentlist")]'
             '//a[text()="%s"]' % assignment_alias)))


@util.annotate
def add_problem_to_assignment(driver, assignment_alias, problem):
    '''Add problems to an assignment given.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#problems"]'))).click()
    Select(driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//select[@name = "assignments"]')))).select_by_visible_text(
                 assignment_alias)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '#problems .problemlist button'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR,
             '.omegaup-course-problemlist .panel-footer')))

    driver.typeahead_helper(
        '*[contains(@class, "panel-footer")]', problem)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '.omegaup-course-problemlist .panel-footer '
             'button[type=submit]'))).click()
    driver.wait.until(
        EC.invisibility_of_element_located(
            (By.CSS_SELECTOR,
             '.omegaup-course-problemlist .panel-footer')))


@util.annotate
def update_scoreboard_for_assignment(driver, assignment_alias, course_alias):
    '''Updates the scoreboard for an assignment.

    This can be run without a session being active.
    '''

    scoreboard_refresh_url = driver.url(
        '/api/scoreboard/refresh/alias/%s/course_alias/%s/token/secret' %
        (urllib.parse.quote(assignment_alias, safe=''),
         urllib.parse.quote(course_alias, safe='')))
    driver.browser.get(scoreboard_refresh_url)
    assert '{"status":"ok"}' in driver.browser.page_source


@util.annotate
def add_students_course(driver, users):
    '''Add students to a recently course.'''

    util.add_students(
        driver, users, tab_xpath='//a[contains(@href, "#students")]',
        container_xpath='//*[@id="students"]',
        parent_xpath='*[contains(@class, "omegaup-course-addstudent")]',
        submit_locator=(By.CSS_SELECTOR,
                        '.omegaup-course-addstudent form button[type=submit]'))


@util.annotate
def enter_course(driver, course_alias, assignment_alias):
    '''Enter to course previously created.'''

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "/schools/"]'))).click()

    course_url = '/course/%s' % course_alias
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[starts-with(@href, "%s")]' % course_url))).click()
    assert (course_url in
            driver.browser.current_url), driver.browser.current_url

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//input[@name = "accept-teacher"]'))).click()
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//button[@name = "start-course-submit"]'))).click()

    assignment_url = '/course/%s/assignment/%s' % (course_alias,
                                                   assignment_alias)
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[starts-with(@href, "%s")]' % assignment_url)))).click()
    assert (assignment_url in
            driver.browser.current_url), driver.browser.current_url
