// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';
import { buildURLQuery } from '../../frontend/www/js/omegaup/ui';

// Logins the user given a username and password
Cypress.Commands.add('login', ({ username, password }) => {
  const URL =
    '/api/user/login?' + buildURLQuery({ usernameOrEmail: username, password });
  cy.request(URL).then((response) => {
    expect(response.status).to.equal(200);
    cy.reload();
  });
});

// Registers and logs in a new user given a username and password.
Cypress.Commands.add('register', ({ username, password }) => {
  const URL =
    '/api/user/create?' +
    buildURLQuery({ username, password, email: username + '@omegaup.com' });
  cy.request(URL).then((response) => {
    expect(response.status).to.equal(200);
    cy.login({ username, password });
  });
});
