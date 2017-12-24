#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium end-to-end tests.'''

import os

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

_OMEGAUP_ROOT = os.path.normpath(os.path.join(__file__, '../../../..'))


def test_create_user(driver):
    '''Tests basic functionality.'''

    # Home page
    home_page_url = driver.url('/')
    driver.browser.get(home_page_url)
    driver.browser.find_element_by_xpath(
        '//a[contains(@href, "/login/")]').click()

    # Login screen
    driver.wait.until(lambda _: driver.browser.current_url != home_page_url)
    username = 'unittest_user_%s' % driver.id
    password = 'p@ssw0rd'
    driver.browser.find_element_by_id('reg_username').send_keys(username)
    driver.browser.find_element_by_id('reg_email').send_keys(
        'email_%s@localhost.localdomain' % username)
    driver.browser.find_element_by_id('reg_pass').send_keys(password)
    driver.browser.find_element_by_id('reg_pass2').send_keys(password)
    with driver.ajax_page_transition():
        driver.browser.find_element_by_id('register-form').submit()

    # Home screen
    driver.browser.get(driver.url('/logout/?redirect=/'))
    driver.wait.until(lambda _: driver.browser.current_url == home_page_url)

    with driver.login(username, password):
        pass


def test_login(driver):
    '''Tests login with a normal and an admin user.'''

    with driver.login_user():
        pass

    with driver.login_admin():
        pass


def test_create_problem(driver):
    '''Tests creating a public problem and retrieving it.'''

    problem_alias = 'unittest_problem_%s' % driver.id

    with driver.login_admin():
        driver.browser.find_element_by_id('nav-problems').click()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-problems"]'
                  '//a[@href = "/problem/new/"]')))).click()

        driver.browser.find_element_by_name('title').send_keys(problem_alias)
        # Alias should be set automatically
        driver.browser.find_element_by_name('source').send_keys('test')
        # Make the problem public
        driver.browser.find_element_by_xpath(
            '//input[@name="visibility" and @value = "1"]').click()
        contents_element = driver.browser.find_element_by_name(
            'problem_contents')
        contents_element.send_keys(os.path.join(
            _OMEGAUP_ROOT, 'frontend/tests/resources/triangulos.zip'))
        with driver.ajax_page_transition():
            contents_element.submit()

        assert (('/problem/%s/edit/' % problem_alias) in
                driver.browser.current_url), driver.browser.current_url

    with driver.login_user():
        driver.browser.find_element_by_id('nav-problems').click()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-problems"]'
                  '//a[@href = "/problem/"]')))).click()

        search_box_element = driver.browser.find_element_by_id(
            'problem-search-box')
        search_box_element.send_keys(problem_alias)
        with driver.ajax_page_transition():
            search_box_element.submit()

        driver.browser.find_element_by_xpath(
            '//a[text() = "%s"]' % problem_alias).click()

        assert (problem_alias in driver.browser.find_element_by_xpath(
            '//h1[@class="title"]').get_attribute('innerText'))
