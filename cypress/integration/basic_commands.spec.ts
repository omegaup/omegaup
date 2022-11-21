import { v4 as uuid } from 'uuid';
import {
  addSubtractDaysToDate,
  getISODate,
  getISODateTime,
} from '../support/commands';
import {
  ContestOptions,
  CourseOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
  Status,
} from '../support/types';

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
      startDate: new Date(),
      unlimitedDuration: true,
      school: 'Escuela curso',
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
  });

  it('Should create a course with end date', () => {
    const loginOptions: LoginOptions = {
      username: uuid(),
      password: uuid(),
    };

    cy.clock(new Date(2022, 2, 31, 22, 19, 0), ['Date']);

    const now = new Date();

    const courseOptions: CourseOptions = {
      courseAlias: uuid().slice(0, 10),
      showScoreboard: true,
      startDate: now,
      endDate: addSubtractDaysToDate(now, {days: 1}),
      unlimitedDuration: false,
      school: 'Escuela curso',
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
      getISODate(courseOptions.startDate),
    );
    cy.get('[name="unlimited-duration"]')
      .eq(courseOptions.unlimitedDuration ? 0 : 1)
      .should('be.checked');
    if (courseOptions.endDate) {
      cy.get('[name="end-date"]').should(
        'have.value',
        getISODate(courseOptions.endDate),
      );
    }
    cy.get('.tags-input').should('have.text', courseOptions.school); //
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
    now.setFullYear(now.getFullYear() - 18);
    cy.get('[data-signup-birthdate]').type(getISODate(now));
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

  it('Should make a run of a problem', () => {
    const problemOptions: ProblemOptions = {
      problemAlias: 'problem-' + uuid().slice(0, 8),
      tag: 'Recursion',
      autoCompleteTextTag: 'Recur',
      problemLevelIndex: 1,
    };

    const runOptions: RunOptions = {
      problemAlias: problemOptions.problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
    };

    const expectedStatus: Status = 'AC';

    cy.login({ username: 'user', password: 'user' });
    cy.createProblem(problemOptions);
    cy.createRun(runOptions);
    cy.get('[data-run-status] > span').first().should('have.text', 'new');

    cy.intercept({ method: 'POST', url: '/api/run/status/' }).as('runStatus');
    cy.wait(['@runStatus'], { timeout: 10000 });

    cy.get('[data-run-status] > span')
      .first()
      .should('have.text', expectedStatus);
  });

  const now = new Date();

  enum ScoreMode {
    AllOrNothing = 'all_or_nothing',
    Partial = 'partial',
    MaxPerGroup = 'max_per_group',
  }

  const problemAlias = 'problem-' + uuid().slice(0, 8);
  const contestOptions: ContestOptions = {
    contestAlias: 'contest' + uuid().slice(0, 5),
    description: 'Test Description',
    startDate: addSubtractDaysToDate(now, {days: -1}),
    endDate: addSubtractDaysToDate(now, {days: 2}),
    showScoreboard: true,
    basicInformation: false,
    scoreMode: ScoreMode.Partial,
    requestParticipantInformation: 'no',
    admissionMode: 'public',
    problems: [
      {
        problemAlias: 'sumas',
        tag: 'Recursion',
        autoCompleteTextTag: 'Recur',
        problemLevelIndex: 1,
      },
    ],
    runs: [
      {
        problemAlias: 'sumas',
        fixturePath: 'main.cpp',
        language: 'cpp11-gcc',
        valid: true,
      },
      {
        problemAlias: 'sumas',
        fixturePath: 'main.cpp',
        language: 'cpp11-gcc',
        valid: false,
      },
    ]
  };
  const loginOptions: LoginOptions = {
    username: 'omegaup',
    password: 'omegaup',
  };

  it('Should create a contest', () => {
    cy.login(loginOptions);

    cy.createContest(contestOptions);
    cy.location('href').should('include', contestOptions.contestAlias); // Url

    // Assert
    cy.get('[name="title"]').should('have.value', contestOptions.contestAlias);
    cy.get('[name="alias"]').should('have.value', contestOptions.contestAlias);
    cy.get('[name="description"]').should(
      'have.value',
      contestOptions.description,
    );
    cy.get('[data-start-date]').should(
      'have.value',
      getISODateTime(contestOptions.startDate),
    );
    cy.get('[data-end-date]').type(
      getISODateTime(contestOptions.endDate),
    );
    cy.get('[data-show-scoreboard-at-end]').should(
      'have.value',
      `${contestOptions.showScoreboard}`,
    );
    cy.get('[data-score-mode]').should(
      'have.value',
      `${contestOptions.scoreMode}`,
    );
    cy.get('[data-basic-information-required]').should(
      contestOptions.basicInformation ? 'be.checked' : 'not.be.checked',
    );
    cy.get('[data-request-user-information]').should(
      'have.value',
      contestOptions.requestParticipantInformation,
    );
  });

  it('Should create runs inside contest', () => {
    cy.login(loginOptions);
    cy.addProblemsToContest(contestOptions);
    cy.changeAdmissionModeContest(contestOptions);

    cy.get('a[href="/logout/"]:last').click();
    cy.waitUntil(() =>
      cy.url().should('eq', 'http://127.0.0.1:8001/'),
    );

    // Mocking date 2 hours before to test timeRemote is working correctly.
    cy.clock(new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate(),
      now.getHours() - 2,
      now.getMinutes(),
      now.getSeconds(),
    ), ['Date']);
    cy.get('[data-login-button]').click();
    cy.get('[data-login-username]').type('user');
    cy.get('[data-login-password]').type('user');
    cy.get('[data-login-submit]').click();
    cy.waitUntil(() =>
      cy.get('header .username').should('have.text', 'user'),
    );

    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
  });
});
