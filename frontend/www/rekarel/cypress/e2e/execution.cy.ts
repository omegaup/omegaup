describe('execution spec', () => {
  it('Create new code after run', () => {
    cy.visit('http://localhost:5500/webapp/')

    /* ==== Generated with Cypress Studio ==== */
    cy.get('.cm-editor').click();
    cy.get('.cm-content').type('{ctrl}{home}');
    cy.get('.cm-content').type('{enter}{enter}{enter}{enter}{enter}{enter}{enter}{enter}{enter}{enter}');

    cy.get('#desktopFutureProgram').click();
    cy.get('#navbarDropdownCodeLink').click();
    cy.get('#newJavaCodeNavBtn').click();
    cy.wait(500);
    cy.get('#confirmModalYes').click();
    /* ==== End Cypress Studio ==== */
  })
})