import { LoginOptions } from "../support/types";

describe('Problem creator Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should write and verify the problem statement', () => {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);

    cy.visit('/problem/creator/');
    
    cy.get('[data-problem-creator-tab="statement"]').click();

    cy.get('[data-problem-creator-editor-markdown]').type("Hello omegaUp!");
    cy.get("[data-problem-creator-save-markdown]").click();

    cy.get("[data-problem-creator-previewer-markdown]").should("have.html", "<h1>Previsualización</h1>\n\n<p>Hello omegaUp!</p>")
  });
})