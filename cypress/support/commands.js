// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';
import 'cypress-xpath';

// Example command
Cypress.Commands.add('login', (username, password) => {
  cy.visit('/');
  cy.xpath('//a[@href="/login/?redirect=%2F"]').click();
  cy.xpath('//input[@name="login_username"]').type(username);
  cy.xpath('//input[@name="login_password"]').type(password);
  cy.xpath('//button[@name="login"]').click();
  cy.waitUntil(() => cy.get('#carousel-display').should('be.visible'));
  cy.xpath('//*[@id="__BVID__11___BV_modal_header_"]/button').click(); /// close the modal
});

Cypress.Commands.add('register', (username, password) => {
  cy.visit('/');
  cy.xpath('//a[@href="/login/?redirect=%2F"]').click();
  cy.xpath('//input[@name="reg_username"]').type(username);
  cy.xpath('//input[@name="reg_email"]').type(`${username}@${username}.com`);
  cy.xpath('//input[@name="reg_password"]').type(password);
  cy.xpath('//input[@name="reg_password_confirmation"]').type(password);
  cy.xpath('//button[@name="sign_up"]').click();
  cy.waitUntil(() => cy.get('#carousel-display').should('be.visible'));
  cy.xpath('//*[@id="__BVID__11___BV_modal_header_"]/button').click(); /// close the modal
});
