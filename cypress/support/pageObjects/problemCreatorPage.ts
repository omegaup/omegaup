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

  visit(lang?: string): void {
    cy.get('[data-nav-problems]').click();
    if (!lang) {
      cy.get('[data-nav-problems-create-options]').click();
    }
    cy.get('a[href="/problem/creator/"]').click();

    // Check if we're on the correct page
    cy.location('pathname', { timeout: 10000 }).should(
      'eq',
      '/problem/creator/',
    );

    // If a language is specified and not already in URL, add it
    cy.location('search').then((search) => {
      if (!search.includes('lang') && lang) {
        cy.visit(`/problem/creator/?lang=${lang}`);
      }
    });
  }
}

export const problemCreatorPage = new ProblemCreatorPage();
