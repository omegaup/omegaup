#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium identities tests like create, update and associate with a user.

Also, added group create test
'''

from ui import util  # pylint: disable=no-name-in-module

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC


@util.annotate
def test_create_group_with_identities_and_restrictions(driver):
    '''Tests creation of a group with identities.'''

    group_title = 'unittest_group_%s' % driver.generate_id()
    description = 'some text for group description'

    with driver.login_admin():
        group_alias = util.create_group(driver, group_title, description)
        identity, *_ = util.add_identities_group(driver, group_alias)

    with driver.login(identity.username, identity.password):
        # Trying to create a contest
        try:
            with util.assert_js_errors(driver,
                                       message_list=('/api/contest/create/',)):
                util.create_contest(driver, 'some_alias', has_privileges=False)
        except Exception as ex:  # pylint: disable=broad-except
            print(ex)
        finally:
            pass

        # Trying to create a course
        course = 'curse_alias'
        school = 'school_alias'
        try:
            with util.assert_js_errors(driver,
                                       message_list=('/api/course/create/',)):
                util.create_course(driver, course, school,
                                   has_privileges=False)
        except Exception as ex:  # pylint: disable=broad-except
            print(ex)
        finally:
            pass

        # Trying to create a problem
        util.create_problem(driver, 'some_alias', has_privileges=False)

        # Trying to see the list of contests created by the identity
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'nav-contests'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//li[@id = "nav-contests"]'
                      '//a[@href = "/contest/mine/"]')))).click()

        assert_page_not_found_is_shown(driver)

    with driver.login(identity.username, identity.password):
        # Trying to see the list of problems created by the identity
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'nav-problems'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//li[@id = "nav-problems"]'
                      '//a[@href = "/problem/mine/"]')))).click()

        assert_page_not_found_is_shown(driver)


def assert_page_not_found_is_shown(driver):
    ''' Asserts user or identity does not have access to the page.'''

    error_page = driver.wait.until(
        EC.visibility_of_element_located((By.XPATH, '//h1/strong')))
    error_symbol = error_page.get_attribute('title')

    assert 'omega' in error_symbol, error_symbol

    error_page = driver.wait.until(
        EC.visibility_of_element_located((By.XPATH, '//h1/span')))
    error_down = error_page.get_attribute('title')

    assert 'Down' in error_down, error_down
