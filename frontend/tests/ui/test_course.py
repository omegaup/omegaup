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

    with driver.login_admin():
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
        driver.browser.find_element_by_class_name('tt-hint').send_keys(
            school_name)
        driver.browser.find_element_by_class_name('tt-input').send_keys(
            school_name)
        driver.browser.find_element_by_tag_name('textarea').send_keys(
            'course description')

        with driver.ajax_page_transition():
            driver.browser.find_element_by_tag_name('form').submit()

        assert (('/course/%s/edit/' % course_alias) in
                driver.browser.current_url), driver.browser.current_url

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, ('//a[contains(@href, "students")]')))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, (
                    '.omegaup-course-addstudent .tt-hint')))).send_keys('user')
        driver.browser.find_element_by_css_selector(
            '.omegaup-course-addstudent .tt-input').send_keys('user')

        driver.browser.find_element_by_css_selector(
            '.omegaup-course-addstudent form button[type=submit]').click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, (
                    '//a[contains(@href, "assignments")]')))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, ('#assignments .new button')))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, ('.schedule .name')))).send_keys(
                    assignment_alias)
        driver.browser.find_element_by_css_selector(
            '.schedule .alias').send_keys(assignment_alias)
        driver.browser.find_element_by_css_selector(
            '.schedule textarea').send_keys('homework description')

        driver.browser.find_element_by_css_selector(
            '#assignments .schedule button[type=submit]').click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, ('//a[contains(@href, "problems")]')))).click()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//select[@name="assignments"]/option[contains(text(), %s)]'
                  % assignment_alias)))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, ('#problems .problemlist button')))).click()

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, (
                    '.problems-dropdown.tt-hint')))).send_keys('sumas')
        driver.browser.find_element_by_css_selector(
            '.problems-dropdown.tt-input').send_keys('sumas')

        driver.browser.find_element_by_css_selector(
            '.omegaup-course-problemlist form button[type=submit]').click()

        driver.browser.find_element_by_css_selector(
            '#problems .problemlist button').click()

    with driver.login_user():
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
