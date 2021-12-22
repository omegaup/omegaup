// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';

import { expect } from 'chai';

// Logins the user given a username and password
Cypress.Commands.add('login', ({ username, password }) => {
  cy.request(
    `/api/user/login?usernameOrEmail=${username}&password=${password}`,
  ).then((response) => {
    expect(response.status).to.eq(200);
    cy.reload();
    cy.get('header .username').should('have.text', username);
  });
});

// Registers and logs in a new user given a username and password.
Cypress.Commands.add('register', ({ username, password }) => {
  cy.request(
    `/api/user/create?username=${username}&password=${password}&email=${username}@omegaup.com`,
  ).then((response) => {
    expect(response.status).to.eq(200);
    cy.login({ username, password });
  });
});
