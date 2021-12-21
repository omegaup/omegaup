// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';
import 'cypress-xpath';
import { v4 as uuid } from 'uuid';

// Logins the user given a username and password
Cypress.Commands.add('login', ({ username, password }) => {
  cy.visit('/');
  cy.get('[data-login-btn]').click();
  cy.get('[data-login-username]').type(username);
  cy.get('[data-login-password]').type(password);
  cy.get('[data-login-submit]').click();
  cy.waitUntil(() =>
    cy
      .xpath('//*[@id="__BVID__11___BV_modal_header_"]/button')
      .should('be.visible')
      .click(),
  );
});

// Register a new user given a username and password. If they are not specified, a random one is generated and returned
Cypress.Commands.add('register', ({ username, password }) => {
  cy.visit('/');
  cy.get('[data-login-btn]').click();
  cy.get('[data-signup-username]').type(username);
  cy.get('[data-signup-password]').type(password);
  cy.get('[data-signup-repeat-password]').type(password);
  cy.get('[data-signup-email]').type(`${username}@omegaup.com`);
  cy.get('[data-signup-submit]').click();
  cy.waitUntil(() =>
    cy
      .xpath('//*[@id="__BVID__11___BV_modal_header_"]/button')
      .should('be.visible')
      .click(),
  );
});
