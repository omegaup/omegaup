// <reference types="cypress"/>

declare namespace Cypress {
  // Includes custom omegaup API error types
  interface Error {
    error: string;
  }

  interface Chainable {
    login(loginInfo: LoginInfo): void;
    register(loginInfo: LoginInfo): void;
    createProblem(problemInfo: ProblemInfo): void;
  }
}

interface LoginInfo {
  username: string;
  password: string;
}

interface ProblemInfo {
  problemAlias: string;
  tag: string;
  autoCompleteTextTag: string;
  problemLevelIndex: number;
}
