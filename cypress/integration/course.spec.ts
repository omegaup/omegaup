import { v4 as uuid } from 'uuid';
import { coursePage } from '../support/pageObjects/coursePage';
import {
  CourseOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
} from '../support/types';
import { loginPage } from '../support/pageObjects/loginPage';
import { contestPage } from '../support/pageObjects/contestPage';

describe('Course Test', () => {
  let loginOptions: LoginOptions[] = [];
  let courseOptions: CourseOptions;
  let problemOptions: ProblemOptions;

  before(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');

    loginOptions = loginPage.registerMultipleUsers(2);
    courseOptions = coursePage.generateCourseOptions();

    const users = [loginOptions[0].username];
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    problemOptions = contestPage.generateProblemOptions(1)[0];

    // We are creating an course with assignment that end after two minutes
    // so that it can be used as a past assignment which will be tested later
    // on in the test.
    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(
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
    const problemOptions = contestPage.generateProblemOptions(1);
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
    coursePage.addAssignmentWithProblem(
      assignmentAlias,
      shortAlias,
      problemOptions[0],
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 3);
    cy.get(`.${loginOptions[0].username} > td:nth-child(4)`).should(
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
    cy.get(`.${loginOptions[2].username} > td:nth-child(4)`).should(
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
    cy.get(`.${loginOptions[3].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    cy.get('[data-course-scoreboard-button]').click();
    cy.get(`.${loginOptions[0].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.get(`.${loginOptions[2].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.get(`.${loginOptions[3].username} > td:nth-child(4)`).should(
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
    const problemOptions = contestPage.generateProblemOptions(1);

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(
      assignmentAlias,
      shortAlias,
      problemOptions[0],
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
    const problemOptions = contestPage.generateProblemOptions(1);
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
    coursePage.addAssignmentWithProblem(
      assignmentAlias,
      shortAlias,
      problemOptions[0],
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    coursePage.leaveFeedbackOnSolution('Solution is not optimal');
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.verifyFeedback('Solution is not optimal');
    cy.logout();
  });

  it('Should create a course and edit the assignment', () => {
    const loginOptions = loginPage.registerMultipleUsers(2);
    const users = [loginOptions[1].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = contestPage.generateProblemOptions(1);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(
      assignmentAlias,
      shortAlias,
      problemOptions[0],
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

  it('Should not be able to submit a past assignment', () => {
    const runOptions: RunOptions = {
      problemAlias: problemOptions.problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createInvalidSubmission(problemOptions, runOptions);
    cy.logout();
  });

  it('Should test the working of filters on scoreboard page', () => {
    const loginOptions = loginPage.registerMultipleUsers(3);
    const users = [loginOptions[1].username, loginOptions[2].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = contestPage.generateProblemOptions(1);
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
    coursePage.addAssignmentWithProblem(
      assignmentAlias,
      shortAlias,
      problemOptions[0],
    );
    cy.logout();

    cy.login(loginOptions[1]);
    loginPage.addUsername('User 1');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[1].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[2]);
    loginPage.addUsername('User 2');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[2].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    cy.get('[data-course-scoreboard-button]').click();
    cy.get(`.${loginOptions[1].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.get(`.${loginOptions[2].username} > td:nth-child(4)`).should(
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
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = contestPage.generateProblemOptions(1);
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
    coursePage.addAssignmentWithProblem(
      assignmentAlias,
      shortAlias,
      problemOptions[0],
    );
    cy.logout();

    cy.login(loginOptions[1]);
    loginPage.addUsername('User 1');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[1].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[2]);
    loginPage.addUsername('User 2');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions[0], runOptions);
    coursePage.closePopup(problemOptions[0]);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${loginOptions[2].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    cy.pause();
    cy.get('[data-course-submisson-button]').click();
    coursePage.verifySubmissionsFilter(users);
    cy.logout();
  });

  it('Should test student is not able to access a future assignment', () => {
    const loginOptions = loginPage.registerMultipleUsers(3);
    const users = [loginOptions[1].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const shortAlias = assignmentAlias.slice(0, 12);
    const problemOptions = contestPage.generateProblemOptions(1);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(
      assignmentAlias,
      shortAlias,
      problemOptions[0],
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
      expect(resp.status).to.eq(404);
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
    const problemOptions = contestPage.generateProblemOptions(2);
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
    cy.createProblem(problemOptions[0]);
    cy.createProblem(problemOptions[1]);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(
      assignmentAlias1,
      shortAlias1,
      problemOptions[0],
    );
    coursePage.addAssignmentWithProblem(
      assignmentAlias2,
      shortAlias2,
      problemOptions[1],
    );
    cy.logout();

    // 100% Course completion
    cy.login(loginOptions[1]);
    cy.visit(courseUrl);
    cy.get('button[name="start-course-submit"]').click();
    cy.get('[data-course-start-assignment-button]').first().click();
    coursePage.createSubmission(problemOptions[0], runOptions);
    cy.visit(courseUrl);
    cy.get('[data-course-start-assignment-button]').last().click();
    coursePage.createSubmission(problemOptions[1], runOptions);
    cy.logout();

    // 50% Course completion
    cy.login(loginOptions[2]);
    cy.visit(courseUrl);
    cy.get('button[name="start-course-submit"]').click();
    cy.get('[data-course-start-assignment-button]').first().click();
    coursePage.createSubmission(problemOptions[0], runOptions);
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
});
