import { v4 as uuid } from 'uuid';
import { coursePage } from '../support/pageObjects/coursePage';
import {
  CourseOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
} from '../support/types';
import { loginPage } from '../support/pageObjects/loginPage';

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
    problemOptions = {
      problemAlias: uuid().slice(0, 10),
      tag: 'Recursión',
      autoCompleteTextTag: 'recur',
      problemLevelIndex: 0,
    };

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(assignmentAlias, problemOptions, true);
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
    const problemOptions: ProblemOptions = {
      problemAlias: uuid().slice(0, 10),
      tag: 'Recursión',
      autoCompleteTextTag: 'recur',
      problemLevelIndex: 0,
    };
    const runOptions: RunOptions = {
      problemAlias: problemOptions.problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(assignmentAlias, problemOptions);
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions, runOptions);
    coursePage.closePopup(problemOptions);
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
    coursePage.createSubmission(problemOptions, runOptions);
    coursePage.closePopup(problemOptions);
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
    coursePage.createSubmission(problemOptions, runOptions);
    coursePage.closePopup(problemOptions);
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
    const problemOptions: ProblemOptions = {
      problemAlias: uuid().slice(0, 10),
      tag: 'Recursión',
      autoCompleteTextTag: 'recur',
      problemLevelIndex: 0,
    };

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(assignmentAlias, problemOptions);
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createClarification(
      problemOptions.problemAlias,
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
    const problemOptions: ProblemOptions = {
      problemAlias: uuid().slice(0, 10),
      tag: 'Recursión',
      autoCompleteTextTag: 'recur',
      problemLevelIndex: 0,
    };
    const runOptions: RunOptions = {
      problemAlias: problemOptions.problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[1]);
    cy.createProblem(problemOptions);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(assignmentAlias, problemOptions);
    cy.logout();

    cy.login(loginOptions[0]);
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions, runOptions);
    coursePage.closePopup(problemOptions);
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
    const problemOptions: ProblemOptions = {
      problemAlias: uuid().slice(0, 10),
      tag: 'Recursión',
      autoCompleteTextTag: 'recur',
      problemLevelIndex: 0,
    };

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(assignmentAlias, problemOptions);
    cy.logout();

    courseOptions.description = 'Changed Desciption';
    courseOptions.objective = 'New Objective';
    cy.login(loginOptions[0]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    coursePage.editCourse(courseOptions);
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.verifyCourseDetails(courseOptions, problemOptions);
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
    const loginOptions = loginPage.registerMultipleUsers(2);
    const users = [loginOptions[1].username];
    const courseOptions = coursePage.generateCourseOptions();
    const assignmentAlias = 'ut_rank_hw_' + uuid();
    const problemOptions: ProblemOptions = {
      problemAlias: uuid().slice(0, 10),
      tag: 'Recursión',
      autoCompleteTextTag: 'recur',
      problemLevelIndex: 0,
    };
    const runOptions: RunOptions = {
      problemAlias: problemOptions.problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp11-gcc',
      valid: true,
      status: 'AC',
    };

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions);
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblem(assignmentAlias, problemOptions);
    cy.logout();

    cy.login(loginOptions[1]);
    loginPage.addUsername('User 1');
    coursePage.enterCourse(courseOptions.courseAlias);
    coursePage.createSubmission(problemOptions, runOptions);
    coursePage.closePopup(problemOptions);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get(`.${loginOptions[1].username} > td:nth-child(4)`).should(
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
    coursePage.toggleScoreboardFilter(users[0], 'User 1');
    cy.logout();
  });
});
