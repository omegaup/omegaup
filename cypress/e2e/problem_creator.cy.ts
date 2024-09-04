import { LoginOptions } from '../support/types';

describe('Problem creator Test', () => {

  const loginOptions: LoginOptions = {
    username: 'user',
    password: 'user',
  };

  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should write and verify the problem statement', () => {
    cy.login(loginOptions);

    cy.visit('/problem/creator/');

    cy.get('[data-problem-creator-tab="statement"]').click();

    cy.get('[data-problem-creator-editor-markdown]').type('Hello omegaUp!');
    cy.get('[data-problem-creator-save-markdown]').click();

    cy.get('[data-problem-creator-previewer-markdown]').should(
      'have.html',
      '<h1>Previsualización</h1>\n\n<p>Hello omegaUp!</p>',
    );
  });

  it('Should write and verify the problem solution', () => {
    cy.login(loginOptions);

    cy.visit('/problem/creator/');

    cy.get('[data-problem-creator-tab="solution"]').click();

    cy.get('[data-problem-creator-solution-editor-markdown]').type(
      'Hello **solution**!',
    );
    cy.get('[data-problem-creator-solution-save-markdown]').click();

    cy.get('[data-problem-creator-solution-previewer-markdown]').should(
      'have.html',
      '<h1>Previsualización</h1>\n\n<p>Hello <strong>solution</strong>!</p>',
    );
  });

  it('Should upload code and verify the problem code', () => {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);

    cy.visit('/problem/creator/');

    cy.get('[data-problem-creator-tab="code"]').click();

    cy.get('[data-problem-creator-code-input]').attachFile(
      '../fixtures/main.rs',
    );

    // CodeMirror takes some time to load the uploaded code, so we will wait for 2 seconds.
    cy.wait(1000).then(() => {
      cy.get('.CodeMirror-line').then((rawHTMLElements) => {
        const intendedLine = rawHTMLElements[1];
        expect(intendedLine.innerText).to.eq('println!("Hello omegaUp!");');
      });
      cy.get('[data-problem-creator-code-language]')
        .should('have.value', 'rs')
        .find('option:selected')
        .should('contain', 'Rust (1.56.1)');
    });
  });
});
