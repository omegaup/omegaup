#!/usr/bin/python3
# -*- coding: utf-8 -*-
# type: ignore

'''Run Selenium identities tests like create, update and associate with a user.

Also, added group create test
'''

from ui import util  # pylint: disable=no-name-in-module

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC


@util.annotate
@util.no_javascript_errors()
def test_create_group_with_identities_and_restrictions(driver):
    '''Tests creation of a group with identities.'''

    group_title = 'unittest_group_%s' % driver.generate_id()
    description = 'some text for group description'

    with driver.login_admin():
        navbar = driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, 'ul.nav:first-child')))

        navbar.find_element_by_css_selector('li.nav-problems a').click()
        problems_dropdown = driver.wait.until(
            EC.visibility_of(
                navbar.find_element_by_css_selector('li.nav-problems ul')))
        # Problems menu
        for present_href in ['/problem/', '/submissions/', '/problem/new/',
                             '/problem/mine/', '/nomination/mine/']:
            assert problems_dropdown.find_elements_by_css_selector(
                'a[href="%s"]' % present_href), (
                    '%s item is not present!' % present_href)

        # Contests menu
        assert navbar.find_elements_by_css_selector('li.nav-contests a')

        group_alias = util.create_group(driver, group_title, description)
        identity, *_ = util.add_identities_group(driver, group_alias)

    with driver.login(identity.username, identity.password):
        navbar = driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, 'ul.nav:first-child')))

        # Problems menu
        navbar.find_element_by_css_selector('li.nav-problems a').click()
        problems_dropdown = driver.wait.until(
            EC.visibility_of(
                navbar.find_element_by_css_selector('li.nav-problems ul')))
        for present_href in ['/problem/', '/submissions/']:
            assert problems_dropdown.find_elements_by_css_selector(
                'a[href="%s"]' % present_href), (
                    '%s item is not present!' % present_href)
        for absent_href in ['/problem/new/', '/problem/mine/',
                            '/nomination/mine/']:
            assert not problems_dropdown.find_elements_by_css_selector(
                'a[href="%s"]' % absent_href), (
                    '%s item is visible!' % absent_href)

        # Contests menu
        assert not navbar.find_elements_by_css_selector('li.nav-contests a')

        # Courses list
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH, '//a[@href = "/schools/"]'))).click()
        assert not driver.browser.find_elements_by_css_selector(
            'a[href="/course/new/"]')

        inaccessible_paths = ['/problem/new/', '/problem/mine/',
                              '/nomination/mine/', '/contest/new/',
                              '/contest/mine/', '/group/', '/course/new/']
        for inaccessible_path in inaccessible_paths:
            with util.assert_js_errors(driver,
                                       expected_paths=(inaccessible_path,)):
                with driver.page_transition():
                    driver.browser.get(driver.url(inaccessible_path))
                    assert_page_not_found_is_shown(driver, inaccessible_path)


def assert_page_not_found_is_shown(driver, url):
    ''' Asserts user or identity does not have access to the page.'''

    error_page = driver.wait.until(
        EC.visibility_of_element_located((By.XPATH, '//h1/strong')))
    error_symbol = error_page.get_attribute('title')
    assert 'omega' in error_symbol, url

    error_page = driver.wait.until(
        EC.visibility_of_element_located((By.XPATH, '//h1/span')))
    error_down = error_page.get_attribute('title')
    assert 'Down' in error_down, url
