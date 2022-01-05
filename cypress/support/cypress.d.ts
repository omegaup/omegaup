// <reference types="cypress"/>

import { CourseInfo, LoginInfo, ProblemInfo } from './types';

declare global {
  namespace Cypress {
    // Includes custom omegaup API error types
    interface Error {
      error: string;
    }

    interface Chainable {
      login(loginInfo: LoginInfo): void;
      register(loginInfo: LoginInfo): void;
      createProblem(problemInfo: ProblemInfo): void;
      createCourse(courseInfo: CourseInfo): void;
    }
  }
}
