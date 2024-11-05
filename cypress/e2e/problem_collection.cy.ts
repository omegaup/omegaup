import { loginPage } from '../support/pageObjects/loginPage';
import { problemPage } from '../support/pageObjects/problemPage';
import { profilePage } from '../support/pageObjects/profilePage';
import { RunOptions } from '../support/types';

describe('Problem Collection Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should view and verify all problems tab', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const languages = ['all', 'en', 'es', 'pt'];

    const problemOptions = problemPage.generateProblemOptions(1);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    problemPage.navigateToAllProblemsTab();
    languages.forEach((language) => {
      problemPage.verifyLanguageFilter(language);
    });
    problemPage.verifyLanguageFilter('all');
    problemPage.verifyFilterByAlias(problemOptions[0].problemAlias);
    cy.logout();
  });

  it('Should search a problem and solve it', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const problemOptions = problemPage.generateProblemOptions(1);

    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    problemPage.navigateToAllProblemsTab();
    problemPage.verifyFilterByAlias(problemOptions[0].problemAlias);
    problemPage.openProblem(problemOptions[0].problemAlias);
    problemPage.createRun(runOptions);
    problemPage.verifySubmission(loginOptions[0].username);
    cy.logout();
  });

  it('Should add additional tags to a problem as admin', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const problemOptions = problemPage.generateProblemOptions(1);
    problemOptions[0].problemLevelIndex = 5;

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    cy.logout();

    cy.loginAdmin();
    problemPage.navigateToAllProblemsTab();
    problemPage.verifyFilterByAlias(problemOptions[0].problemAlias);
    problemPage.openProblem(problemOptions[0].problemAlias);
    problemPage.qualifyProblem(['Dynamic programming', 'Backtracking']);
    cy.logout();

    cy.login(loginOptions[0]);
    problemPage.navigateToAllProblemsTab();
    problemPage.verifyFilterByAlias(problemOptions[0].problemAlias);
    cy.logout();
  });

  it('Should report a problem', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const problemOptions = problemPage.generateProblemOptions(1);
    problemOptions[0].publicAccess = true;

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    cy.logout();

    cy.login(loginOptions[1]);
    problemPage.navigateToAllProblemsTab();
    problemPage.verifyFilterByAlias(problemOptions[0].problemAlias);
    problemPage.openProblem(problemOptions[0].problemAlias);
    problemPage.reportProblem('offensive');
    cy.logout();

    cy.loginAdmin();
    problemPage.banProblem(problemOptions[0].problemAlias);
    cy.logout();

    cy.login(loginOptions[1]);
    problemPage.navigateToAllProblemsTab();
    problemPage.verifyBan(problemOptions[0].problemAlias);
    cy.logout();
  });

  it('Should be able to remove problems with no submissions', () => {
    const numberOfProblems = 3;
    const numberOfUsers = 1;
    const loginOptions = loginPage.registerMultipleUsers(numberOfUsers);

    const problemOptions = problemPage.generateProblemOptions(numberOfProblems);

    cy.login(loginOptions[0]);
    // Create 3 problems
    cy.createProblem(problemOptions[0]);
    cy.createProblem({ ...problemOptions[1], firstTimeVisited: false });
    cy.createProblem({ ...problemOptions[2], firstTimeVisited: false });

    profilePage.navigateToMyProblemsPage();

    // Verify that the problems are displayed
    problemOptions.forEach((problem) => {
      profilePage.verifyProblemIsVisible(problem.problemAlias);
    });

    // Delete single problem
    profilePage.deleteProblem(problemOptions[0].problemAlias);

    // Problem 0 should not be visible
    profilePage.verifyProblemIsNotVisible(problemOptions[0].problemAlias);

    // Delete problems in batch
    const problemAliases = problemOptions
      .filter(
        (problem) => problem.problemAlias !== problemOptions[0].problemAlias,
      )
      .map((problem) => problem.problemAlias);
    profilePage.deleteProblemsInBatch(problemAliases);

    // None of the problems should be visible
    problemOptions.forEach((problem) => {
      profilePage.verifyProblemIsNotVisible(problem.problemAlias);
    });

    cy.logout();
  });
});
