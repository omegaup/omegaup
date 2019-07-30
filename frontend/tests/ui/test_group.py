#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium identities tests like create, update and associate with a user.

Also, added group create test
'''

import os

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

from ui import util  # pylint: disable=no-name-in-module


@util.annotate
@util.no_javascript_errors()
def test_create_group_with_identities(driver):
    '''Tests creation of a group with identities.'''

    group_title = 'unittest_group_%s' % driver.generate_id()
    description = 'some text for group description'
    create_group(driver, group_title, description)


@util.annotate
@util.no_javascript_errors()
def create_group(driver, group_title, description):
    ''' Creates a group as an admin. '''

    with driver.login_admin():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'nav-contests'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//li[@id = "nav-contests"]'
                      '//a[@href = "/group/"]')))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//a[@href = "/group/new/"]')))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//input[@name = "title"]'))).send_keys(group_title)
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//textarea[@name = "description"]'))).send_keys(description)
        with driver.page_transition():
            driver.wait.until(
                EC.visibility_of_element_located(
                    (By.XPATH,
                     '//form[@class = "new_group_form"]'))).submit()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[contains(@href, "#identities")]'))).click()
        identities_element = driver.browser.find_element_by_name('identities')
        identities_element.send_keys(os.path.join(
            util.OMEGAUP_ROOT, 'frontend/tests/resources/identities.csv'))
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//div[@class = "upload-csv"]/div/a'))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//table[contains(@class, "identities-table")]/tbody/tr')))
        create_identities_button = driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//button[contains(@name, "create_identities")]')))
        create_identities_button.click()
        message = driver.wait.until(
            EC.visibility_of_element_located((By.ID, 'status')))
        message_class = message.get_attribute('class')

        assert 'success' in message_class, message_class
