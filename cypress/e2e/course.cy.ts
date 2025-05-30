import { v4 as uuid } from 'uuid';
import { coursePage } from '../support/pageObjects/coursePage';
import {
  CourseOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
  LinkTestCase,
} from '../support/types';
import { loginPage } from '../support/pageObjects/loginPage';
import { profilePage } from '../support/pageObjects/profilePage';
import { problemPage } from '../support/pageObjects/problemPage';

describe('Course Test', () => {
  let loginOptions: LoginOptions[] = [];
  let courseOptions: CourseOptions;
  let problemOptions: ProblemOptions[];

  before(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');

    loginOptions = loginPage.registerMultipleUsers(2);
    courseOptions = coursePage.generateCourseOptions();

    const users = [loginOptions[0].username];
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    problemOptions = problemPage.generateProblemOptions(1);

    // We are creating an course with assignment that end after two minutes
    // so that it can be used as a past assignment which will be tested later
    // on in the test.
    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
      'past',
    );
    cy.logout();
  });

  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });
  it('Should create a course and add students to it as participants make submits to problems', () => {
    const loginOptions = loginPage.registerMultipleUsers(4);
    const users: Array<string> = [];
    loginOptions.forEach((loginDetails) => {
      users.push(loginDetails.username);
    });
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(1);
    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 3);
    cy.get(`.${loginOptions[0].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[2]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 3);
    cy.get(`.${loginOptions[2].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[3]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 3);
    cy.get(`.${loginOptions[3].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    cy.get('[data-course-scoreboard-button]').click();
    cy.get(`.${loginOptions[0].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.get(`.${loginOptions[2].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.get(`.${loginOptions[3].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();
  });
  it('Should create a course, add and answer a clarification', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const users = [loginOptions[0].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(1);

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createClarification(
      problemOptions[0].problemAlias,
      'This is question',
    );
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    coursePage.answerClarification('No');
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias, false);
    coursePage.verifyCalrification('No');
    cy.logout();
  });

  it('Should create a course and leave feedback for the submission', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const users = [loginOptions[0].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(1);
    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    const suggestions: { line: number; text: string }[] = [
      { line: 1, text: 'Solution is not optimal' },
      { line: 3, text: 'This code could be improved' },
      { line: 5, text: 'This line could be removed' },
    ];
    coursePage.leaveFeedbackOnSolution(suggestions);
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.verifyFeedback({
      feedback: suggestions[0].text,
      problemAlias: runOptions.problemAlias,
      courseAlias: courseOptions.courseAlias,
    });
    cy.logout();
  });

  it('Should create a course and edit the assignment', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const users = [loginOptions[1].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(1);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
    );
    cy.logout();

    courseOptions.description = 'Changed Desciption';
    courseOptions.objective = 'New Objective';
    cy.login(loginOptions[0]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    coursePage.editCourse(courseOptions);
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.verifyCourseDetails(courseOptions, problemOptions[0]);
    cy.logout();
  });

  // TODO: Temporarily skipping the test until it becomes more stable.
  it.skip('Should not be able to submit a past assignment', () => {
    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createInvalidSubmission(problemOptions[0], runOptions);
    cy.logout();
  });

  it('Should test the working of filters on scoreboard page', () => {
    const loginOptions = loginPage.registerMultipleUsers(3);
    const users = [loginOptions[1].username, loginOptions[2].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(1);

    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
    );
    cy.logout();

    cy.login(loginOptions[1]);
    profilePage.addUsername('User 1');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[1].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[2]);
    profilePage.addUsername('User 2');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[2].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    cy.get('[data-course-scoreboard-button]').click();
    cy.get(`.${loginOptions[1].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.get(`.${loginOptions[2].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    coursePage.toggleScoreboardFilter(users, ['User 1', 'User 2']);
    cy.logout();
  });

  it('Should test the working of filter on submissions page', () => {
    const loginOptions = loginPage.registerMultipleUsers(3);
    const users = [loginOptions[1].username, loginOptions[2].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_1' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(2);
    const problems = [
      problemOptions[0].problemAlias,
      problemOptions[1].problemAlias,
    ];
    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    cy.createProblem({ ...problemOptions[1], firstTimeVisited: false });
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
    );
    cy.logout();

    cy.login(loginOptions[1]);
    profilePage.addUsername('User 2');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.createSubmission(problemOptions[1], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[1].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[2]);
    profilePage.addUsername('User 2');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.createSubmission(problemOptions[1], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[2].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    cy.get('[data-course-submisson-button]').click();
    coursePage.verifySubmissionsFilterUsingNames(users);
    coursePage.verifySubmissionsFilterUsingProblems(problems);
    cy.logout();
  });

  it('Should test student is not able to access a future assignment', () => {
    const loginOptions = loginPage.registerMultipleUsers(3);
    const users = [loginOptions[1].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(1);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
      'future',
    );
    cy.logout();

    cy.login(loginOptions[1]);
    const courseUrl = '/course/' + courseOptions.courseAlias;
    cy.visit(courseUrl);
    cy.get('button[name="start-course-submit"]').click();
    cy.get('[data-course-start-assignment-button]').should('not.exist');
    const assignmentUrl = courseUrl + '/assignment/' + shortAlias + '#problems';
    cy.request({ url: assignmentUrl, failOnStatusCode: false }).then((resp) => {
      expect(resp.status).to.eq(200);
    });
    cy.visit('/');
    cy.logout();
  });

  it('Should verify progress of students in the course', () => {
    const loginOptions = loginPage.registerMultipleUsers(4);
    const users: Array<string> = [];
    loginOptions.forEach((loginDetails) => {
      users.push(loginDetails.username);
    });
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias1 = 'ut_rank_hw_1' + uuid();
    const shortAlias1 = assignmentAlias1.slice(0, 12);
    const assignmentAlias2 = 'ut_rank_hw_2' + uuid();
    const shortAlias2 = assignmentAlias2.slice(0, 12);
    const problemOptions1 = problemPage.generateProblemOptions(1);
    const problemOptions2 = problemPage.generateProblemOptions(1);
    const courseUrl = '/course/' + courseOptions.courseAlias;
    const studentsProgressUrl =
      '/course/' + courseOptions.courseAlias + '/students/';
    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions1[0]);
    cy.createProblem({ ...problemOptions2[0], firstTimeVisited: false });
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(
      assignmentAlias1,
      shortAlias1,
      problemOptions1,
    );
    coursePage.addAssignmentWithProblems(
      assignmentAlias2,
      shortAlias2,
      problemOptions2,
    );
    cy.logout();

    // 100% Course completion
    cy.login(loginOptions[1]);
    cy.visit(courseUrl);
    cy.get('button[name="start-course-submit"]').click();
    cy.get('[data-course-start-assignment-button]').first().click();
    coursePage.createSubmission(problemOptions1[0], runOptions);
    cy.visit(courseUrl);
    cy.get('[data-course-start-assignment-button]').last().click();
    coursePage.createSubmission(problemOptions2[0], runOptions);
    cy.logout();

    // 50% Course completion
    cy.login(loginOptions[2]);
    cy.visit(courseUrl);
    cy.get('button[name="start-course-submit"]').click();
    cy.get('[data-course-start-assignment-button]').first().click();
    coursePage.createSubmission(problemOptions1[0], runOptions);
    cy.logout();

    cy.login(loginOptions[0]);
    cy.visit(studentsProgressUrl);
    cy.get(`.${loginOptions[1].username} > [data-global-score]`).should(
      'contain',
      '100%',
    );
    cy.get(`.${loginOptions[2].username} > [data-global-score]`).should(
      'contain',
      '50%',
    );
    cy.get(`.${loginOptions[3].username} > [data-global-score]`).should(
      'contain',
      '0%',
    );
    cy.get('[data-scorecard-csv-download-button]').click();
    cy.wait(3000);
    cy.get('[data-scorecard-csv-download-button]')
      .should('have.attr', 'download')
      .then((download) => {
        const filename = 'cypress/downloads/' + download;
        cy.readFile(filename).should('exist');
      });
    cy.logout();
  });

  it('Should create a public course and share the link to allow joining it', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const courseOptions = coursePage.generateCourseOptions();
    const courseAlias = courseOptions.courseAlias;
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = problemPage.generateProblemOptions(1);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.makeCoursePublic();
    coursePage.addAssignmentWithProblems(
      assignmentAlias,
      shortAlias,
      problemOptions,
    );
    cy.logout();

    cy.login(loginOptions[1]);
    cy.get('a[data-nav-courses]').click();
    cy.get('a[data-nav-courses-all]').click();
    cy.get('.introjs-skipbutton').click();
    const courseUrl = '/course/' + courseAlias;
    cy.get(`div>a[href="${courseUrl}"]`, { timeout: 0 }).should('not.exist');
    cy.visit(courseUrl);
    cy.waitUntil(() => cy.url().should('include', courseUrl));
    cy.get('button[name=start-course-submit]').click();
  });
});

const testCases: LinkTestCase[] = [
  {
    url: '/',
    links: [
      'https://blog.omegaup.com',
      'https://blog.omegaup.com/policies/codigo-de-conducta-en-omegaup/',
      'https://blog.omegaup.com/policies/privacy-policy/',
    ],
  },
];

describe('External Blog Link Validation On Home Page ', () => {
  testCases.forEach((testCase) => {
    it(`should find and validate external blog links on page: ${testCase.url}`, () => {
      cy.visit(testCase.url);

      testCase.links.forEach((linkUrl) => {
        cy.get(`a[href="${linkUrl}"]`).should('exist');
        cy.request({
          url: linkUrl,
          failOnStatusCode: false,
        }).then((response) => {
          expect(response.status).to.be.lessThan(400);
        });
      });
    });
  });
});
