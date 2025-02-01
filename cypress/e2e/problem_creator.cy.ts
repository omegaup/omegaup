import { LoginOptions } from '../support/types';

describe('Problem creator Test', () => {

  const loginOptions: LoginOptions = {
    username: 'user',
    password: 'user',
  };

  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should write and verify the problem statement', () => {
    cy.login(loginOptions);

    cy.visit('/problem/creator/');

    cy.get('[data-problem-creator-tab="statement"]').click();

    cy.get('[data-problem-creator-editor-markdown]').type('Hello omegaUp!');
    cy.get('[data-problem-creator-save-markdown]').click();

    cy.get('[data-problem-creator-previewer-markdown]').should(
      'have.html',
      '<h1>Previsualización</h1>\n\n<p>Hello omegaUp!</p>',
    );
  });

  it('Should write and verify the problem solution', () => {
    cy.login(loginOptions);

    cy.visit('/problem/creator/');

    cy.get('[data-problem-creator-tab="solution"]').click();

    cy.get('[data-problem-creator-solution-editor-markdown]').type(
      'Hello **solution**!',
    );
    cy.get('[data-problem-creator-solution-save-markdown]').click();

    cy.get('[data-problem-creator-solution-previewer-markdown]').should(
      'have.html',
      '<h1>Previsualización</h1>\n\n<p>Hello <strong>solution</strong>!</p>',
    );
  });
});
