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
    cy.get('.username').should('have.text', username);
  });
});

// Register a new user given a username and password. If shouldLogin is true, then the user will be logged in after the registration
Cypress.Commands.add(
  'register',
  ({ username, password, shouldLogin = false }) => {
    cy.request(
      `/api/user/create?username=${username}&password=${password}&email=${username}@omegaup.com`,
    ).then((response) => {
      expect(response.status).to.eq(200);
      if (shouldLogin) cy.login({ username, password });
    });
  },
);
