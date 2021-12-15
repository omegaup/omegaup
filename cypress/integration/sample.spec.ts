describe('Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Should go to the landing page', () => {
    cy.visit('/');
  });
});
