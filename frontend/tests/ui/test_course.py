#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium course tests.'''

from flaky import flaky
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.select import Select


@flaky
def test_create_course(driver):
    '''Tests creating an course and retrieving it.'''

    run_id = driver.generate_id()
    course_alias = 'unittest_course_%s' % run_id
    school_name = 'unittest_school_%s' % run_id
    assignment_alias = 'unittest_homework_%s' % run_id
    user = 'user'
    problem = 'sumas'

    with driver.login_admin():
        create_course(driver, course_alias, school_name)

        assert (('/course/%s/edit/' % course_alias) in
                driver.browser.current_url), driver.browser.current_url

        add_students(driver, [user])

        add_assignment(driver, assignment_alias)

        add_problem_to_assignment(driver, assignment_alias, problem)

    with driver.login_user():
        enter_to_course(driver, course_alias, assignment_alias)


def test_user_ranking_course(driver):
    '''Creates a course and students to participate make submits to problems'''

    run_id = driver.generate_id()
    user1 = 'unittest_ranking_user_%s_1' % run_id
    user2 = 'unittest_ranking_user_%s_2' % run_id
    password = 'r@nk1ng_p@55'
    with driver.register_user(user1, password):
        pass
    with driver.register_user(user2, password):
        pass

    course_alias = 'ut_rank_course_%s' % run_id
    school_name = 'ut_rank_school_%s' % run_id
    assignment_alias = 'ut_rank_homework_%s' % run_id
    problem = 'sumas'
    with driver.login_admin():
        create_course(driver, course_alias, school_name)
        add_students(driver, user1)
        add_students(driver, user2)
        add_assignment(driver, assignment_alias)
        add_problem_to_assignment(driver, assignment_alias, problem)

    with driver.login(user1, password):
        enter_to_course(driver, course_alias, assignment_alias)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, problems/%s)]' % problem)))).click()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//a[contains(@href, new-run)]')))).click()

        language = 'Java'
        javascript_code = """
                            import java.util.Scanner;

                            public class Main {

                                public static void main(String[] args) {
                                    Scanner leer=new Scanner(System.in);
                                    int a,b;
                                    a=leer.nextInt();
                                    b=leer.nextInt();
                                    System.out.print((a+b));
                                }
                            }
                            """
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CLASS_NAME, ('CodeMirror-scroll')))).send_keys(
                    javascript_code)

        driver.browser.find_element_by_xpath(
            '//select[@name="language"]/option[contains(text(), %s)]' %
            language).click()
        with driver.ajax_page_transition():
            driver.browser.find_element_by_id('submit').submit()


def create_course(driver, course_alias, school_name):
    '''Creates one course with a new school.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "/schools/"]'))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[@href = "/course/"]')))).click()
    driver.wait_for_page_loaded()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[@href = "/course/new/"]')))).click()
    driver.wait_for_page_loaded()

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
        driver.wait_for_page_loaded()


def add_assignment(driver, assignment_alias):
    '''Add assignments to a recently created course.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, (
                '//a[contains(@href, "#assignments")]')))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
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
    driver.wait_for_page_loaded()


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
             '.tab-pane.active .problemlist button'))).click()
    driver.wait_for_page_loaded()

    driver.typeahead_helper('.omegaup-course-problemlist', problem)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '.omegaup-course-problemlist form button[type=submit]'))).click()
    driver.wait_for_page_loaded()


def enter_to_course(driver, course_alias, assignment_alias):
    '''Enter to course previously created.'''

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
             '//a[starts-with(@href, "%s")]' % course_url))).click()
    driver.wait_for_page_loaded()
    assert (course_url in
            driver.browser.current_url), driver.browser.current_url

    assignment_url = '/course/%s/assignment/%s' % (course_alias,
                                                   assignment_alias)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[starts-with(@href, "%s")]' % assignment_url)))).click()
    driver.wait_for_page_loaded()
    assert (assignment_url in
            driver.browser.current_url), driver.browser.current_url
