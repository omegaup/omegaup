import { RunOptions } from "../types";

export class ProblemPage {
  navigateToAllProblemsTab(): void {
    cy.get('[data-nav-problems]').click();
    cy.get('[data-nav-problems-collection]').click();
    cy.url().should('contain', '/problem/collection');
    cy.get('[data-nav-problems-all]').click();
  }

  verifyLanguageFilter(language: string): void {
    cy.get('[data-filter-language]').select(language);
    cy.get('[data-filter-submit-button]').click();
  }

  verifyFilterByAlias(problemAlias: string): void {
    cy.get('[data-problem-keyword-search] input').type(problemAlias + '{enter}');
    cy.get('[data-filter-submit-button]').click();

    cy.get('[data-problem-title-list]').first().should('contain', problemAlias);
  }

  verifyFilterByTags(): void {

  }

  openProblem(problemAlias: string): void {
    cy.get(`a[href="/arena/problem/${problemAlias}/"]`).click();
  }

  createRun(runOptions: RunOptions): void {
    cy.get('[data-new-run]').click();
    cy.get('[name="language"]').select(runOptions.language);
    cy.fixture(runOptions.fixturePath).then((fileContent) => {
      cy.get('.CodeMirror-line').type(fileContent);
      cy.get('[data-submit-run]').click();
    });

    cy.get('[data-run-status] > span').first().should('have.text', 'new');

    cy.intercept({ method: 'POST', url: '/api/run/status/' }).as('runStatus');
    cy.wait(['@runStatus'], { timeout: 10000 });

    cy.get('[data-run-status] > span')
      .first()
      .should('have.text', runOptions.status);
  }

  verifySubmission(username: string): void {
    cy.get('a[href="#runs"]').click();
    cy.get(`[data-username=${username}]`).should('be.visible');
    cy.get('[data-run-status]').should('contain', 'AC');
  }
}

export const problemPage = new ProblemPage();
