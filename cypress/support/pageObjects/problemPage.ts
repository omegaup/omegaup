import { ProblemOptions, RunOptions } from '../types';
import { v4 as uuid } from 'uuid';

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
    cy.get('[data-problem-keyword-search] input').type(
      problemAlias + '{enter}',
    );
    cy.get('[data-filter-submit-button]').click();

    cy.get('[data-problem-title-list]').first().should('contain', problemAlias);
  }

  openProblem(problemAlias: string): void {
    cy.get(`a[href="/arena/problem/${problemAlias}/"]`).click();
  }

  createRun(runOptions: RunOptions): void {
    cy.get('[data-new-run]').click();
    cy.get('[name="language"]').select(runOptions.language);
    cy.fixture(runOptions.fixturePath).then((fileContent) => {
      cy.get('.CodeMirror-line').first().type(fileContent);
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
    cy.get('a.nav-link[href="#runs"]').click();
    cy.get(`[data-username=${username}]`).should('be.visible');
    cy.get('[data-run-status]').should('contain', 'AC');
  }

  qualifyProblem(tags: string[]): void {
    cy.get('[data-rate-problem-button]').click();
    cy.get('[type="radio"]').check('true');
    tags.forEach((tag) => {
      cy.get('[data-other-tag-input] input').clear().type(tag);
      cy.waitUntil(() =>
        cy
          .get('[data-other-tag-input] .vbt-autcomplete-list a.vbst-item:first')
          .should('have.text', tag)
          .click(),
      );
      cy.get('[data-tag-name]').last().should('contain', tag);
    });
    cy.get('[data-review-submit-button]').click();
  }

  reportProblem(reason: string): void {
    cy.get('[data-report-problem-button]').click();
    cy.get('[name="selectedReason"]').select(reason);
    cy.get('[ data-submit-report-button]').click();
  }

  banProblem(problemAlias: string): void {
    cy.visit('nomination');
    cy.get(`.${problemAlias}`).click();
    cy.get('[data-ban-problem-button]').click();
    cy.get('.modal-footer [data-dismiss="modal"]').first().click();
  }

  verifyBan(problemAlias: string): void {
    cy.get('[data-problem-keyword-search] input').type(
      problemAlias + '{enter}',
    );
    cy.get('[data-filter-submit-button]').click();
    cy.get('[data-problem-title-list]').should('not.exist');
  }

  generateProblemOptions(noOfProblems: number): ProblemOptions[] {
    const problems: ProblemOptions[] = [];

    for (let i = 0; i < noOfProblems; i++) {
      const problemOptions: ProblemOptions = {
        problemAlias: uuid().slice(0, 10),
        tag: 'Recursion',
        autoCompleteTextTag: 'recur',
        problemLevelIndex: 0,
      };

      problems.push(problemOptions);
    }

    return problems;
  }

  verifyProblem(problemOptions: ProblemOptions): void {
    cy.location('href').should('include', problemOptions.problemAlias);
    cy.get('[name="title"]').should('have.value', problemOptions.problemAlias);
    cy.get('[name="problem_alias"]').should(
      'have.value',
      problemOptions.problemAlias,
    );
    cy.get('[name="source"]').should('have.value', problemOptions.problemAlias);
  }

  verifyProblemRun(status: string): void {
    cy.get('[data-run-status] > span').first().should('have.text', 'new');

    cy.intercept({ method: 'POST', url: '/api/run/status/' }).as('runStatus');
    cy.wait(['@runStatus'], { timeout: 10000 });

    cy.get('[data-run-status] > span').first().should('have.text', status);
  }

  downloadProblem(problemAlias: string): void {
    cy.visit(`/problem/${problemAlias}/edit/`);
    cy.get(`[data-tab-download]`).click();

    cy.get('form.form button[type="submit"]').click();
    cy.readFile(`cypress/downloads/${problemAlias}.zip`).should('exist');
  }
}

export const problemPage = new ProblemPage();
