describe('Cron control plane', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  it('Should show the cron jobs dashboard to an admin', () => {
    cy.loginAdmin();
    cy.visit('/admin/crons/');

    cy.get('[data-cron-jobs]').should('be.visible');
    cy.get('[data-cron-jobs]').should('contain', 'update_ranks.py');
    cy.get('[data-cron-jobs]').should('contain', 'assign_badges.py');
    cy.get('[data-cron-jobs]').should('contain', 'aggregate_feedback.py');
    cy.get('[data-cron-runs]').should('exist');

    cy.logout();
  });

  it('Should let an admin request a rerun', () => {
    cy.loginAdmin();
    cy.visit('/admin/crons/');

    cy.intercept('POST', '/api/admin/rerunCron/').as('rerunCron');
    cy.get('[data-cron-rerun]').first().click();
    cy.wait('@rerunCron').its('response.statusCode').should('eq', 200);

    cy.logout();
  });
});
