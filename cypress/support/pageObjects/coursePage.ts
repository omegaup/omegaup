import { v4 as uuid } from 'uuid';
import 'cypress-iframe';
import { addSubtractDateTime, getISODate, getISODateTime } from '../commands';
import { CourseOptions, ProblemOptions, RunOptions, Status } from '../types';

export class CoursePage {
  generateCourseOptions(): CourseOptions {
    const now = new Date();
    const courseOptions: CourseOptions = {
      courseAlias: uuid().slice(0, 10),
      showScoreboard: true,
      startDate: now,
      endDate: addSubtractDateTime(now, { days: 1 }),
      unlimitedDuration: false,
      school: 'Escuela curso',
      basicInformation: false,
      requestParticipantInformation: 'optional',
      problemLevel: 'intermediate',
      objective: 'This is the objective',
      description: 'This is the description',
    };

    return courseOptions;
  }

  addStudents(users: Array<string>): void {
    cy.get('[data-course-edit-students]').click();
    cy.get('textarea[data-course-multiple-students-add]').type(
      users.join(', '),
    );
    cy.get('.user-add-bulk').click();

    cy.get('[data-uploaded-students]').then((rawHTMLElements) => {
      const studentsNames: Array<string> = [];
      Cypress.$.makeArray(rawHTMLElements).forEach((element) => {
        cy.task('log', element.innerText);
        studentsNames.push(element.innerText);
      });

      cy.wrap(studentsNames).as('savedStudentsNames');
    });

    cy.get('@savedStudentsNames').should('deep.equal', users);
    cy.get('[data-alert-close]').click();
  }

  addAssignmentWithProblems(
    assignmentAlias: string,
    shortAlias: string,
    problemOptions: ProblemOptions[],
    assignmentType: string = 'now',
  ): void {
    cy.get('[data-course-edit-content]').click();
    cy.get('div[data-content-tab]').should('be.visible');

    cy.get('button[data-course-add-new-content]').click();

    cy.get('.omegaup-course-assignmentdetails').should('be.visible');
    cy.get('[data-course-assignment-name]').type(assignmentAlias);
    cy.get('[data-course-assignment-alias]').type(shortAlias);
    cy.get('[data-course-add-problem]').should('be.visible');
    cy.get('[data-course-assignment-description]').type('Homework Description');

    if (assignmentType == 'past') {
      const now = new Date();
      const startDate = new Date(now.getTime() + 120 * 1000);
      const endDate = new Date(now.getTime() + 240 * 1000);
      cy.get('[data-course-start-date]').type(getISODateTime(startDate));
      cy.get('[data-course-end-date]').type(getISODateTime(endDate));
    }

    if (assignmentType == 'future') {
      const now = new Date();
      const startDate = new Date(now.getTime() + 600 * 1000);
      const endDate = new Date(now.getTime() + 1200 * 1000);
      cy.get('[data-course-start-date]').type(getISODateTime(startDate));
      cy.get('[data-course-end-date]').type(getISODateTime(endDate));
    }
    for (let i = 0; i < problemOptions.length; i++) {
      cy.get('.tags-input input[type="text"]').type(
        problemOptions[i].problemAlias,
      );
      cy.get('.typeahead-dropdown li').first().click();
      cy.get('button[data-add-problem]').click();
      cy.get('[data-course-problemlist] table.table-striped').should(
        'be.visible',
      );
    }
    cy.get('button[data-schedule-assignment]').click();
    cy.get('.omegaup-course-assignmentdetails').should('not.be.visible');
    cy.get('[data-alert-close]').click();
  }

  enterCourse(courseAlias: string, firstTime: boolean = true): void {
    cy.get('a[data-nav-courses]').click();
    cy.get('a[data-nav-courses-all]').click();

    const courseUrl = '/course/' + courseAlias;
    cy.visit(courseUrl);

    if (firstTime) {
      cy.get('button[name="start-course-submit"]').click();
    }
    cy.get('[data-course-start-assignment-button]').click();
    cy.url().should('include', courseAlias);
    cy.url().should('include', '#problems');
  }

  createCourse(courseOptions: CourseOptions): void {
    cy.get('a[data-nav-courses]').should('be.visible').click();
    cy.get('a[data-nav-courses-create]').should('be.visible').click();
    cy.get('.introjs-skipbutton').click();
    cy.get('input[data-course-new-name]')
      .should('be.visible')
      .type(courseOptions.courseAlias);
    cy.get('input[data-course-new-alias]')
      .should('be.visible')
      .type(courseOptions.courseAlias);
    cy.get('input[name="show-scoreboard"][value="true"]')
      .should('be.visible')
      .click();
    if (courseOptions.school != undefined)
      cy.get('.tags-input input[type="text"]')
        .first()
        .type(courseOptions.school);
    cy.get('.typeahead-dropdown li').first().click();
    cy.get('textarea[data-course-new-description]')
      .should('be.visible')
      .type('course description');
    cy.get('form[data-course-form]').submit();
    cy.url().should('include', `/course/${courseOptions.courseAlias}/edit/`);
  }

  makeCoursePublic(): void {
    cy.get('[data-course-edit-admission-mode]').click();
    cy.get('div[data-admission-mode-tab]').should('be.visible');
    cy.get('[name="admission-mode"]').select('public');
    cy.get('form[data-course-admission-mode-form]').submit();
  }

  createInvalidSubmission(
    problemOptions: ProblemOptions,
    runOptions: RunOptions,
  ): void {
    cy.get(`a[data-problem="${problemOptions.problemAlias}"]`).click();
    cy.get('[data-new-run]').click();
    cy.get('[name="language"]').select(runOptions.language);
    cy.fixture(runOptions.fixturePath).then((fileContent) => {
      cy.get('.CodeMirror-line').first().type(fileContent);
      cy.get('[data-submit-run]').click();
    });
    cy.get('.alert-danger').should('be.visible');
  }

  createSubmission(
    problemOptions: ProblemOptions,
    runOptions: RunOptions,
  ): void {
    cy.get(`a[data-problem="${problemOptions.problemAlias}"]`).click();
    cy.waitUntil(() => cy.get('[data-new-run] a').should('be.visible'));
    cy.get('[data-new-run] a').click();
    cy.get('[name="language"]').select(runOptions.language);
    cy.fixture(runOptions.fixturePath).then((fileContent) => {
      cy.get('.CodeMirror-line').first().type(fileContent);
      cy.get('[data-submit-run]').click();
    });
    const expectedStatus: Status = runOptions.status;
    cy.intercept({ method: 'POST', url: '/api/run/status/' }).as('runStatus');

    cy.wait(['@runStatus'], { timeout: 10000 })
      .its('response.statusCode')
      .should('eq', 200);
    cy.get('[data-run-status] > span')
      .first({ timeout: 10000 })
      .should('have.text', expectedStatus);
  }

  closePopup(problemOptions: ProblemOptions): void {
    cy.reload();
    cy.get(`a[data-problem="${problemOptions.problemAlias}"]`).click();
    cy.get('[data-dificulty-radio-button]').last().click();
    cy.get('[data-submit-feedback-button]').click();
    cy.get('[data-overlay-popup] button.close')
      .should('be.visible')
      .first()
      .click({ force: true });
  }

  enterCourseAssignmentPage(courseAlias: string): void {
    const courseUrl = `/course/${courseAlias}/`;
    cy.visit(courseUrl);
  }

  createClarification(problemAlias: string, question: string): void {
    cy.get('a[href="#clarifications"]').click();
    cy.waitUntil(() =>
      cy.get('[data-tab-clarifications]').should('be.visible'),
    );

    cy.get('a[data-new-clarification-button]').click();
    cy.get('[data-new-clarification-problem]').select(problemAlias);
    cy.get('[data-new-clarification-message]')
      .should('be.visible')
      .type(question);

    cy.get('[data-new-clarification]').submit();
    cy.get('[data-form-clarification-message]').should('have.text', question);
  }

  answerClarification(answer: string): void {
    cy.get('[data-course-homework-button]').click();
    cy.get('a[href="#clarifications"]').click();
    cy.get('[data-tab-clarifications]').should('be.visible');
    cy.get('[data-select-answer]').select(answer);
    cy.get('[data-form-clarification-answer]').submit();
    cy.get('[data-form-clarification-resolved-answer]').should(
      'contain',
      answer,
    );
  }

  verifyCalrification(answer: string): void {
    cy.get('a[href="#clarifications"]').click();
    cy.get('[data-tab-clarifications]').should('be.visible');
    cy.get('[data-clarification-answer-text]').should('contain', answer);
  }

  leaveFeedbackOnSolution(feedbacks: { line: number; text: string }[]): void {
    cy.get('[data-course-submisson-button]').click();
    cy.get('[data-runs-actions-button]').click();
    cy.get('[data-runs-show-details-button]').click();

    cy.get('.CodeMirror', { timeout: 20000 })
      .should('exist')
      .then((editor) => {
        cy.wrap(editor).should('be.visible');

        feedbacks.forEach(({ line, text }) => {
          const gutterLines = editor.find(
            '.CodeMirror-gutter-wrapper .CodeMirror-linenumber',
          );

          // Ensure we have enough gutters
          expect(gutterLines.length).to.be.greaterThan(line);

          const gutter = gutterLines[line];
          cy.wrap(gutter).scrollIntoView();
          cy.wrap(gutter).click({ force: true });

          cy.waitUntil(() =>
            cy.get('.card-body > textarea').should('be.visible'),
          );

          cy.get('.card-body > textarea').type(text);

          cy.get('[data-button-submit]').click();
          cy.get('.card-body [data-markdown-statement]').should(
            'contain',
            text,
          );
        });

        cy.get('[data-button-send-feedback]').click();

        cy.get('[data-runs-actions-button]').click();
        cy.get('[data-runs-show-details-button]').click();

        feedbacks.forEach(({ text }) => {
          cy.get('.CodeMirror-linewidget [data-markdown-statement]').should(
            'contain',
            text,
          );
        });
        cy.get('[data-overlay-popup] button.close')
          .should('be.visible')
          .first()
          .click({ force: true });
      });
  }

  verifyFeedback({
    feedback,
    problemAlias,
    courseAlias,
  }: {
    feedback: string;
    problemAlias: string;
    courseAlias: string;
  }): void {
    coursePage.enterCourse(courseAlias, false);
    cy.get(`a[data-problem="${problemAlias}"]`).click();
    cy.get('button[data-run-details]').click();
    cy.get('.CodeMirror-linewidget [data-markdown-statement]').should(
      'contain',
      feedback,
    );
    cy.get('.CodeMirror-line', { timeout: 20000 }).should('be.visible');
    cy.get('.CodeMirror-line').then((rawHTMLElements) => {
      const userCode: Array<string> = [];
      Cypress.$.makeArray(rawHTMLElements).forEach((element) => {
        cy.task('log', element.innerText);
        userCode.push(element.innerText);
      });

      cy.wrap(userCode).as('userCodeLines');
    });
    cy.get('@userCodeLines').should('have.length.above', 11);
    cy.get('[data-overlay-popup] button.close')
      .should('be.visible')
      .first()
      .click({ force: true });
  }

  editCourse(courseOptions: CourseOptions): void {
    const editContestUrl = `/course/${courseOptions.courseAlias}/edit/`;
    cy.visit(editContestUrl);
    if (courseOptions.description != undefined) {
      cy.get('textarea[data-course-new-description]')
        .should('be.visible')
        .clear()
        .type(courseOptions.description);
    }
    if (courseOptions.objective != undefined) {
      cy.get('textarea[data-course-objective]')
        .should('be.visible')
        .clear()
        .type(courseOptions.objective);
    }
    cy.get('form[data-course-form]').submit();
    cy.get('[data-alert-close]').click();
    cy.get('[data-course-edit-content]').click();
    cy.get('div[data-content-tab]').should('be.visible');
    cy.get('[data-course-edit-content-button]').click();
    cy.get('.omegaup-course-assignmentdetails').should('be.visible');
    const now = new Date();
    cy.get('[data-course-start-date]').type(getISODateTime(now));
    cy.get('.tags-input input[type="text"]').type('Sumas');
    cy.get('.typeahead-dropdown li').first().click();
    cy.wait(3000);
    cy.get('button[data-add-problem]').click();
    cy.get('[data-course-problemlist] table.table-striped').should(
      'be.visible',
    );
    cy.get('button[data-schedule-assignment]').click();
  }

  verifyCourseDetails(
    courseOptions: CourseOptions,
    problemOptions: ProblemOptions,
  ): void {
    const courseUrl = `/course/${courseOptions.courseAlias}/`;
    cy.visit(courseUrl);
    cy.get('button[name="start-course-submit"]').click();
    cy.get('a[href="#information"]').click();
    cy.get('[data-markdown-statement]').should(
      'contain',
      courseOptions.description,
    );
    cy.get('a[href="#content"]').click();
    cy.get('[data-course-start-assignment-button]').click();
    cy.get('a[data-problem="sumas"]').should('be.visible');
    cy.get(`a[data-problem="${problemOptions.problemAlias}"]`).should(
      'be.visible',
    );
  }

  toggleScoreboardFilter(users: string[], userNames: string[]) {
    for (let i = 0; i < users.length; i++) {
      cy.get('[data-scoreboard-options]').select('1');
      cy.get(`.${users[i]} > [data-table-scoreboard-username]`).should(
        'contain',
        userNames[i],
      );
      cy.get(`.${users[i]} > [data-table-scoreboard-username]`).should(
        'not.contain',
        users[i],
      );
      cy.get('[data-scoreboard-options]').select('2');
      cy.get(`.${users[i]} > [data-table-scoreboard-username]`).should(
        'not.contain',
        userNames[i],
      );
      cy.get(`.${users[i]} > [data-table-scoreboard-username]`).should(
        'contain',
        users[i],
      );
      cy.get('[data-scoreboard-options]').select('3');
      cy.get(`.${users[i]} > [data-table-scoreboard-username]`).should(
        'contain',
        userNames[i],
      );
      cy.get(`.${users[i]} > [data-table-scoreboard-username]`).should(
        'contain',
        users[i],
      );
    }
  }

  verifySubmissionsFilterUsingNames(users: string[]): void {
    cy.get('[data-search-username]').type(users[0]);
    cy.get('.tags-input-typeahead-item-highlighted-default').click();
    cy.get(`td[data-username=${users[0]}]`).should('be.visible');
    cy.get(`td[data-username=${users[1]}]`).should('not.exist');

    cy.get('[data-remove-all-filters]').click();
    cy.get(`td[data-username=${users[0]}]`).should('be.visible');
    cy.get(`td[data-username=${users[1]}]`).should('be.visible');

    cy.get('[data-search-username]').type(users[1]);
    cy.get('.tags-input-typeahead-item-highlighted-default').click();
    cy.get(`td[data-username=${users[1]}]`).should('be.visible');
    cy.get(`td[data-username=${users[0]}]`).should('not.exist');
    cy.get('[data-remove-all-filters]').click();

    cy.get(`a[title=${users[0]}]`).first().click();
    cy.get(`td[data-username=${users[0]}]`).should('be.visible');
    cy.get(`td[data-username=${users[1]}]`).should('not.exist');
    cy.get('[data-remove-all-filters]').click();
  }

  verifySubmissionsFilterUsingProblems(problems: string[]): void {
    cy.get('[data-search-problem]').type(problems[0]);
    cy.get('.tags-input-typeahead-item-highlighted-default').click();
    cy.get(`a[href="/arena/problem/${problems[0]}/"]`).should('be.visible');
    cy.get(`a[href="/arena/problem/${problems[1]}/"]`).should('not.exist');

    cy.get('[data-remove-all-filters]').click();
    cy.get(`a[href="/arena/problem/${problems[0]}/"]`).should('be.visible');
    cy.get(`a[href="/arena/problem/${problems[1]}/"]`).should('be.visible');

    cy.get('[data-search-problem]').type(problems[1]);
    cy.get('.tags-input-typeahead-item-highlighted-default').click();
    cy.get(`a[href="/arena/problem/${problems[1]}/"]`).should('be.visible');
    cy.get(`a[href="/arena/problem/${problems[0]}/]"`).should('not.exist');
  }

  verifyCourseOptions(courseOptions: CourseOptions): void {
    cy.location('href').should('include', courseOptions.courseAlias); // Url
    cy.get('[data-course-name]').contains(courseOptions.courseAlias);
    cy.get('[data-tab-course').click();
    cy.get('[data-course-new-name]').should(
      'have.value',
      courseOptions.courseAlias,
    );
    cy.get('[data-course-new-alias]').should(
      'have.value',
      courseOptions.courseAlias,
    );
    cy.get('[name="show-scoreboard"]')
      .eq(courseOptions.showScoreboard ? 0 : 1)
      .should('be.checked');
    cy.get('[name="start-date"]').should(
      'have.value',
      getISODate(courseOptions.startDate),
    );
    cy.get('[name="unlimited-duration"]')
      .eq(courseOptions.unlimitedDuration ? 0 : 1)
      .should('be.checked');
    cy.get('.tags-input').should('have.text', courseOptions.school);
    cy.get('[name="basic-information"]')
      .eq(courseOptions.basicInformation ? 0 : 1)
      .should('be.checked');
    cy.get('[data-course-participant-information]').should(
      'have.value',
      courseOptions.requestParticipantInformation,
    );
    cy.get('[data-course-problem-level]').should(
      'have.value',
      courseOptions.problemLevel,
    );
    cy.get('[data-course-objective]').should(
      'have.value',
      courseOptions.objective,
    );
    cy.get('[data-course-new-description]').should(
      'have.value',
      courseOptions.description,
    );
  }
}

export const coursePage = new CoursePage();
