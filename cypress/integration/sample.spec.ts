describe('Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });
  it('Check if contests exist', () => {
    cy.visit('/');
    cy.get('[data-nav-problems]').click();
    cy.get('[data-nav-problems-collection]').click();
    cy.get('[data-nav-problems-all]').click();
    cy.get('[href="/arena/problem/karel-helloworld/"]').contains(
      'Karel Hello World',
    );
  });
});
