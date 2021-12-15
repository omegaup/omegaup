// <reference types="cypress"/>

declare namespace Cypress {
  // Includes custom omegaup API error types
  interface Error {
    error: string;
  }

  interface Chainable {
    login(username: string, password: string): void;
  }
}
