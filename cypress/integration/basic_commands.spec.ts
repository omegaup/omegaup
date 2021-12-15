import { v4 as uuid } from 'uuid';

describe('Basic Commands Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Should register an user', () => {
    cy.register(uuid(), uuid());
  });

  it('Should login an user', () => {
    cy.login('omegaup', 'omegaup');
  });
});
