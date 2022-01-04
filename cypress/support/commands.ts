// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';
import { buildURLQuery } from '@/js/omegaup/ui';

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

Cypress.Commands.add(
  'createProblem',
  ({ problemAlias, tag, autoCompleteTextTag, problemLevelIndex }) => {
    cy.visit('/');
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
        .get('[data-tags-input] .vbt-autcomplete-list a.vbst-item:first')
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
  },
);
