describe('Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Should create a new problem (Cypress Studio Test)', function () {
    cy.login('user', 'user');
    cy.waitUntil(() =>
      cy.get('.active > .container-lg > .slide > :nth-child(1) > h2'),
    );
    cy.get('.nav-problems > .nav-link').click();
    cy.get('.dropdown-menu > [href="/problem/new/"]').click();
    cy.get(
      ':nth-child(1) > .collapse > :nth-child(1) > :nth-child(1) > .form-control',
    ).clear();
    cy.get(
      ':nth-child(1) > .collapse > :nth-child(1) > :nth-child(1) > .form-control',
    ).type('name');
    cy.get(
      ':nth-child(1) > .collapse > :nth-child(1) > :nth-child(2) > .form-control',
    ).click();
    cy.get('.collapse > :nth-child(2) > :nth-child(1) > .form-control').clear();
    cy.get('.collapse > :nth-child(2) > :nth-child(1) > .form-control').type(
      'name',
    );
    cy.get('input[type="file"]').attachFile(
      '../../frontend/tests/resources/testproblem.zip',
    );
    cy.get(':nth-child(2) > .card-header > .mb-0 > .btn').click();
    cy.get('[required="required"] > .input-group > .form-control').clear();
    cy.get('[required="required"] > .input-group > .form-control').type('rec');
    cy.get('.list-group > :nth-child(1) > :nth-child(1)').click();
    cy.get(
      '.tags > .card > .card-body > .row > .form-group > .form-control',
    ).select('problemLevelBasicIntroductionToProgramming');
    cy.get('.form-group > .btn').click();
  });
});
