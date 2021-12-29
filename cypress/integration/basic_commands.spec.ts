import { v4 as uuid } from 'uuid';

describe('Basic Commands Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should register a user using the API', () => {
    const username = uuid();
    const password = uuid();
    cy.register({ username, password });
    cy.get('header .username').should('have.text', username);
  });

  it('Should register a user', () => {
    const username = uuid();
    const password = uuid();
    cy.get('[data-login-button]').click();
    cy.get('[data-signup-username]').type(username);
    cy.get('[data-signup-password]').type(password);
    cy.get('[data-signup-repeat-password]').type(password);
    cy.get('[data-signup-email]').type(`${username}@omegaup.com`);
    cy.get('[data-signup-submit]').click();
    cy.waitUntil(() =>
      cy.get('header .username').should('have.text', username),
    );
  });

  it('Should login a user using the API', () => {
    const username = 'user';
    const password = 'user';
    cy.login({ username, password });
    cy.get('header .username').should('have.text', username);
  });

  it('Should login a user', () => {
    const username = 'user';
    const password = 'user';
    cy.get('[data-login-button]').click();
    cy.get('[data-login-username]').type(username);
    cy.get('[data-login-password]').type(password);
    cy.get('[data-login-submit]').click();
    cy.waitUntil(() =>
      cy.get('header .username').should('have.text', username),
    );
  });

  it('Should create a problem', () => {
    const username = uuid();
    const password = uuid();
    const problemAlias = uuid().slice(0, 10); // Too large for the alias
    const tag = 'Recursion';
    const autoCompleteTextTag = 'recur';
    const problemLevelIndex = 0;

    cy.register({ username, password });
    // Select problem nav
    cy.get('[data-nav-problems]').click();
    cy.get('[data-nav-problems-create]').click();
    // Fill basic problem form
    cy.get('[name="title"]').type(problemAlias).blur();

    // Alias should be the same as title.
    cy.get('[name="problem_alias"]').should('have.value', problemAlias);

    cy.get('[name="source"]').type(problemAlias);
    cy.get('[name="problem_contents"]').attachFile('testproblem.zip');
    cy.get('[data-tags-input]').type(autoCompleteTextTag);

    // Tags panel
    cy.waitUntil(() =>
      cy
        .get('[data-tags-input] a:first')
        .should('have.text', tag) // Maybe theres another way to avoid to hardcode this
        .click(),
    );

    cy.get('[name="problem-level"]').select(problemLevelIndex); // How can we assert this with the real text?

    cy.get('button[type="submit"]').click(); // Submit

    // Assert problem has been created
    cy.location('href').should('include', problemAlias); // Url
    cy.get('[name="title"]').should('have.value', problemAlias); // Title
    cy.get('[name="problem_alias"]').should('have.value', problemAlias);
    cy.get('[name="source"]').should('have.value', problemAlias);
  });
});
