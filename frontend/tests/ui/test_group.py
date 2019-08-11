#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium identities tests like create, update and associate with a user.

Also, added group create test
'''

from ui import util  # pylint: disable=no-name-in-module


@util.annotate
@util.no_javascript_errors()
def test_create_group_with_identities_and_restrictions(driver):
    '''Tests creation of a group with identities.'''

    group_title = 'unittest_group_%s' % driver.generate_id()
    description = 'some text for group description'

    with driver.login_admin():
        group_alias = util.create_group(driver, group_title, description)
        identity, *_ = util.add_identities_group(driver, group_alias)

    with driver.login(identity.username, identity.password):
        # Trying to create a contest
        with util.assert_js_errors(driver, message=('/api/contest/create/',)):
            util.create_contest(driver, 'some_alias', has_privileges=False)

        # Trying to create a course
        course = 'curse_alias'
        school = 'school_alias'
        with util.assert_js_errors(driver, message=('/api/course/create/',)):
            util.create_course(driver, course, school, has_privileges=False)

        # Trying to create a problem
        with util.assert_js_errors(driver):
            util.create_problem(driver, 'some_alias', has_privileges=False)

        # Trying to see the list of contests created by the identity
        with util.assert_js_errors(driver):
            util.assert_page_not_found(driver, 'contest')

    with driver.login(identity.username, identity.password):
        # Trying to see the list of problems created by the identity
        with util.assert_js_errors(driver):
            util.assert_page_not_found(driver, 'problem')
