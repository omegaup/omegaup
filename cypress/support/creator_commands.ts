import { CaseOptions } from './types';

Cypress.Commands.add(
  'addCase',
  ({ caseName, groupName, points, autoPoints }: CaseOptions) => {
    cy.get('[name="testcases"]').click();
    cy.get('[data-add-window]').click();

    // Fill form
    cy.get('[name="case-name"]').type(caseName);
    cy.get('[name="case-group"]').select(groupName);
    if (!autoPoints) {
      cy.get('[name="auto-points"]').click({ force: true });
      cy.get('[name="case-points"]').type(`${points}` ?? '0');
    }
    cy.get('button[type="submit"]').click();
  },
);
