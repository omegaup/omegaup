#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium identities tests like create, update and associate with a user.

Also, added group create test
'''

from ui import util  # pylint: disable=no-name-in-module


@util.annotate
@util.no_javascript_errors(path_whitelist=('/api/contest/create/',
                                           '/js/dist/commons.js',
                                           '/api/course/create/',
                                           '/contest/mine/',
                                           '/problem/mine/'),
                           message_whitelist=('/api/contest/create/',
                                              '/api/course/create/'))
def test_create_group_with_identities_and_restrictions(driver):
    '''Tests creation of a group with identities.'''

    group_title = 'unittest_group_%s' % driver.generate_id()
    description = 'some text for group description'

    with driver.login_admin():
        group_alias = util.create_group(driver, group_title, description)
        identity, *_ = util.add_identities_group(driver, group_alias)

    with driver.login(identity.username, identity.password):
        # Trying to create a contest
        util.create_contest(driver, 'contest_alias', has_privileges=False)

        # Trying to create a course
        util.create_course(driver, 'course_alias', 'school_name',
                           has_privileges=False)

        # Trying to create a problem
        util.create_problem(driver, 'problem_alias', has_privileges=False)

        # Trying to see the list of contests created by the identity
        util.assert_page_not_found(driver, 'contest')

    with driver.login(identity.username, identity.password):
        # Trying to see the list of problems created by the identity
        util.assert_page_not_found(driver, 'problem')
