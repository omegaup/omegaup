import { contestPage } from '../support/pageObjects/contestPage';
import { loginPage } from '../support/pageObjects/loginPage';
import { problemPage } from '../support/pageObjects/problemPage';
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

    const problemOptions = contestPage.generateProblemOptions(1);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    problemPage.navigateToAllProblemsTab();
    languages.forEach((language) => {
      problemPage.verifyLanguageFilter(language);
    });
    problemPage.verifyLanguageFilter('all');
    problemPage.verifyFilterByAlias(problemOptions[0].problemAlias);
    problemPage.verifyFilterByTags();
    cy.logout();
  });

  it('Should search a problem and solve it', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const problemOptions = contestPage.generateProblemOptions(1);

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
});
