// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';

Cypress.Commands.add('login', (username, password) => {
  cy.visit('/');
  cy.get('.navbar-right > .nav-item > .nav-link').click();
  cy.get('.form-horizontal > :nth-child(1) > .form-control').clear();
  cy.get('.form-horizontal > :nth-child(1) > .form-control').type(username);
  cy.get(':nth-child(2) > .form-control').clear();
  cy.get(':nth-child(2) > .form-control').type(password);
  cy.get(':nth-child(3) > .btn').click();
});
