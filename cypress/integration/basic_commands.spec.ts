import { v4 as uuid } from 'uuid';

describe('Basic Commands Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Should register an user', () => {
    const username = uuid();
    const password = uuid();
    cy.register({ username, password });
  });

  it('Should login an user', () => {
    cy.login({ username: 'omegaup', password: 'omegaup' });
  });
});
