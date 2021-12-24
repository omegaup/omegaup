// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';

// Logins the user given a username and password
Cypress.Commands.add('login', ({ username, password }) => {
  cy.request(
    `/api/user/login?usernameOrEmail=${username}&password=${password}`,
  ).then((response) => {
    expect(response.status).to.equal(200);
    cy.reload();
  });
});

// Registers and logs in a new user given a username and password.
Cypress.Commands.add('register', ({ username, password }) => {
  cy.request(
    `/api/user/create?username=${username}&password=${password}&email=${username}@omegaup.com`,
  ).then((response) => {
    expect(response.status).to.equal(200);
    cy.login({ username, password });
  });
});
