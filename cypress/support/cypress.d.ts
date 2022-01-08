// <reference types="cypress"/>

import { CourseOptions, LoginOptions, ProblemOptions } from './types';

declare global {
  namespace Cypress {
    // Includes custom omegaup API error types
    interface Error {
      error: string;
    }

    interface Chainable {
      login(loginOptions: LoginOptions): void;
      register(loginOptions: LoginOptions): void;
      createProblem(problemOptions: ProblemOptions): void;
      createCourse(
        courseOptions: Partial<CourseOptions> &
          Pick<CourseOptions, 'courseAlias'>,
      ): void;
    }
  }
}
