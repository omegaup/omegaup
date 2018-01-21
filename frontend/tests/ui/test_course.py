#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium course tests.'''

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC


def test_create_course(driver):
    '''Tests creating an course and retrieving it.'''

    course_alias = 'unittest_course_%s' % driver.id
    school_name = 'unittest_school_%s' % driver.id
    assignment_alias = 'unittest_homework_%s' % driver.id
    user = 'user'
    problem = 'sumas'

    with driver.login_admin():
        create_course(driver, course_alias, school_name)

        assert (('/course/%s/edit/' % course_alias) in
                driver.browser.current_url), driver.browser.current_url

        add_students(driver, [user])

        add_assignment(driver, assignment_alias)

        add_problem_to_assignment(driver, assignment_alias, problem)

    with driver.login('user', 'user'):
        enter_to_course(driver, course_alias, assignment_alias)


def create_course(driver, course_alias, school_name):
    '''Creates one course with a new school.'''

    driver.browser.find_element_by_xpath(
        '//a[contains(@href, "/schools/")]').click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, ('//a[contains(@href, "/course/")]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, ('//a[contains(@href, "/course/new/")]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CLASS_NAME, ('name')))).send_keys(course_alias)
    driver.browser.find_element_by_class_name('alias').send_keys(
        course_alias)
    driver.typeahead_helper('.omegaup-course-details', school_name,
                            select_suggestion=False)
    driver.browser.find_element_by_tag_name('textarea').send_keys(
        'course description')

    with driver.ajax_page_transition():
        driver.browser.find_element_by_tag_name('form').submit()


def add_students(driver, users):
    '''Add students to a recently created course.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "students")]')))).click()

    for user in users:
        driver.typeahead_helper('.omegaup-course-addstudent', user)

        driver.browser.find_element_by_css_selector(
            '.omegaup-course-addstudent form button[type=submit]').click()


def add_assignment(driver, assignment_alias):
    '''Add assignments to a recently created course.'''

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, (
                '//a[contains(@href, "assignments")]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, ('.tab-pane.active .new button')))).click()

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


def add_problem_to_assignment(driver, assignment_alias, problem):
    '''Add problems to an assignment given.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "#problems")]')))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//select[@name="assignments"]/option[contains(text(), %s)]'
              % assignment_alias)))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, (
                '.tab-pane.active .problemlist button')))).click()

    driver.typeahead_helper('.omegaup-course-problemlist', problem,
                            select_suggestion=False)

    driver.browser.find_element_by_css_selector(
        '.omegaup-course-problemlist form button[type=submit]').click()


def enter_to_course(driver, course_alias, assignment_alias):
    '''Enter to course previously created.'''

    driver.browser.find_element_by_xpath(
        '//a[contains(@href, "/schools/")]').click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[contains(@href, "/course/")]')))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[contains(@href, %s)]' % course_alias)))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[contains(@href, %s)]' % assignment_alias)))).click()
