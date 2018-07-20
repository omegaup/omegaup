#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium contest tests.'''

import urllib

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC

from ui import util


@util.no_javascript_errors()
@util.annotate
def test_create_contest(driver):
    '''Tests creating a contest and retrieving it.'''

    run_id = driver.generate_id()
    contest_alias = 'ut_contest_%s' % run_id
    problem = 'sumas'
    user1 = 'ut_user_1_%s' % run_id
    user2 = 'ut_user_2_%s' % run_id
    password = 'P@55w0rd'

    driver.register_user(user1, password)
    driver.register_user(user2, password)

    create_contest_admin(driver, contest_alias, problem, [user1, user2],
                         driver.user_username)

    with driver.login(user1, password):
        create_run_user(driver, contest_alias, problem, 'Main.cpp11',
                        verdict='AC', score=1)

    with driver.login(user2, password):
        create_run_user(driver, contest_alias, problem, 'Main_wrong.cpp11',
                        verdict='WA', score=0)

    update_scoreboard_for_contest(driver, contest_alias)

    with driver.login_admin():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'nav-contests'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//li[@id = "nav-contests"]'
                      '//a[@href = "/contest/mine/"]')))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//a[contains(@href, "/arena/%s/scoreboard/")]' %
                      contest_alias)))).click()

        run_accepted_user = driver.browser.find_element_by_xpath(
            '//td[@class="accepted"]/preceding-sibling::td[1]')
        assert run_accepted_user.text == user1, run_accepted_user

        run_wrong_user = driver.browser.find_element_by_xpath(
            '//td[@class="wrong"]/preceding-sibling::td[1]')
        assert run_wrong_user.text == user2, run_wrong_user


@util.no_javascript_errors()
@util.annotate
def test_user_ranking_contest(driver):
    '''Tests creating a contest and reviewing ranking.'''

    run_id = driver.generate_id()
    contest_alias = 'utrank_contest_%s' % run_id
    problem = 'sumas'
    user1 = 'ut_rank_user_1_%s' % run_id
    user2 = 'ut_rank_user_2_%s' % run_id
    password = 'P@55w0rd'

    driver.register_user(user1, password)
    driver.register_user(user2, password)

    create_contest_admin(driver, contest_alias, problem, [user1, user2],
                         driver.user_username)

    with driver.login(user1, password):
        create_run_user(driver, contest_alias, problem, 'Main.cpp11',
                        verdict='AC', score=1)

    with driver.login(user2, password):
        create_run_user(driver, contest_alias, problem, 'Main_wrong.cpp11',
                        verdict='WA', score=0)

    update_scoreboard_for_contest(driver, contest_alias)

    with driver.login_admin():
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH, '//a[@href = "/arena/"]'))).click()

        with driver.page_transition():
            contest_url = '/arena/%s' % contest_alias
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR,
                     '#current-contests a[href="%s"]' % contest_url))).click()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "#ranking"]'))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, '#ranking')))

        run_accepted_user = driver.browser.find_element_by_xpath(
            '//td[@class="accepted"]/preceding-sibling::td[1]')
        assert run_accepted_user.text == user1, run_accepted_user

        run_wrong_user = driver.browser.find_element_by_xpath(
            '//td[@class="wrong"]/preceding-sibling::td[1]')
        assert run_wrong_user.text == user2, run_wrong_user


@util.annotate
def create_contest_admin(driver, contest_alias, problem, users, user):
    '''Creates a contest as an admin.'''

    with driver.login_admin():
        create_contest(driver, contest_alias)

        assert (('/contest/%s/edit/' % contest_alias) in
                driver.browser.current_url), driver.browser.current_url

        add_problem_to_contest(driver, problem)

        add_students_bulk(driver, users)
        add_students_contest(driver, [user])

        contest_url = '/arena/%s' % contest_alias
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     '//a[starts-with(@href, "%s")]' % contest_url))).click()
        assert (contest_alias in
                driver.browser.current_url), driver.browser.current_url

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.ID, 'start-contest-submit'))).click()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "#ranking"]'))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, '#ranking')))
        assert ((contest_url) in
                driver.browser.current_url), driver.browser.current_url


@util.annotate
def update_scoreboard_for_contest(driver, contest_alias):
    '''Updates the scoreboard for a contest.

    This can be run without a session being active.
    '''

    scoreboard_refresh_url = driver.url(
        '/api/scoreboard/refresh/alias/%s/token/secret' %
        urllib.parse.quote(contest_alias, safe=''))
    driver.browser.get(scoreboard_refresh_url)
    assert '{"status":"ok"}' in driver.browser.page_source


@util.annotate
def create_run_user(driver, contest_alias, problem, filename, **kwargs):
    '''Makes the user join a course and then creates a run.'''

    enter_contest(driver, contest_alias)

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[contains(@href, "#problems/%s")]' %
              problem)))).click()

    util.create_run(driver, problem, filename)
    driver.update_score_in_contest(problem, contest_alias, **kwargs)

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'button.details'))).click()
    assert (('show-run:') in
            driver.browser.current_url), driver.browser.current_url


@util.annotate
def create_contest(driver, contest_alias):
    '''Creates a new contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.ID, 'nav-contests'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 ('//li[@id = "nav-contests"]'
                  '//a[@href = "/contest/new/"]')))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.ID, ('title')))).send_keys(contest_alias)
    driver.browser.find_element_by_id('alias').send_keys(
        contest_alias)
    driver.browser.find_element_by_id('description').send_keys(
        'contest description')

    with driver.page_transition():
        driver.browser.find_element_by_tag_name('form').submit()


@util.annotate
def add_students_contest(driver, users):
    '''Add students to a recently contest.'''

    util.add_students(
        driver, users, container_id='contestants',
        parent_xpath='*[@id="contestants"]',
        submit_locator=(By.CLASS_NAME, 'user-add-single'))


@util.annotate
def add_students_bulk(driver, users):
    '''Add students to a recently created contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, ('//a[contains(@href, "#contestants")]')))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '#contestants')))

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, (
                '//textarea[contains(@name, "usernames")]')))).send_keys(
                    ', '.join(users))
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CLASS_NAME, ('user-add-bulk')))).click()
    for user in users:
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH, '//*[@id="contest-users"]//a[text()="%s"]' % user)))


@util.annotate
def add_problem_to_contest(driver, problem):
    '''Add problems to a contest given.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#problems"]'))).click()

    driver.typeahead_helper('*[@id="problems"]', problem)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '#add-problem-form button[type="submit"]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, '//*[@id="problems"]//a[text()="%s"]' % problem)))


@util.annotate
def enter_contest(driver, contest_alias):
    '''Enter contest previously created.'''

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "/arena/"]'))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[contains(@href, "#list-past-contest")]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '#list-past-contest')))

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//a[contains(@href, "#list-current-contest")]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '#list-current-contest')))

    contest_url = '/arena/%s' % contest_alias
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR,
                 '#current-contests a[href="%s"]' % contest_url))).click()
    assert (contest_url in
            driver.browser.current_url), driver.browser.current_url

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'start-contest-submit'))).click()
