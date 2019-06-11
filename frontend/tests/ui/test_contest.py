#!/usr/bin/python3
# -*- coding: utf-8 -*-

'''Run Selenium contest tests.'''

import urllib

from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.select import Select

from ui import util  # pylint: disable=no-name-in-module


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
            '//td[contains(@class, "accepted")]/preceding-sibling::td[@class='
            '"user"]')
        assert run_accepted_user.text == user1, run_accepted_user

        run_wrong_user = driver.browser.find_element_by_xpath(
            '//td[contains(@class, "wrong")]/preceding-sibling::td[@class='
            '"user"]')
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
    user3 = 'ut_rank_user_3_%s' % run_id
    password = 'P@55w0rd'

    driver.register_user(user1, password)
    driver.register_user(user2, password)
    driver.register_user(user3, password)

    create_contest_admin(driver, contest_alias, problem, [user1, user2],
                         driver.user_username)

    with driver.login(user1, password):
        create_run_user(driver, contest_alias, problem, 'Main.cpp11',
                        verdict='AC', score=1)

    with driver.login(user2, password):
        create_run_user(driver, contest_alias, problem, 'Main_wrong.cpp11',
                        verdict='WA', score=0)

    with driver.login(user3, password):
        create_run_user(driver, contest_alias, problem, 'Main.cpp11',
                        verdict='AC', score=1)

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

        url = '/arena/%s/scoreboard' % (contest_alias)
        util.check_scoreboard_events(driver, contest_alias, url,
                                     num_elements=2, scoreboard='Public')

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.ID, 'nav-contests'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//li[@id = "nav-contests"]'
                      '//a[@href = "/contest/mine/"]')))).click()
        util.check_scoreboard_events(driver, contest_alias, url,
                                     num_elements=2, scoreboard='Admin')

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH, '//a[@href = "/arena/"]'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR,
                     '#current-contests a[href="/arena/%s"]' %
                     contest_alias))).click()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "#ranking"]'))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, '#ranking')))

        run_accepted_user = driver.browser.find_element_by_xpath(
            '//td[contains(@class, "accepted")]/preceding-sibling::td[@class='
            '"user"]')
        assert run_accepted_user.text == user1, run_accepted_user

        run_wrong_user = driver.browser.find_element_by_xpath(
            '//td[contains(@class, "wrong")]/preceding-sibling::td[@class='
            '"user"]')
        assert run_wrong_user.text == user2, run_wrong_user

        users_invited_set = {user1, user2, driver.user_username}
        compare_contestants_list(driver, users_invited_set)

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//input[@class = "toggle-contestants"]'))).click()

        users_full_set = {user1, user2, user3, driver.user_username}
        compare_contestants_list(driver, users_full_set)


@util.no_javascript_errors()
@util.annotate
def test_user_ranking_contest_when_scoreboard_show_time_finished(driver):
    '''Tests creating a contest and reviewing ranking contest when
    scoreboard show time has finished.
    '''

    run_id = driver.generate_id()
    alias = 'utrank_contest_%s' % run_id
    problem = 'sumas'
    user1 = 'ut_rank_user_1_%s' % run_id
    user2 = 'ut_rank_user_2_%s' % run_id
    password = 'P@55w0rd'

    driver.register_user(user1, password)
    driver.register_user(user2, password)

    create_contest_admin(driver, alias, problem, [user1, user2],
                         driver.user_username, scoreboard_time_percent=0)

    with driver.login(user1, password):
        create_run_user(driver, alias, problem, 'Main.cpp11',
                        verdict='AC', score=1)

    with driver.login(user2, password):
        create_run_user(driver, alias, problem, 'Main_wrong.cpp11',
                        verdict='WA', score=0)

    update_scoreboard_for_contest(driver, alias)

    with driver.login(driver.user_username, 'user'):
        create_run_user(driver, alias, problem, 'Main.cpp11',
                        verdict='AC', score=1)

    with driver.login(driver.user_username, 'user'):
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, '.navbar-brand'))).click()

        contest_url = '/arena/%s' % alias
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     '//a[starts-with(@href, "%s")]' % contest_url))).click()

        # User checks the score, it might be be 0 because broadcaster is turned
        # off in Travis, and the only way to get updated results while we are
        # on the same page is going back to the page.
        check_ranking(driver, problem, driver.user_username,
                      scores=['0.00', '100.00'])

        # User enters to problem in contest, the ranking for this problem
        # should update.
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "#problems"]'))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, '#problems')))

        driver.browser.find_element_by_xpath(
            '//a[contains(@href, "problems/%s")]' % problem).click()

        # Now, user checks the score again, ranking should be 100
        check_ranking(driver, problem, driver.user_username, scores=['100.00'])


@util.annotate
def check_ranking(driver, problem, user, *, scores):
    ''' Check ranking for a contest'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#ranking"]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '#ranking')))

    ranking_problem = driver.browser.find_element_by_xpath(
        '//tr[@class = "%s"]/td[contains(@class, "%s")]/div[@class = "points"]'
        % (user, problem))

    assert ranking_problem.text in scores, ranking_problem


@util.annotate
def create_contest_admin(driver, contest_alias, problem, users, user,
                         **kwargs):
    '''Creates a contest as an admin.'''

    with driver.login_admin():
        create_contest(driver, contest_alias, **kwargs)

        assert (('/contest/%s/edit/' % contest_alias) in
                driver.browser.current_url), driver.browser.current_url

        add_problem_to_contest(driver, problem)

        add_students_bulk(driver, users)
        add_students_contest(driver, [user])

        change_contest_admission_mode(driver, 'Public')

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
def create_contest(driver, contest_alias, scoreboard_time_percent=100):
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
    scoreboard_element = driver.browser.find_element_by_id('scoreboard')
    scoreboard_element.clear()
    scoreboard_element.send_keys(scoreboard_time_percent)

    with driver.page_transition():
        driver.browser.find_element_by_tag_name('form').submit()


@util.annotate
def add_students_contest(driver, users):
    '''Add students to a recently contest.'''

    util.add_students(
        driver, users,
        tab_xpath='//li[contains(@class, "contestants")]//a',
        container_xpath='//div[contains(@class, "contestants-input-area")]',
        parent_xpath='div[contains(@class, "contestants")]',
        submit_locator=(By.CLASS_NAME, 'user-add-single'))


@util.annotate
def add_students_bulk(driver, users):
    '''Add students to a recently created contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             ('li.contestants > a')))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'div.contestants')))

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, (
                '//textarea[contains(@class, "contestants")]')))).send_keys(
                    ', '.join(users))
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CLASS_NAME, ('user-add-bulk')))).click()
    for user in users:
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.XPATH,
                 '//table[contains(@class, "participants")]//a[text()="%s"]'
                 % user)))


@util.annotate
def add_problem_to_contest(driver, problem):
    '''Add problems to a contest given.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'li.problems > a'))).click()

    driver.typeahead_helper('*[contains(@class, "problems-container")]',
                            problem)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, '.btn.add-problem'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//*[contains(@class, "table")]//a[text()="%s"]' % problem)))


@util.annotate
def enter_contest(driver, contest_alias):
    '''Enter contest previously created.'''

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "/arena/"]'))).click()

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//ul[contains(@class, "arena-tabs")]'
                       '/li[contains(@class, "active")]')))
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


@util.annotate
def change_contest_admission_mode(driver, contest_admission_mode):
    '''Change admission mode for a contetst.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'li.admission-mode > a'))).click()
    Select(driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//select[@name = "admission-mode"]')))).select_by_visible_text(
                 contest_admission_mode)
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, '.btn.change-admission-mode'))).click()


@util.annotate
def compare_contestants_list(driver, users_set):
    ''' Compares list of contestants toggle scoreboard filter.'''

    contestants_list = driver.browser.find_elements_by_xpath(
        '//*[@id="ranking"]/div/table/tbody/tr/td[@class="user"]')
    contestants_set = {item.text for item in contestants_list}
    different_users = contestants_set ^ users_set
    assert contestants_set == users_set, different_users
