#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium course tests.'''

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

    with driver.login_admin():
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH, '//a[@href = "/schools/"]'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH, ('//a[@href = "/course/"]')))).click()

        with driver.page_transition():
            course_url = '/course/%s' % course_alias
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     '//a[@href = "%s"]' % course_url))).click()

        with driver.page_transition():
            progress_url = '/course/%s/students/' % course_alias
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//a[@href = "%s"]' % progress_url)))).click()

        assert driver.browser.find_element_by_css_selector(
            'td.score').text == '100'


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
                (By.XPATH, ('//a[@href = "/course/"]')))).click()

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
def add_students_course(driver, users):
    '''Add students to a recently course.'''

    util.add_students(
        driver, users, container_id='students',
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

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, ('//a[@href = "/course/"]')))).click()

    course_url = '/course/%s' % course_alias
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[starts-with(@href, "%s")]' % course_url))).click()
    assert (course_url in
            driver.browser.current_url), driver.browser.current_url

    assignment_url = '/course/%s/assignment/%s' % (course_alias,
                                                   assignment_alias)
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[starts-with(@href, "%s")]' % assignment_url)))).click()
    assert (assignment_url in
            driver.browser.current_url), driver.browser.current_url
