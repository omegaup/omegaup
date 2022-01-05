import { v4 as uuid } from 'uuid';
import { parseDateToCypressString } from '../support/commands';

describe('Basic Commands Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should create a course with unlimited duration', () => {
    const username = uuid();
    const password = uuid();

    const courseAlias = uuid().slice(0, 10);
    const showScoreboard = true;
    const startDate = new Date(2022, 2, 2);
    const unlimitedDuration = true;
    const school = 'omegaup';
    const basicInformation = false;
    const requestParticipantInformation: 'no' | 'optional' | 'required' =
      'optional';
    const problemLevel: 'introductory' | 'intermediate' | 'advanced' =
      'intermediate';
    const objective = 'This is the objective';
    const description = 'This is the description';

    cy.register({ username, password });
    cy.createCourse({
      courseAlias,
      showScoreboard,
      startDate,
      unlimitedDuration,
      school,
      basicInformation,
      requestParticipantInformation,
      problemLevel,
      objective,
      description,
    });

    // Assert
    cy.location('href').should('include', courseAlias); // Url
    cy.get('[data-course-name]').contains(courseAlias);
    cy.get('[data-tab-course').click();
    cy.get('[data-course-new-name]').should('have.value', courseAlias);
    cy.get('[data-course-new-alias]').should('have.value', courseAlias);
    cy.get('[name="show-scoreboard"]')
      .eq(showScoreboard ? 0 : 1)
      .should('be.checked');
    cy.get('[name="start-date"]').should(
      'have.value',
      parseDateToCypressString(startDate),
    );
    cy.get('[name="unlimited-duration"]')
      .eq(unlimitedDuration ? 0 : 1)
      .should('be.checked');
    cy.get('.tt-input').first().should('have.value', school);
    cy.get('[name="basic-information"]')
      .eq(basicInformation ? 0 : 1)
      .should('be.checked');
    cy.get('[data-course-participant-information]').should(
      'have.value',
      requestParticipantInformation,
    );
    cy.get('[data-course-problem-level]').should('have.value', problemLevel);
    cy.get('[data-course-objective]').should('have.value', objective);
    cy.get('[data-course-new-description]').should('have.value', description);
  });

  it('Should create a course with end date', () => {
    const username = uuid();
    const password = uuid();

    const courseAlias = uuid().slice(0, 10);
    const showScoreboard = true;
    const startDate = new Date(2022, 2, 2);
    const unlimitedDuration = false;
    const endDate = new Date(2022, 3, 3);
    const school = 'omegaup';
    const basicInformation = false;
    const requestParticipantInformation: 'no' | 'optional' | 'required' =
      'optional';
    const problemLevel: 'introductory' | 'intermediate' | 'advanced' =
      'intermediate';
    const objective = 'This is the objective';
    const description = 'This is the description';

    cy.register({ username, password });
    cy.createCourse({
      courseAlias,
      showScoreboard,
      startDate,
      unlimitedDuration,
      endDate,
      school,
      basicInformation,
      requestParticipantInformation,
      problemLevel,
      objective,
      description,
    });

    // Assert
    cy.location('href').should('include', courseAlias); // Url
    cy.get('[data-course-name]').contains(courseAlias);
    cy.get('[data-tab-course').click();
    cy.get('[data-course-new-name]').should('have.value', courseAlias);
    cy.get('[data-course-new-alias]').should('have.value', courseAlias);
    cy.get('[name="show-scoreboard"]')
      .eq(showScoreboard ? 0 : 1)
      .should('be.checked');
    cy.get('[name="start-date"]').should(
      'have.value',
      parseDateToCypressString(startDate),
    );
    cy.get('[name="unlimited-duration"]')
      .eq(unlimitedDuration ? 0 : 1)
      .should('be.checked');
    cy.get('[name="end-date"]').should(
      'have.value',
      parseDateToCypressString(endDate),
    );
    cy.get('.tt-input').first().should('have.value', school); //
    cy.get('[name="basic-information"]')
      .eq(basicInformation ? 0 : 1)
      .should('be.checked');
    cy.get('[data-course-participant-information]').should(
      'have.value',
      requestParticipantInformation,
    );
    cy.get('[data-course-problem-level]').should('have.value', problemLevel);
    cy.get('[data-course-objective]').should('have.value', objective);
    cy.get('[data-course-new-description]').should('have.value', description);
  });

  it('Should register a user using the API', () => {
    const username = uuid();
    const password = uuid();
    cy.register({ username, password });
    cy.get('header .username').should('have.text', username);
  });

  it('Should register a user', () => {
    const username = uuid();
    const password = uuid();
    cy.get('[data-login-button]').click();
    cy.get('[data-signup-username]').type(username);
    cy.get('[data-signup-password]').type(password);
    cy.get('[data-signup-repeat-password]').type(password);
    cy.get('[data-signup-email]').type(`${username}@omegaup.com`);
    cy.get('[data-signup-submit]').click();
    cy.waitUntil(() =>
      cy.get('header .username').should('have.text', username),
    );
  });

  it('Should login a user using the API', () => {
    const username = 'user';
    const password = 'user';
    cy.login({ username, password });
    cy.get('header .username').should('have.text', username);
  });

  it('Should login a user', () => {
    const username = 'user';
    const password = 'user';
    cy.get('[data-login-button]').click();
    cy.get('[data-login-username]').type(username);
    cy.get('[data-login-password]').type(password);
    cy.get('[data-login-submit]').click();
    cy.waitUntil(() =>
      cy.get('header .username').should('have.text', username),
    );
  });

  it('Should create a problem', () => {
    const username = uuid();
    const password = uuid();
    const problemAlias = uuid().slice(0, 10); // Too large for the alias
    const tag = 'Recursion';
    const autoCompleteTextTag = 'recur';
    const problemLevelIndex = 0;

    cy.register({ username, password });
    cy.createProblem({
      problemAlias,
      tag,
      autoCompleteTextTag,
      problemLevelIndex,
    });

    // Assert problem has been created
    cy.location('href').should('include', problemAlias); // Url
    cy.get('[name="title"]').should('have.value', problemAlias); // Title
    cy.get('[name="problem_alias"]').should('have.value', problemAlias);
    cy.get('[name="source"]').should('have.value', problemAlias);
  });
});
