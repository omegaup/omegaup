#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# type: ignore

'''Run Selenium course tests.'''

import logging
import urllib

from typing import Optional

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

from ui import util  # pylint: disable=no-name-in-module
from ui import conftest  # pylint: disable=no-name-in-module


def _setup_course(driver: conftest.Driver, course_alias: str, school_name: str,
                  assignment_alias: str, problem_alias: str) -> None:
    with driver.login_admin():
        util.create_problem(
            driver,
            problem_alias,
            resource_path='frontend/tests/resources/testproblem.zip',
            private=True)
        create_course(driver, course_alias, school_name)
        add_students_course(driver, [driver.user_username])
        add_assignment_with_problem(driver, assignment_alias, problem_alias)


def _click_on_problem(driver: conftest.Driver, problem_alias: str) -> None:
    for _ in range(10):
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 (f'//div[@data-navbar-problem]'
                  f'//a[contains(text(), "{problem_alias}")]/parent::div'
                  )))).click()
        if driver.browser.current_url.endswith(f'#problems/{problem_alias}'):
            break
    else:
        logging.error('Failed to find the problem to click')


# Assignment scoreboard is still not completely working.
@util.no_javascript_errors()
@util.annotate
def test_user_ranking_course(driver):
    '''Creates a course and students to participate make submits to problems'''

    run_id = driver.generate_id()
    course_alias = f'ut_rank_course_{run_id}'
    school_name = 'Escuela curso'
    assignment_alias = f'ut_rank_hw_{run_id}'
    problem = 'ut_rc_problem_%s' % driver.generate_id()

    _setup_course(driver, course_alias, school_name, assignment_alias, problem)

    with driver.login_user():
        enter_course(driver, course_alias, assignment_alias)
        _click_on_problem(driver, problem)

        util.create_run(driver, problem, 'Main.cpp17-gcc')
        driver.update_score_in_course(problem, assignment_alias)

        # Refresh the current page.
        with driver.page_transition():
            driver.browser.get(driver.browser.current_url.split('#')[0])

        _click_on_problem(driver, problem)

        # When user has tried or solved a problem, feedback popup will be shown
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, '[data-overlay-popup] button.close')
            )).click()
        driver.wait.until(
            EC.invisibility_of_element_located(
                (By.CSS_SELECTOR, '[data-overlay-popup] button.close')
            ))

        _click_on_problem(driver, problem)
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 'button[data-run-details]'))).click()

        assert (('show-run:') in
                driver.browser.current_url), driver.browser.current_url

    update_scoreboard_for_assignment(driver, assignment_alias, course_alias)

    with driver.login_admin():
        enter_course_assignments_page(driver, course_alias)
        with driver.page_transition():
            driver.wait.until(EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[contains(@href, "/assignment/%s/scoreboard/")]' %
                 assignment_alias))).click()

        toggle_contestants_element = driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'input.toggle-contestants')))
        for _ in range(10):
            toggle_contestants_element.click()
            if not toggle_contestants_element.is_selected():
                break
        else:
            logging.error('Failed to toggle contestants')

        run_user = driver.browser.find_element(
            By.XPATH,
            '//td[contains(@class, "accepted")]/preceding-sibling::td[@class='
            '"user"]')
        assert run_user.text == driver.user_username, run_user

        url = '/course/{}/assignment/{}/scoreboard'.format(
            course_alias, assignment_alias)

        enter_course_assignments_page(driver, course_alias)
        util.check_scoreboard_events(driver, assignment_alias, url,
                                     num_elements=1, scoreboard='Scoreboard')

        enter_course_assignments_page(driver, course_alias)
        with driver.page_transition():
            driver.wait.until(EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[contains(@href, "/course/%s/edit/")]' %
                 course_alias))).click()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//input[@name = "show-scoreboard"][@value="true"]'))).click()

        driver.browser.find_element(By.CSS_SELECTOR,
                                    'form[data-course-form]').submit()
        assert (('/course/%s/edit/' % course_alias) in
                driver.browser.current_url), driver.browser.current_url

    with driver.login_user():
        enter_course(driver, course_alias, assignment_alias, first_time=False)

        for _ in range(10):
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH, ('//a[contains(@href, "#ranking")]')))).click()
            if driver.browser.current_url.endswith('#ranking'):
                break
        assert (('#ranking') in
                driver.browser.current_url), driver.browser.current_url

    with driver.login_admin():
        show_run_details_course(driver, course_alias, assignment_alias)


def show_run_details_course(driver: conftest.Driver, course_alias: str,
                            assignment_alias: str) -> None:
    '''It shows details popup for a certain submission in a course.'''
    enter_course_assignments_page(driver, course_alias)
    with driver.page_transition():
        driver.wait.until(EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "/course/%s/assignment/%s/"]'
             % (course_alias, assignment_alias)))).click()

    util.show_run_details(driver, code='#include <iostream>')

    driver.browser.find_element(By.CSS_SELECTOR, 'div[data-overlay]').click()


def test_create_identities_for_course(driver):
    '''Adding some identities into a course and associating one of them to
    specific user
    '''

    run_id = driver.generate_id()
    course_alias = 'ut_rank_course_%s' % run_id
    school_name = 'ut_rank_school'
    assignment_alias = 'ut_rank_hw_%s' % run_id
    problem = 'Sumas'
    username = 'ut_user_%s' % driver.generate_id()
    password = 'p@ssw0rd'
    driver.register_user(username, password)

    # Admin creates a course with one assignment and one problem, and then
    # creates some identities associated with the course group
    associated: Optional[util.Identity] = None
    unassociated: Optional[util.Identity] = None
    with driver.login_admin():
        create_course(driver, course_alias, school_name)
        add_assignment_with_problem(driver, assignment_alias, problem)
        # The function require the group alias. We are assuming that it is the
        # same as the course alias, since that is the default
        unassociated, associated = util.add_identities_group(driver,
                                                             course_alias)[:2]

    # Unassociated identity joins the course which it was created for and
    # creates a new run
    with driver.login(unassociated.username,
                      unassociated.password,
                      is_main_user_identity=False):
        enter_course(driver, course_alias, assignment_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(text(), "%s")]/parent::div' %
                  problem.title())))).click()

        util.create_run(driver, problem, 'Main.cpp17-gcc')
        driver.update_score_in_course(problem, assignment_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 'button[data-run-details]'))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR,
                 '.show form[data-run-details-view] .CodeMirror-code')))

        assert (('show-run:') in
                driver.browser.current_url), driver.browser.current_url

    # Registred user associates a new identity
    with driver.login(username, password):
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-user]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-profile]'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//a[@href = "/profile/#edit-basic-information"]')
                     ))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//input[contains(concat(" ", normalize-space(@class), " "), '
                 '" username-input ")]'))).send_keys(associated.username)
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//input[contains(concat(" ", normalize-space(@class), " "), '
                 '" password-input ")]'
                 ))).send_keys(associated.password)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//form[contains(concat(" ", normalize-space(@class), " "), '
                 '" add-identity-form ")]/div/button'))).click()

        associated_identities = driver.browser.find_element(
            By.XPATH,
            '//tr/td[text() = "%s"]' % (associated.username))
        assert associated_identities is not None, 'No identity matches'

    # The new associated identity joins the course
    with driver.login(associated.username,
                      associated.password,
                      is_main_user_identity=False):
        enter_course(driver, course_alias, assignment_alias)


def enter_course_assignments_page(driver, course_alias):
    '''Steps to enter into scoreboard page'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'li[data-nav-right]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-courses-mine]'))).click()

    course_url = f'/course/{course_alias}/'
    with driver.page_transition(target_url=driver.url(course_url)):
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, f'a[href="{course_url}"]'))).click()


@util.annotate
def create_course(driver, course_alias: str, school_name: str) -> None:
    '''Creates one course with a new school.'''
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'a[data-nav-courses]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-courses-create]'))).click()
    driver.send_keys(
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'input[data-course-new-name]'))),
        course_alias)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'input[data-course-new-alias]'))).send_keys(course_alias)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'input[name="show-scoreboard"][value="true"]'))).click()
    driver.typeahead_helper('.omegaup-course-details', school_name)
    driver.browser.find_element(
        By.CSS_SELECTOR, 'textarea[data-course-new-description]').send_keys(
            'course description')

    with driver.page_transition():
        driver.browser.find_element(By.CSS_SELECTOR,
                                    'form[data-course-form]').submit()
    assert (f'/course/{course_alias}/edit/' in driver.browser.current_url
            ), driver.browser.current_url


@util.annotate
def add_assignment_with_problem(driver, assignment_alias, problem_alias):
    '''Add assignments to a recently created course.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, (
                '//a[contains(@href, "#content")]')))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'div[data-content-tab]')))

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'div[data-content-tab] .new button'))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '.omegaup-course-assignmentdetails')))
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, ('.schedule .name')))).send_keys(
                assignment_alias)
    assignments_tab = driver.browser.find_element(By.CSS_SELECTOR,
                                                  '.tab-pane.active')
    new_assignment_form = assignments_tab.find_element(By.CSS_SELECTOR,
                                                       '.schedule')
    new_assignment_form.find_element(By.CSS_SELECTOR,
                                     '.alias').send_keys(assignment_alias)
    new_assignment_form.find_element(
        By.CSS_SELECTOR, 'textarea').send_keys('homework description')

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR,
             '[data-course-problemlist] .card-footer')))

    driver.typeahead_helper('div[data-course-add-problem]', problem_alias)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'button[data-add-problem]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR,
             '[data-course-problemlist] table.table-striped')))

    with util.dismiss_status(driver):
        new_assignment_form.find_element(
            By.CSS_SELECTOR, 'button[data-schedule-assignment]').click()
    driver.wait.until(
        EC.invisibility_of_element_located(
            (By.CSS_SELECTOR, '.omegaup-course-assignmentdetails')))
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//*[contains(@class, "omegaup-course-assignmentlist")]'
             '//a[text()="%s"]' % assignment_alias)))


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
    assert '"status":"ok"' in driver.browser.page_source


@util.annotate
def add_students_course(driver, users):
    '''Add students to a recently course.'''

    util.add_students(
        driver, users, tab_xpath='//a[contains(@href, "#students")]',
        container_xpath='//div[@data-students-tab]',
        parent_selector='.omegaup-course-addstudent',
        add_button_locator=(
            By.CSS_SELECTOR,
            '.omegaup-course-addstudent form button.add-participant'
        ),
        submit_locator=(
            By.CSS_SELECTOR,
            '.omegaup-course-addstudent form button[type=submit]'
        )
    )


@util.annotate
def enter_course(driver, course_alias, assignment_alias, *, first_time=True):
    '''Enter to course previously created.'''
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'a[data-nav-courses]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-courses-all]'))).click()
    course_url = '/course/%s' % course_alias
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//a[starts-with(@href, "%s")]' % course_url))).click()
    assert (course_url in
            driver.browser.current_url), driver.browser.current_url

    if first_time:
        with driver.page_transition(target_url=driver.browser.current_url):
            accept_teacher_element = driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR,
                     'input[name="accept-teacher"][value="true"]')))
            for _ in range(10):
                accept_teacher_element.click()
                if accept_teacher_element.is_selected():
                    break
            else:
                logging.error('Failed to accept teacher')

            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR,
                     'button[name="start-course-submit"]'))).click()

    assignment_url = f'/course/{course_alias}/assignment/{assignment_alias}/'
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[starts-with(@href, "%s")]' % assignment_url)))).click()
    assert (assignment_url in
            driver.browser.current_url), driver.browser.current_url

    driver.wait.until(EC.url_contains('#problems'))

    # Verify the socket status logo
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'sup.socket-status-ok')))
