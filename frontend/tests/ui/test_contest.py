#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# type: ignore

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
    problem = 'Sumas'
    user = 'ut_user_%s' % run_id
    password = 'P@55w0rd'
    group_title = 'ut_group_%s' % driver.generate_id()
    description = 'group description'

    with driver.login_admin():
        group_alias = util.create_group(driver, group_title, description)
        identity, *_ = util.add_identities_group(driver, group_alias)

    driver.register_user(user, password)
    invited_users = [user, identity.username]
    create_contest_admin(driver, contest_alias, problem, invited_users,
                         driver.user_username, access_mode='Private')

    with driver.login(identity.username,
                      identity.password,
                      is_main_user_identity=False):
        create_run_user(driver, contest_alias, problem, 'Main.cpp17-gcc',
                        verdict='AC', score=1)

    with driver.login(user, password):
        create_run_user(driver, contest_alias, problem, 'Main_wrong.cpp17-gcc',
                        verdict='WA', score=0)

    update_scoreboard_for_contest(driver, contest_alias)

    with driver.login_admin():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-user]'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-user-contests]'))).click()

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     ('//a[contains(@href, "/arena/%s/scoreboard/")]' %
                      contest_alias)))).click()

        assert_run_verdict(driver, identity.username, problem,
                           classname="accepted")
        assert_run_verdict(driver, user, problem, classname="wrong")


@util.no_javascript_errors()
@util.annotate
def test_user_ranking_contest(driver):
    '''Tests creating a contest and reviewing ranking.'''

    run_id = driver.generate_id()
    contest_alias = 'utrank_contest_%s' % run_id
    problem = 'Sumas'
    user1 = 'ut_rank_user_1_%s' % run_id
    user2 = 'ut_rank_user_2_%s' % run_id
    user3 = 'ut_rank_user_3_%s' % run_id
    password = 'P@55w0rd'
    group_title = 'ut_group_%s' % driver.generate_id()
    description = 'group description'

    with driver.login_admin():
        group_alias = util.create_group(driver, group_title, description)
        uninvited_identity, *_ = util.add_identities_group(driver, group_alias)

    driver.register_user(user1, password)
    driver.register_user(user2, password)
    driver.register_user(user3, password)

    create_contest_admin(driver, contest_alias, problem, [user1, user2],
                         driver.user_username)

    with driver.login(user1, password):
        create_run_user(driver, contest_alias, problem, 'Main.cpp17-gcc',
                        verdict='AC', score=1)

    with driver.login(user2, password):
        create_run_user(driver, contest_alias, problem, 'Main_wrong.cpp17-gcc',
                        verdict='WA', score=0)

    with driver.login(user3, password):
        create_run_user(driver, contest_alias, problem, 'Main.cpp17-gcc',
                        verdict='AC', score=1)

    with driver.login(uninvited_identity.username,
                      uninvited_identity.password,
                      is_main_user_identity=False):
        create_run_user(driver, contest_alias, problem, 'Main.cpp17-gcc',
                        verdict='AC', score=1)

    update_scoreboard_for_contest(driver, contest_alias)

    with driver.login_admin():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-user]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-user-contests]'))).click()

        url = '/arena/%s/scoreboard' % (contest_alias)
        util.check_scoreboard_events(driver, contest_alias, url,
                                     num_elements=3, scoreboard='Public')

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-user]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-user-contests]'))).click()
        util.check_scoreboard_events(driver, contest_alias, url,
                                     num_elements=3, scoreboard='Admin')

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-contests]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-contests-arena]'))).click()

        with driver.page_transition():
            select_contests_list(driver, 'data-list-current')
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, '.contest-list a[href="/arena/%s/"]' %
                     contest_alias))).click()

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "#ranking"]'))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, '.omegaup-scoreboard')))

        assert_run_verdict(driver, user1, problem, classname='accepted')
        assert_run_verdict(driver, user2, problem, classname='wrong')

        compare_contestants_list(driver, {user1, user2, driver.user_username})

        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//input[@class = "toggle-contestants"]'))).click()

        users_full_set = {user1, user2, user3, driver.user_username,
                          uninvited_identity.username}
        compare_contestants_list(driver, users_full_set)


@util.no_javascript_errors()
@util.annotate
def select_contests_list(driver, selected_list):
    '''This function allows us select one item of the contest list types
    because now it needs to click on dropdown button first
    '''
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'a[data-contests]'))).click()
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'a[%s]' % selected_list))).click()


@util.no_javascript_errors()
@util.annotate
def test_user_ranking_contest_when_scoreboard_show_time_finished(driver):
    '''Tests creating a contest and reviewing ranking contest when
    scoreboard show time has finished.
    '''

    run_id = driver.generate_id()
    alias = 'utrank_contest_%s' % run_id
    problem = 'Sumas'
    user1 = 'ut_rank_user_1_%s' % run_id
    user2 = 'ut_rank_user_2_%s' % run_id
    password = 'P@55w0rd'

    driver.register_user(user1, password)
    driver.register_user(user2, password)

    create_contest_admin(driver, alias, problem, [user1, user2],
                         driver.user_username, scoreboard_time_percent=1)

    with driver.login(user1, password):
        create_run_user(driver, alias, problem, 'Main.cpp17-gcc',
                        verdict='AC', score=1)

    with driver.login(user2, password):
        create_run_user(driver, alias, problem, 'Main_wrong.cpp17-gcc',
                        verdict='WA', score=0)

    with driver.login(driver.user_username, 'user'):
        create_run_user(driver, alias, problem, 'Main.cpp17-gcc',
                        verdict='AC', score=1)

    update_scoreboard_for_contest(driver, alias)

    with driver.login(driver.user_username, 'user'):
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-contests]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-contests-arena]'))).click()

        contest_url = '/arena/%s' % alias
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     '//a[starts-with(@href, "%s")]' % contest_url))).click()

        # User checks the score, it might be 0 because broadcaster is turned
        # off in Travis, and the only way to get updated results while we are
        # on the same page is going back to the page.
        check_ranking(driver, problem, driver.user_username,
                      scores=['0.00', '+100.00'])


@util.annotate
def check_ranking(driver, problem, user, *, scores):
    ''' Check ranking for a contest'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#ranking"]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '.omegaup-scoreboard')))

    ranking_problem = driver.browser.find_element(
        By.XPATH,
        '//tr[@class = "%s"]/td[contains(@class, "%s")]/div[@class = "points"]'
        % (user, problem))

    assert ranking_problem.text in scores, ranking_problem


@util.no_javascript_errors()
@util.annotate
def test_user_clarifications_contest(driver):
    '''Tests creating a contest and adding a clarification.'''

    run_id = driver.generate_id()
    contest_alias = 'utrank_contest_%s' % run_id
    problem = 'Sumas'
    user1 = 'ut_rank_user_1_%s' % run_id
    password = 'P@55w0rd'

    driver.register_user(user1, password)

    create_contest_admin(driver, contest_alias, problem, [user1],
                         driver.user_username)

    with driver.login(user1, password):
        enter_contest(driver, contest_alias)
        create_clarification_user(driver, problem, 'question 1')

    with driver.login_admin():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-contests]'))).click()
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'a[data-nav-contests-arena]'))).click()

        with driver.page_transition():
            select_contests_list(driver, 'data-list-current')
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, '.contest-list a[href="/arena/%s/"]' %
                     contest_alias))).click()

        answer_clarification_admin(driver, 'no')


# pylint: disable=too-many-arguments
@util.annotate
def create_contest_admin(driver, contest_alias, problem, users, user,
                         access_mode='Public', **kwargs):
    '''Creates a contest as an admin.'''

    with driver.login_admin():
        create_contest(driver, contest_alias, **kwargs)

        assert (('/contest/%s/edit/' % contest_alias) in
                driver.browser.current_url), driver.browser.current_url

        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, 'input[data-title]'))).send_keys('(Updated)')

        driver.browser.find_element(
            By.CSS_SELECTOR,
            'form.contest-form').submit()

        message_link = driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, '#status span.message a')))

        contest_title = driver.browser.find_element(
            By.CSS_SELECTOR,
            '.page-header h1')

        assert '(Updated)' in contest_title.text, 'Update contest failed'
        assert (contest_alias in
                message_link.get_attribute('href')), 'Update contest failed'

        add_problem_to_contest(driver, problem)

        add_students_bulk(driver, users)
        add_students_contest(driver, [user])

        change_contest_admission_mode(driver, access_mode)

        contest_url = '/arena/%s' % contest_alias
        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.XPATH,
                     '//small/a[starts-with(@href, "%s")]' % contest_url
                     ))).click()
        assert (contest_alias in
                driver.browser.current_url), driver.browser.current_url

        with driver.page_transition():
            driver.wait.until(
                EC.element_to_be_clickable(
                    (By.CSS_SELECTOR, 'button[data-start-contest]'))).click()
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH, '//a[@href = "#ranking"]'))).click()
        driver.wait.until(
            EC.visibility_of_element_located(
                (By.CSS_SELECTOR, '.omegaup-scoreboard')))
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
    assert '"status":"ok"' in driver.browser.page_source


@util.annotate
def create_clarification_user(driver, problem, question):
    '''Makes the user post a question in an specific contest and problem'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#clarifications"]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '[data-tab-clarifications]')))

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#clarifications/all/new"]'))).click()

    Select(driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '[data-new-clarification-problem]')))).select_by_value(problem)

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR,
             '[data-new-clarification-message]'))).send_keys(question)

    driver.browser.find_element(
        By.CSS_SELECTOR,
        '[data-new-clarification]').submit()

    clarifications = driver.browser.find_elements(
        By.CSS_SELECTOR,
        '[data-tab-clarifications] table tbody tr')

    assert len(clarifications) == 1, len(clarifications)


@util.annotate
def answer_clarification_admin(driver, answer):
    '''Makes the admin course answer users' clarifications'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH, '//a[@href = "#clarifications"]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '[data-tab-clarifications]')))

    Select(driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             '[data-select-answer]')))).select_by_value(answer)

    driver.browser.find_element(
        By.CSS_SELECTOR,
        '[data-form-clarification-answer]').submit()

    resolved = driver.wait.until(
        EC.visibility_of_element_located((By.CSS_SELECTOR, 'tr.resolved')))

    assert 'resolved' in resolved.get_attribute('class').split(), resolved


@util.annotate
def create_run_user(driver, contest_alias, problem, filename, **kwargs):
    '''Makes the user join a course and then creates a run.'''

    enter_contest(driver, contest_alias)

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             ('//a[contains(text(), "%s")]/parent::div' %
              problem.title())))).click()

    util.create_run(driver, problem, filename)
    driver.update_score_in_contest(problem, contest_alias, **kwargs)

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'button[data-run-details]'))).click()
    assert (('show-run:') in
            driver.browser.current_url), driver.browser.current_url


@util.annotate
def create_contest(driver, alias, scoreboard_time_percent=100):
    '''Creates a new contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'a[data-nav-contests]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-contests-create]'))).click()

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'input[data-title]'))).send_keys(alias)
    driver.browser.find_element(By.NAME, 'alias').send_keys(alias)
    driver.browser.find_element(By.NAME, 'description').send_keys(
        'contest description')
    scoreboard_element = driver.browser.find_element(By.NAME, 'scoreboard')
    scoreboard_element.clear()
    scoreboard_element.send_keys(scoreboard_time_percent)

    with driver.page_transition():
        driver.browser.find_element(
            By.CSS_SELECTOR,
            'form.contest-form').submit()


@util.annotate
def add_students_contest(driver, users):
    '''Add students to a recently contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'a[data-nav-contest-edit]'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, '//a[@data-nav-contestant]')))
    util.add_students_to_contest(
        driver, users,
        tab_xpath='//a[@data-nav-contestant]',
        container_xpath='//div[contains(@class, "contestants-input-area")]',
        parent_selector='.contestants',
        submit_locator=(By.CLASS_NAME, 'user-add-typeahead'))


@util.annotate
def add_students_bulk(driver, users):
    '''Add students to a recently created contest.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'a[data-nav-contest-edit]'))).click()
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             ('a.contestants')))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'div.contestants')))

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH, (
                '//textarea[contains(@class, "contestants")]')))).send_keys(
                    ', '.join(users))
    with util.dismiss_status(driver):
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
             'a[data-nav-contest-edit]'))).click()
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'a.problems'))).click()

    driver.typeahead_helper('.problems-container', problem)
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//input[contains(concat(" ", normalize-space(@class), " "), " '
             'problem-points ")]'))).click()
    with util.dismiss_status(driver):
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, '.btn.add-problem'))).click()
    driver.wait.until(
        EC.visibility_of_element_located(
            (By.XPATH,
             '//*[contains(concat(" ", normalize-space(@class), " "), " table'
             ' ")]//a[@href="/arena/problem/%s/"]' % problem)))


@util.annotate
def enter_contest(driver, contest_alias):
    '''Enter contest previously created.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, 'a[data-nav-contests]'))).click()
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'a[data-nav-contests-arena]'))).click()

    select_contests_list(driver, 'data-list-past')
    select_contests_list(driver, 'data-list-current')

    driver.wait.until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, '.contest-list .list-current')))

    contest_url = '/arena/%s/' % contest_alias
    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.XPATH,
                 '//div[contains(concat(" ", normalize-space(@class), " "'
                 '), " contest-list")]//div[contains(concat(" ", '
                 'normalize-space(@class), " "), " list-current ")]//a[@href='
                 '"%s"]' % contest_url))).click()
    assert (contest_url in
            driver.browser.current_url), driver.browser.current_url

    with driver.page_transition():
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, 'button[data-start-contest]'))).click()


@util.annotate
def change_contest_admission_mode(driver, contest_admission_mode):
    '''Change admission mode for a contetst.'''

    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'a[data-nav-contest-edit]'))).click()
    driver.wait.until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR,
             'a.admission-mode'))).click()
    Select(driver.wait.until(
        EC.element_to_be_clickable(
            (By.XPATH,
             '//select[@name = "admission-mode"]')))).select_by_visible_text(
                 contest_admission_mode)
    with util.dismiss_status(driver):
        driver.wait.until(
            EC.element_to_be_clickable(
                (By.CSS_SELECTOR, '.btn.change-admission-mode'))).click()


@util.annotate
def compare_contestants_list(driver, users_set):
    ''' Compares list of contestants toggle scoreboard filter.'''

    contestants_list = driver.browser.find_elements(
        By.XPATH, '//*[@data-table-scoreboard]/tbody/tr/td[@class="user"]')
    # Considering only the username. All unassociated identities are created
    # with a name, which is appended after the username, like:
    #
    #     ut_group_w0_1564721415_4:identity_1 (Identity One)
    contestants_set = {item.text.split()[0] for item in contestants_list}

    different_users = contestants_set ^ users_set
    assert contestants_set == users_set, different_users


@util.annotate
def assert_run_verdict(driver, user, problem, *, classname):
    ''' Asserts that run verdict matches with expected classname. '''

    run_verdict = driver.browser.find_element(
        By.XPATH,
        '//tr[contains(concat(" ", normalize-space(@class), " "), " %s ")]'
        '/td[contains(concat(" ", normalize-space(@class), " "), " %s ")]'
        % (user, problem))
    assert classname in run_verdict.get_attribute('class').split(), run_verdict
