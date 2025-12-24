import { v4 as uuid } from 'uuid';
import { coursePage } from '../support/pageObjects/coursePage';
import {
  CourseOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
} from '../support/types';
import { loginPage } from '../support/pageObjects/loginPage';
import { problemPage } from '../support/pageObjects/problemPage';

describe('Course Test Part 1', () => {
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
    cy.window().then((win) => {
      // Force GC if exposed (requires launching Chrome with --js-flags=--expose-gc)
      if (typeof (win as any).gc === 'function') {
        (win as any).gc();
      }
    });
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
    const problemOptions = problemPage.generateProblemOptions(2);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);
    cy.createProblem({ ...problemOptions[1], firstTimeVisited: false });
    coursePage.createCourse(courseOptions);
    coursePage.addStudents(users);
    coursePage.addAssignmentWithProblems(assignmentAlias, shortAlias, [
      problemOptions[0],
    ]);
    cy.logout();

    courseOptions.description = 'Changed Desciption';
    courseOptions.objective = 'New Objective';
    cy.login(loginOptions[0]);
    coursePage.enterCourseAssignmentPage(courseOptions.courseAlias);
    coursePage.editCourse(courseOptions, problemOptions[1].problemAlias);
    cy.logout();

    cy.login(loginOptions[1]);
    coursePage.verifyCourseDetails(courseOptions, problemOptions[0]);
    cy.logout();
  });

  // TODO: Temporarily skipping the test until it becomes more stable.
  it('Should not be able to submit a past assignment', () => {
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
});
