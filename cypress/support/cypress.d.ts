// <reference types="cypress"/>

import {
  ContestOptions,
  CourseOptions,
  GroupOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
} from './types';

declare global {
  namespace Cypress {
    // Includes custom omegaup API error types
    interface Error {
      error: string;
    }

    interface Chainable {
      login(loginOptions: LoginOptions): void;
      logout(): void;
      logoutUsingApi(): void;
      loginAdmin(): void;
      register(loginOptions: LoginOptions): void;
      createProblem(problemOptions: ProblemOptions): void;
      createCourse(
        courseOptions: Partial<CourseOptions> &
          Pick<CourseOptions, 'courseAlias'>,
      ): void;
      createRun(problemOptions: RunOptions): void;
      createContest(
        contestOptions: Partial<ContestOptions> &
          Pick<ContestOptions, 'contestAlias'>,
        shouldShowIntro: boolean,
      ): void;
      addProblemsToContest(contestOptions: ContestOptions): void;
      changeAdmissionModeContest(contestOptions: ContestOptions): void;
      enterContest(contestOptions: ContestOptions): void;
      createRunsInsideContest(contestOptions: ContestOptions): void;
      createGroup(groupOptions: GroupOptions): string;
      addIdentitiesGroup(groupAlias: string): Array<string>;
    }
  }
}
