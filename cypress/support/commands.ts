// https://on.cypress.io/custom-commands
import 'cypress-wait-until';
import 'cypress-file-upload';
import { buildURLQuery } from '@/js/omegaup/ui';
import {
  CourseOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
  Status,
} from './types';

// Logins the user given a username and password
Cypress.Commands.add('login', ({ username, password }: LoginOptions) => {
  const URL =
    '/api/user/login?' + buildURLQuery({ usernameOrEmail: username, password });
  cy.request(URL).then((response) => {
    expect(response.status).to.equal(200);
    cy.reload();
  });
});

// Logins the user as an admin
Cypress.Commands.add('loginAdmin', () => {
  const username = 'omegaup';
  const password = 'omegaup';

  const URL =
    '/api/user/login?' + buildURLQuery({ usernameOrEmail: username, password });
  cy.request(URL).then((response) => {
    expect(response.status).to.equal(200);
    cy.reload();
  });
});

// Logouts the user
Cypress.Commands.add('logout', () => {
  cy.get('a[data-nav-user]').click();
  cy.get('a[data-logout-button]').click();
  cy.waitUntil(() =>
    cy.url().should('eq', 'http://127.0.0.1:8001/'),
  );
});

// Registers and logs in a new user given a username and password.
Cypress.Commands.add('register', ({ username, password }: LoginOptions) => {
  const URL =
    '/api/user/create?' +
    buildURLQuery({ username, password, email: username + '@omegaup.com' });
  cy.request(URL).then((response) => {
    expect(response.status).to.equal(200);
    cy.login({ username, password });
  });
});

Cypress.Commands.add(
  'createProblem',
  ({
    problemAlias,
    tag,
    autoCompleteTextTag,
    problemLevelIndex,
  }: ProblemOptions) => {
    cy.visit('/');
    // Select problem nav
    cy.get('[data-nav-problems]').click();
    cy.get('[data-nav-problems-create]').click();
    // Fill basic problem form
    cy.get('[name="title"]').type(problemAlias).blur();

    // Alias should be the same as title.
    cy.get('[name="problem_alias"]').should('have.value', problemAlias);

    cy.get('[name="source"]').type(problemAlias);
    cy.get('[name="problem_contents"]').attachFile('testproblem.zip');
    cy.get('[data-tags-input]').type(autoCompleteTextTag);

    // Tags panel
    cy.waitUntil(() =>
      cy
        .get('[data-tags-input] .vbt-autcomplete-list a.vbst-item:first')
        .should('have.text', tag) // Maybe theres another way to avoid to hardcode this
        .click(),
    );

    cy.get('[name="problem-level"]').select(problemLevelIndex); // How can we assert this with the real text?

    cy.get('button[type="submit"]').click(); // Submit
  },
);

Cypress.Commands.add(
  'createCourse',
  ({
    courseAlias,
    startDate,
    endDate,
    showScoreboard = false,
    unlimitedDuration = true,
    school = 'Escuela curso',
    basicInformation = false,
    requestParticipantInformation = 'no',
    problemLevel = 'introductory',
    description = 'This is the description',
    objective = 'This is an objective',
  }: Partial<CourseOptions> & Pick<CourseOptions, 'courseAlias'>) => {
    cy.get('[data-nav-courses]').click();
    cy.get('[data-nav-courses-create]').click();
    cy.get('[data-course-new-name]').type(courseAlias);
    cy.get('[data-course-new-alias]').type(courseAlias);
    cy.get('[name="show-scoreboard"]') // Currently the two radios are named equally, thus we need to use the eq, to get the correct index and click it
      .eq(showScoreboard ? 0 : 1)
      .click();
    cy.get('[name="start-date"]').type(getISODate(startDate));
    cy.get('[name="unlimited-duration"]')
      .eq(unlimitedDuration ? 0 : 1)
      .click();
    // only if unlimited duration is false we should change the end date
    if (!unlimitedDuration) {
      cy.get('[name="end-date"]').type(getISODate(endDate));
    } else {
      // the end date input should be disabled
      cy.get('[name="end-date"]').should('be.disabled');
    }
    cy.get('.tags-input input[type="text"]').first().type(school); // If we use the data attribute, the autocomplete makes multiple elements
    cy.get('.typeahead-dropdown li').first().click();
    cy.get('[name="basic-information"]') // Currently the two radios are named equally, thus we need to use the eq, to get the correct index and click it
      .eq(basicInformation ? 0 : 1)
      .click();
    cy.get('[data-course-participant-information]').select(
      requestParticipantInformation,
    );
    cy.get('[data-course-problem-level]').select(problemLevel);
    cy.get('[data-course-objective]').type(objective);
    cy.get('[data-course-new-description]').type(description);
    cy.get('button[type="submit"]').click();
  },
);

Cypress.Commands.add(
  'createRun',
  ({ problemAlias, fixturePath, language }: RunOptions) => {
    cy.visit(`arena/problem/${encodeURIComponent(problemAlias)}/`);
    cy.get('[data-new-run]').click();
    cy.get('[name="language"]').select(language);
    cy.fixture(fixturePath).then((fileContent) => {
      cy.get('.CodeMirror-line').type(fileContent);
      cy.get('[data-submit-run]').click();
    });
  },
);

declare enum ScoreMode {
  AllOrNothing = 'all_or_nothing',
  Partial = 'partial',
  MaxPerGroup = 'max_per_group',
}

Cypress.Commands.add(
  'createContest',
  ({
    contestAlias,
    startDate,
    endDate,
    description = 'Default Description',
    showScoreboard = true,
    scoreBoardVisibleTime = "100",
    scoreMode = ScoreMode.Partial,
    basicInformation = false,
    requestParticipantInformation = 'no',
    differentStart = false,
    differentStartTime = "",
  }) => {
    cy.visit('contest/new/');
    cy.get('[name="title"]').type(contestAlias);
    cy.get('[name="alias"]').type(contestAlias);
    cy.get('[name="description"]').type(description);
    cy.get('[data-start-date]').type(getISODateTime(startDate));
    cy.get('[data-end-date]').type(getISODateTime(endDate));
    cy.get('[data-score-board-visible-time]').clear().type(scoreBoardVisibleTime);
    if (differentStart) {
      cy.get('[data-different-start-check]').click();
      cy.get('[data-different-start-time-input]').type(differentStartTime);
    }
    cy.get('[data-show-scoreboard-at-end]').select(`${showScoreboard}`); // "true" | "false"
    cy.get('[data-score-mode]').select(`${scoreMode}`);
    if (basicInformation) {
      cy.get('[data-basic-information-required]').click();
    }
    cy.get('[data-request-user-information]').select(
      requestParticipantInformation,
    ); // no | optional | required
    cy.get('button[type="submit"]').click();
  },
);

Cypress.Commands.add(
  'addProblemsToContest',
  ({
    contestAlias,
    problems,
  }) => {
    cy.visit(`contest/${contestAlias}/edit/`);
    cy.get('a[data-nav-contest-edit]').click();
    cy.get('a.dropdown-item.problems').click();

    for (const idx in problems) {
      cy.get('.tags-input input[type="text"]').type(problems[idx].problemAlias)
      cy.get('.typeahead-dropdown li').first().click();
      cy.get('.add-problem').click();
    }
  },
);

Cypress.Commands.add(
  'changeAdmissionModeContest',
  ({
    contestAlias,
    admissionMode,
  }) => {
    cy.visit(`contest/${contestAlias}/edit/`);
    cy.get('a[data-nav-contest-edit]').click();
    cy.get('a.dropdown-item.admission-mode').click();
    cy.get('select[name="admission-mode"]').select(
      admissionMode,
    ); // private | registration | public
    cy.get('.change-admission-mode').click();
  },
);

Cypress.Commands.add(
  'enterContest',
  ({
    contestAlias,
  }) => {
    cy.visit(`arena/${contestAlias}`);
    cy.get('button[data-start-contest]').click();
  },
);

Cypress.Commands.add(
  'createRunsInsideContest',
  ({
    contestAlias,
    problems,
    runs,
  }) => {
    const problem = problems[0];
    if (!problem) {
      return;
    }
    cy.visit(`/arena/${contestAlias}/#problems`);
    cy.get(`a[data-problem="${problem.problemAlias}"]`).click();

    for (const idx in runs) {
      // Mocking date just a few seconds after to allow create new run
      cy.clock(new Date(), ['Date']).then((clock) => clock.tick(3000));
      cy.get('[data-new-run]').click();
      cy.get('[name="language"]').select(runs[idx].language);

      // Only the first submission is created because of server validations
      if (!runs[idx].valid) {
        cy.fixture(runs[idx].fixturePath).then((fileContent) => {
          cy.get('.CodeMirror-line').type(fileContent);
          cy.get('[data-submit-run]').should('be.disabled');
        });
        break;
      }

      cy.fixture(runs[idx].fixturePath).then((fileContent) => {
        cy.get('.CodeMirror-line').type(fileContent);
        cy.get('[data-submit-run]').click();
      });

      const expectedStatus: Status = runs[idx].status;
      cy.intercept({ method: 'POST', url: '/api/run/status/' }).as('runStatus');

      cy.wait(['@runStatus'], { timeout: 10000 }).its(
        'response.statusCode',
      ).should('eq', 200);
      cy.get('[data-run-status] > span')
        .first()
        .should('have.text', expectedStatus);
    }
  },
);

/**
 *
 * @param date Date object to convert
 * @returns ISO date required to type on a date input inside cypress
 */
export const getISODate = (date: Date) => {
  return date.toISOString().split('T')[0];
};

/**
 *
 * @param date Date object to convert
 * @returns ISO datetime required to type on a date input inside cypress
 */
export const getISODateTime = (date: Date) => {
  const isoDateTime = new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString();
  return isoDateTime.slice(0, 16);
};

/**
 * Return a date relative to another date
 * @param date original Date object
 * @param days number of days to add to the date
 * @returns Date Relative Date Object
 */
export const addSubtractDaysToDate = (date: Date, { days }: { days: number }): Date => {
  if (days == 0) return date;
  if (days < 0) {
    return new Date(date.getTime() - 24 * 3600 * 1000);
  }

  return new Date(date.getTime() + 24 * 3600 * 1000);
};
