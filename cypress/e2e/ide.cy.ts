import { profilePage } from '../support/pageObjects/profilePage';
import { problemPage } from '../support/pageObjects/problemPage';
import { loginPage } from '../support/pageObjects/loginPage';
import { LoginOptions, ProblemOptions, RunOptions } from '../support/types';
import * as Util from '../../frontend/www/js/omegaup/graderv2/util';
import * as JSZip from 'jszip';

describe('Test IDE', () => {
  let problemOptions: ProblemOptions[];

  let loginOptions: LoginOptions[];
  before(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');

    loginOptions = loginPage.registerMultipleUsers(1);
    problemOptions = problemPage.generateProblemOptions(2);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);

    cy.logout();
  });

  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should create a problem of type output only and display cat langauge only', () => {
    cy.login(loginOptions[0]);

    const catProblemOptions: ProblemOptions = {
      ...problemOptions[1],
      languagesValue: 'cat',
    };
    cy.createProblem(catProblemOptions);

    cy.visit(`arena/problem/${catProblemOptions.problemAlias}/`);
    cy.get('[data-language-select] option').should('have.length', 1);
    cy.get('[data-language-select] option[value="cat"]').should('exist');

    cy.logout();
  });

  it('Should display full list of supported languages in profile prefrences page', () => {
    cy.login(loginOptions[0]);

    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-preferences"]').click();
    cy.get('[data-preferred-language]').should('exist');

    cy.get('[data-preferred-language]').as('selectMenu').should('exist');
    Object.keys(Util.supportedLanguages).forEach((language) => {
      // cannot select cat language
      if (language === 'cat') return;
      cy.get('@selectMenu').find(`option[value="${language}"]`).should('exist');
    });

    cy.logout();
  });

  it('Should change preferred language for user and follow hierarchical order to define the programming language', () => {
    cy.login(loginOptions[0]);

    // update preferred langauge to py2
    profilePage.updatePreferredLanguage('py2');
    // go to the link with the editor
    cy.visit(`arena/problem/${problemOptions[0].problemAlias}/`);

    cy.get('[data-language-select]')
      .should('be.visible')
      .find('option:selected')
      .should('have.value', 'py2');

    // make the submission with cpp20
    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp20-gcc',
      valid: true,
      status: 'AC',
    };
    problemPage.createRun(runOptions);

    // reload the page, check the language again
    // clear session storage before reloading
    cy.clearAllSessionStorage();
    cy.reload();

    cy.get('.close:visible').each(($button) => {
      cy.wrap($button).click();
    });

    cy.get('[data-language-select]')
      .should('be.visible')
      .find('option:selected')
      .should('have.value', 'cpp20-gcc');

    cy.logout();
  });
});
