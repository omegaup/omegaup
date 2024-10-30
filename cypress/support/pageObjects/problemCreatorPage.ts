export class ProblemCreatorPage {
  getLineIDs(
    casesTypes: { type: string; text: string }[],
  ): Cypress.Chainable<{ id: string; text: string; type: string }[]> {
    const lineIDs: Cypress.Chainable<(string | null)[]> = cy
      .get('[data-array-modal-dropdown]')
      .then((elements) => {
        const values = Cypress._.map(elements, (el) =>
          el.getAttribute('data-array-modal-dropdown'),
        );
        return cy.wrap(values);
      });

    return lineIDs.then((ids) => {
      return ids.map((id, index) => {
        return {
          type: casesTypes[index].type,
          text: casesTypes[index].text,
          id: id || '',
        };
      });
    });
  }

  fillInformationForCaseType({ type, id }: { type: string; id: string }): void {
    // Only for array and matrix types we can generate random data
    if (type === 'line' || type === 'multiline') {
      return;
    }
    cy.get(`[data-line-edit-button="${id}"]`).click();
    cy.get(`[data-${type}-modal-generate]`).click();
    cy.get('button[class="btn btn-success"]').eq(0).click();
  }

  visit(): void {
    cy.get('[data-nav-problems]').click();
    cy.get('[data-nav-problems-create-options]').click();
    cy.get('a[href="/problem/creator/"]').click();
  }
}

export const problemCreatorPage = new ProblemCreatorPage();
