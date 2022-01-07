import { v4 as uuid } from 'uuid';
import { getISODate } from '../support/commands';
import { CourseOptions, LoginOptions, ProblemOptions } from '../support/types';

describe('Basic Commands Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should create a course with unlimited duration', () => {
    const loginOptions: LoginOptions = {
      username: uuid(),
      password: uuid(),
    };

    const courseOptions: CourseOptions = {
      courseAlias: uuid().slice(0, 10),
      showScoreboard: true,
      startDate: new Date(2022, 2, 2),
      unlimitedDuration: true,
      school: 'omegaup',
      basicInformation: false,
      requestParticipantInformation: 'optional',
      problemLevel: 'intermediate',
      objective: 'This is the objective',
      description: 'This is the description',
    };

    cy.register(loginOptions);
    cy.createCourse(courseOptions);

    // Assert
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
      getISODate(courseOptions.startDate ?? new Date()),
    );
    cy.get('[name="unlimited-duration"]')
      .eq(courseOptions.unlimitedDuration ? 0 : 1)
      .should('be.checked');
    cy.get('.tt-input').first().should('have.value', courseOptions.school);
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
  });

  it('Should create a course with end date', () => {
    const loginOptions: LoginOptions = {
      username: uuid(),
      password: uuid(),
    };

    const courseOptions: CourseOptions = {
      courseAlias: uuid().slice(0, 10),
      showScoreboard: true,
      startDate: new Date(2022, 2, 2),
      unlimitedDuration: false,
      endDate: new Date(2022, 3, 3),
      school: 'omegaup',
      basicInformation: false,
      requestParticipantInformation: 'optional',
      problemLevel: 'intermediate',
      objective: 'This is the objective',
      description: 'This is the description',
    };

    cy.register(loginOptions);
    cy.createCourse(courseOptions);

    // Assert
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
      getISODate(courseOptions.startDate ?? new Date()),
    );
    cy.get('[name="unlimited-duration"]')
      .eq(courseOptions.unlimitedDuration ? 0 : 1)
      .should('be.checked');
    cy.get('[name="end-date"]').should(
      'have.value',
      getISODate(courseOptions.endDate ?? new Date()),
    );
    cy.get('.tt-input').first().should('have.value', courseOptions.school); //
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
  });

  it('Should register a user using the API', () => {
    const loginOptions: LoginOptions = {
      username: uuid(),
      password: uuid(),
    };
    cy.register(loginOptions);
    cy.get('header .username').should('have.text', loginOptions.username);
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
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);
    cy.get('header .username').should('have.text', loginOptions.username);
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
    const loginOptions: LoginOptions = {
      username: uuid(),
      password: uuid(),
    };

    const problemOptions: ProblemOptions = {
      problemAlias: uuid().slice(0, 10), // Too large for the alias,
      tag: 'Recursion',
      autoCompleteTextTag: 'recur',
      problemLevelIndex: 0,
    };

    cy.register(loginOptions);
    cy.createProblem(problemOptions);

    // Assert problem has been created
    cy.location('href').should('include', problemOptions.problemAlias); // Url
    cy.get('[name="title"]').should('have.value', problemOptions.problemAlias); // Title
    cy.get('[name="problem_alias"]').should(
      'have.value',
      problemOptions.problemAlias,
    );
    cy.get('[name="source"]').should('have.value', problemOptions.problemAlias);
  });
});
