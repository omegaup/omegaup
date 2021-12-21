import { v4 as uuid } from 'uuid';

describe('Basic Commands Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Should register an user using the API', () => {
    cy.visit('/');
    const username = uuid();
    const password = uuid();
    cy.register({ username, password, shouldLogin: true });
  });

  it('Should register an user', () => {
    const username = uuid();
    const password = uuid();
    cy.visit('/');
    cy.get('[data-login-button]').click();
    cy.get('[data-signup-username]').type(username);
    cy.get('[data-signup-password]').type(password);
    cy.get('[data-signup-repeat-password]').type(password);
    cy.get('[data-signup-email]').type(`${username}@omegaup.com`);
    cy.get('[data-signup-submit]').click();
    cy.waitUntil(() => cy.get('.username').should('have.text', username));
  });

  it('Should login an user using the API', () => {
    cy.visit('/');
    const username = 'user';
    const password = 'user';
    cy.login({ username, password });
  });

  it('Should login an user', () => {
    const username = 'user';
    const password = 'user';
    cy.visit('/');
    cy.get('[data-login-button]').click();
    cy.get('[data-login-username]').type(username);
    cy.get('[data-login-password]').type(password);
    cy.get('[data-login-submit]').click();
    cy.waitUntil(() => cy.get('.username').should('have.text', username));
  });
});
