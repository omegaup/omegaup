// <reference types="cypress"/>

declare namespace Cypress {
  // Includes custom omegaup API error types
  interface Error {
    error: string;
  }

  interface Chainable {
    login(loginInfo: LoginInfo): void;
    register(loginInfo: LoginInfo & { shouldLogin: boolean }): void;
  }
}

interface LoginInfo {
  username: string;
  password: string;
}
