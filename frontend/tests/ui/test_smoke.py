#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium end-to-end tests.'''

import random

def test_create_user(driver):
    '''Tests basic functionality.'''

    # Home page
    home_page_url = driver.url('/')
    driver.browser.get(home_page_url)
    driver.browser.find_element_by_id('navbar-login').click()

    # Login screen
    driver.wait.until(lambda _: driver.browser.current_url != home_page_url)
    username = 'unittest_user_' + ''.join(
        random.choice('abcdefghijklmnopqrstuvwxyz_') for _ in range(8))
    driver.browser.find_element_by_id('reg_username').send_keys(username)
    driver.browser.find_element_by_id('reg_email').send_keys(
        '%s@localhost.localdomain' % username)
    driver.browser.find_element_by_id('reg_pass').send_keys('p@ssw0rd')
    driver.browser.find_element_by_id('reg_pass2').send_keys('p@ssw0rd')
    driver.browser.find_element_by_id('register-form').submit()
    driver.wait.until(lambda _: driver.browser.current_url == home_page_url)

    # Home screen
    driver.browser.get(driver.url('/logout/?redirect=/'))
    driver.wait.until(lambda _: driver.browser.current_url == home_page_url)

def test_login(driver):
    with driver.login():
        pass

    with driver.login(username='omegaup', password='omegaup'):
        pass
