#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium identities tests like create, update and associate with a user.

Also, added group create test
'''

from ui import util  # pylint: disable=no-name-in-module


@util.annotate
@util.no_javascript_errors()
def test_create_group_with_identities(driver):
    '''Tests creation of a group with identities.'''

    group_title = 'unittest_group_%s' % driver.generate_id()
    description = 'some text for group description'

    with driver.login_admin():
        group_alias = util.create_group(driver, group_title, description)
        util.add_identities_group(driver, group_alias)
