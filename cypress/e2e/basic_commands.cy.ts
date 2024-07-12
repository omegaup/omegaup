import { v4 as uuid } from 'uuid';
import { LoginOptions, RunOptions } from '../support/types';
import { loginPage } from '../support/pageObjects/loginPage';
import { coursePage } from '../support/pageObjects/coursePage';
import { contestPage } from '../support/pageObjects/contestPage';
import { problemPage } from '../support/pageObjects/problemPage';

describe('Basic Commands Test', () => {
  beforeEach(() => {
    // check if user is logged in, so we logout before each test
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should create a course with unlimited duration', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const courseOptions = coursePage.generateCourseOptions();

    cy.login(loginOptions[0]);
    courseOptions.unlimitedDuration = true;
    cy.createCourse(courseOptions);
    coursePage.verifyCourseOptions(courseOptions);
    cy.logout();
  });

  it('Should create a course with end date', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const courseOptions = coursePage.generateCourseOptions();

    cy.login(loginOptions[0]);
    cy.createCourse(courseOptions);
    coursePage.verifyCourseOptions(courseOptions);
    cy.logout();
  });

  it('Should register a user using the API', () => {
    const loginOptions: LoginOptions = {
      username: uuid(),
      password: uuid(),
    };

    cy.register(loginOptions);
    loginPage.verifyUsername(loginOptions);
  });

  it('Should register a user', () => {
    const loginOptions: LoginOptions = {
      username: uuid(),
      password: uuid(),
    };

    loginPage.registerSingleUser(loginOptions);
    loginPage.verifyUsername(loginOptions);
  });

  it('Should login a user using the API', () => {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };

    cy.login(loginOptions);
    loginPage.verifyUsername(loginOptions);
  });

  it('Should login a user', () => {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };

    loginPage.loginByGUI(loginOptions);
    loginPage.verifyUsername(loginOptions);
  });

  it('Should create a problem', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const problemOptions = problemPage.generateProblemOptions(1);
    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    coursePage.verifyProblem(problemOptions[0]);
    cy.logout();
  });

  it('Should make a run of a problem', () => {
    const problemOptions = problemPage.generateProblemOptions(1)[0];

    const runOptions: RunOptions = {
      problemAlias: problemOptions.problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login({ username: 'user', password: 'user' });
    cy.createProblem(problemOptions);
    cy.createRun(runOptions);
    coursePage.verifyProblemRun('AC');
    cy.logout();
  });

  it('Should create a contest', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(loginOptions[0]);
    const users = [loginOptions[1].username];

    loginPage.giveAdminPrivilege(
      'GroupIdentityCreator',
      loginOptions[0].username,
    );

    cy.login(loginOptions[0]);
    contestPage.createContest(contestOptions, users);
    contestPage.verifyContestDetails(contestOptions);
    cy.logout();
  });

  it('Should create runs inside contest', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(loginOptions[1]);
    const users = [loginOptions[0].username];

    cy.login(loginOptions[1]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(loginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();
  });

  // FIXME: @tech-debt Remove `only` when this test is fixed
  it.only('Should create two contests and merge their scoreboard', () => {
    cy.log('Should create two contests and merge their scoreboard');
    const loginOptions = loginPage.registerMultipleUsers(4);
    const contestOptions1 = contestPage.generateContestOptions(loginOptions[1]);
    const contestOptions2 = contestPage.generateContestOptions(
      loginOptions[2],
      false,
    );
    const users1 = [loginOptions[0].username, loginOptions[3].username];
    const users2 = [loginOptions[3].username];

    cy.log('Create first contest with two users');
    cy.login(loginOptions[1]);
    contestPage.createContest(contestOptions1, users1);
    cy.logout();

    cy.login(loginOptions[0]);
    cy.enterContest(contestOptions1);
    cy.createRunsInsideContest(contestOptions1);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[0].username} > td:nth-child(4)`).should(
      'contain',
      '0.00',
    );
    cy.logout();

    cy.login(loginOptions[2]);
    cy.log('Create second contest with one user');
    contestPage.createContest(contestOptions2, users2, false);
    cy.logout();

    cy.login(loginOptions[3]);
    cy.enterContest(contestOptions2);
    cy.createRunsInsideContest(contestOptions2);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get(`.${loginOptions[3].username} > td:nth-child(4)`).should(
      'contain',
      '0.00',
    );
    cy.logout();

    cy.login(loginOptions[0]);
    contestPage.mergeContests([
      contestOptions1.contestAlias,
      contestOptions2.contestAlias,
    ]);
    contestPage.verifyMergedScoreboard([
      loginOptions[0].username,
      loginOptions[3].username,
    ]);
    cy.logout();
  });
});
