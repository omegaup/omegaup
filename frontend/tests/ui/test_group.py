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
                (By.CSS_SELECTOR, '.navbar-nav:first-child')))

        navbar.find_element_by_css_selector(
            'li.nav-problems a.dropdown-toggle').click()
        problems_dropdown = driver.wait.until(
            EC.visibility_of(
                navbar.find_element_by_css_selector(
                    'li.nav-problems .dropdown-menu')))
        # Problems menu
        for present_href in ['/problem/collection/', '/submissions/',
                             '/problem/new/']:
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
                (By.CSS_SELECTOR, '.navbar-nav:first-child')))

        # Problems menu
        navbar.find_element_by_css_selector(
            'li.nav-problems a.dropdown-toggle').click()
        problems_dropdown = driver.wait.until(
            EC.visibility_of(
                navbar.find_element_by_css_selector(
                    'li.nav-problems .dropdown-menu')))
        for present_href in ['/problem/', '/submissions/']:
            assert problems_dropdown.find_elements_by_css_selector(
                'a[href="%s"]' % present_href), (
                    '%s item is not present!' % present_href)
        for absent_href in ['/problem/new/']:
            assert not problems_dropdown.find_elements_by_css_selector(
                'a[href="%s"]' % absent_href), (
                    '%s item is visible!' % absent_href)

        navbar.find_element_by_css_selector(
            'li.nav-contests a.dropdown-toggle').click()
        contests_dropdown = driver.wait.until(
            EC.visibility_of(
                navbar.find_element_by_css_selector(
                    'li.nav-contests .dropdown-menu')))
        for present_href in ['/arena/']:
            assert contests_dropdown.find_elements_by_css_selector(
                'a[href="%s"]' % present_href), (
                    '%s item is not present!' % present_href)
        for absent_href in ['/contest/new/', '/scoreboardmerge/']:
            assert not contests_dropdown.find_elements_by_css_selector(
                'a[href="%s"]' % absent_href), (
                    '%s item is visible!' % absent_href)

        # Courses list
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-courses]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-courses-all]'))).click()
        assert not driver.browser.find_elements_by_css_selector(
            'a[href="/course/new/"]')

        inaccessible_paths = ['/problem/new/', '/contest/new/',
                              '/course/new/']
        for inaccessible_path in inaccessible_paths:
            # Not using assert_js_errors() since this only produces JS errors
            # with chromedriver, not with saucelabs/Travis.
            with util.assert_no_js_errors(driver,
                                          path_whitelist=(inaccessible_path,)):
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
