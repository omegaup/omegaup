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

  it('Should upload and verify the problem code', () => {
    cy.login(loginOptions);

    cy.visit('/problem/creator/');

    cy.get('[data-problem-creator-tab="code"]').click();

    cy.get('[data-problem-creator-code-input]').attachFile(
      '../fixtures/main.rs',
    );

    // CodeMirror takes some time to load the uploaded code, so we will wait for 1 second.
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

  it('Should add groups, cases and multiple cases', () => {
    cy.login(loginOptions);

    cy.visit('/problem/creator/');

    cy.get('[data-problem-creator-tab="cases"]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="case"]').click();

    cy.get('[data-problem-creator-case-input="name"]').type("Hello case");
    cy.get('[data-problem-creator-case-input="name"]').should(
      'have.value',
      'hellocase',
    );

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-problem-creator-tab="cases"]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="group"]').click();

    cy.get('[data-problem-creator-group-input="name"]').type("Hello group!");
    cy.get('[data-problem-creator-group-input="name"]').should(
      'have.value',
      'hellogroup',
    );

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="multiple-cases"]').click();

    cy.get('[data-problem-creator-multiple-cases-input="prefix"]').type("hello");
    cy.get('[data-problem-creator-multiple-cases-input="suffix"]').type("there");
    cy.get('[data-problem-creator-multiple-cases-input="count"]').clear();
    cy.get('[data-problem-creator-multiple-cases-input="count"]').type('10');

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-sidebar-ungrouped-cases="count"]').invoke('text').then((text) => {
        expect(text.trim()).contain('11')
    });

    cy.get('[data-sidebar-groups="grouped"]').should('have.length', 1);

    cy.get('[data-sidebar-groups="count"]').invoke('text').then((text) => {
      expect(text.trim()).contain('0')
  });

  });
});
