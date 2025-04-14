import { LoginOptions } from '../support/types';
import T from '../../frontend/www/js/omegaup/lang';
import { problemCreatorPage } from '../support/pageObjects/problemCreatorPage';
import { profilePage } from '../support/pageObjects/profilePage';

describe('Problem creator Test', () => {
  const loginOptions: LoginOptions = {
    username: 'user',
    password: 'user',
  };

<<<<<<< HEAD
  // Define test modes
  const testModes = [
    {
      name: 'Authenticated User',
      isAuthenticated: true,
      setup: () => {
        cy.login(loginOptions);
        profilePage.updatePreferredLanguage('es');
      },
      visit: () => {
        problemCreatorPage.visit();
      },
    },
    {
      name: 'Unauthenticated User',
      isAuthenticated: false,
      setup: () => {},
      visit: () => {
        problemCreatorPage.visit('es');
      },
    },
  ];

  before(() => {
    cy.visit('/');
    cy.setCookie('has-visited-problem-creator', true.toString());
    cy.setCookie('has-visited-cases-tab', true.toString());
    cy.setCookie('has-visited-code-tab', true.toString());
    cy.setCookie('has-visited-solution-tab', true.toString());
  });

=======
>>>>>>> d99df7660 (added the test files back)
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
<<<<<<< HEAD

    cy.setCookie('has-visited-problem-creator', true.toString());
    cy.setCookie('has-visited-cases-tab', true.toString());
    cy.setCookie('has-visited-code-tab', true.toString());
    cy.setCookie('has-visited-solution-tab', true.toString());
  });

  testModes.forEach((mode) => {
    describe(`${mode.name} Mode`, () => {
      beforeEach(() => {
        mode.setup();
      });

      it(`Should write and verify the problem statement - ${mode.name}`, () => {
        mode.visit();
        cy.get('[data-problem-creator-tab="statement"]').click();
        cy.get('[data-problem-creator-editor-markdown]').type('Hello omegaUp!');
        cy.get('[data-problem-creator-save-markdown]').click();
        cy.get('[data-problem-creator-previewer-markdown]').should(
          'have.html',
          '<h1>Previsualización</h1>\n\n<p>Hello omegaUp!</p>',
        );
      });

      it(`Should write and verify the problem solution - ${mode.name}`, () => {
        mode.visit();
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

      it(`Should upload and verify the problem code - ${mode.name}`, () => {
        mode.visit();
        cy.get('[data-problem-creator-tab="code"]').click();
        cy.get('[data-problem-creator-code-input]').attachFile(
          '../fixtures/main.rs',
        );
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

      it(`Should add groups, cases and multiple cases - ${mode.name}`, () => {
        mode.visit();
        cy.get('[data-problem-creator-tab="cases"]').click();
        cy.get('[data-add-window]').click();
        cy.get('[data-problem-creator-add-panel-tab="case"]').click();
        cy.get('[data-problem-creator-case-input="name"]').type('Hello case');
        cy.get('[data-problem-creator-case-input="name"]').should(
          'have.value',
          'hellocase',
        );
        cy.get('[data-problem-creator-add-panel-submit]').click();
        cy.get('[data-problem-creator-tab="cases"]').click();
        cy.get('[data-add-window]').click();
        cy.get('[data-problem-creator-add-panel-tab="group"]').click();
        cy.get('[data-problem-creator-group-input="name"]').type(
          'Hello group!',
        );
        cy.get('[data-problem-creator-group-input="name"]').should(
          'have.value',
          'hellogroup',
        );
        cy.get('[data-problem-creator-add-panel-submit]').click();
        cy.get('[data-add-window]').click();
        cy.get('[data-problem-creator-add-panel-tab="multiple-cases"]').click();
        cy.get('[data-problem-creator-multiple-cases-input="prefix"]').type(
          'hello',
        );
        cy.get('[data-problem-creator-multiple-cases-input="suffix"]').type(
          'there',
        );
        cy.get('[data-problem-creator-multiple-cases-input="count"]').clear();
        cy.get('[data-problem-creator-multiple-cases-input="count"]').type(
          '10',
        );
        cy.get('[data-problem-creator-add-panel-submit]').click();
        cy.get('[data-sidebar-ungrouped-cases="count"]')
          .invoke('text')
          .then((text) => {
            expect(text.trim()).contain('11');
          });
        cy.get('[data-sidebar-groups="grouped"]').should('have.length', 1);
        cy.get('[data-sidebar-groups="count"]')
          .invoke('text')
          .then((text) => {
            expect(text.trim()).contain('0');
          });
      });

      it(`Should add and edit layouts - ${mode.name}`, () => {
        mode.visit();
        cy.get('[data-problem-creator-tab="cases"]').click();

        // Add initial case
        cy.get('[data-add-window]').click();
        cy.get('[data-problem-creator-add-panel-tab="case"]').click();
        cy.get('[data-problem-creator-case-input="name"]').type('Hello case');
        cy.get('[data-problem-creator-add-panel-submit]').click();

        cy.get('[data-sidebar-groups="ungrouped"]').click();
        cy.get('[data-sidebar-cases-ungrouped]').first().click();

        const caseTypes = [
          { type: 'multiline', text: T.problemCreatorLineMultiline },
          { type: 'array', text: T.problemCreatorLineArray },
          { type: 'matrix', text: T.problemCreatorLineMatrix },
          { type: 'line', text: T.problemCreatorLineLine },
        ];

        caseTypes.forEach(() => {
          cy.get('[data-edit-case-add-line]').click();
        });

        problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
          lineCases.forEach((lineCase) => {
            cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`).click();
            cy.get(
              `[data-array-modal-dropdown-kind="${lineCase.id}-${lineCase.type}"]`,
            ).click();
            problemCreatorPage.fillInformationForCaseType(lineCase);
          });
        });

        // Create layout from selected case
        cy.get('[data-toggle-layout-sidebar]').click();
        cy.get('[data-add-layout-from-selected-case]').click();
        cy.get('[data-close-layout-sidebar]').click();

        // Add another case
        cy.get('[data-add-window]').click();
        cy.get('[data-problem-creator-add-panel-tab="case"]').click();
        cy.get('[data-problem-creator-case-input="name"]').type('Hello case 2');
        cy.get('[data-problem-creator-add-panel-submit]').click();

        // Enforce layout to all cases
        cy.get('[data-toggle-layout-sidebar]').click();

        cy.contains('[data-layout-dropdown]', 'hellocase_hellocase')
          .as('targetLayout')
          .find('button.dropdown-toggle-split')
          .click();

        cy.get('@targetLayout')
          .find('[data-layout-dropdown-enforce-to-all]')
          .should('be.visible')
          .click({ force: true });

        cy.get('[data-close-layout-sidebar]').click();

        // Assert layout applied to second case
        cy.get('[data-sidebar-cases-ungrouped]').last().click();
        problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
          lineCases.forEach((lineCase) => {
            cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
              .invoke('text')
              .should('contain', lineCase.text);
          });
        });

        // Copy the layout
        cy.get('[data-toggle-layout-sidebar]').click();

        cy.contains('[data-layout-dropdown]', 'hellocase_hellocase')
          .as('originalLayout')
          .find('button.dropdown-toggle-split')
          .click();

        cy.get('@originalLayout')
          .find('[data-layout-dropdown-copy]')
          .should('be.visible')
          .click({ force: true });

        // Apply copied layout to first case
        cy.contains('[data-layout-dropdown]', 'hellocase_hellocase copia')
          .as('copiedLayout')
          .find('button.dropdown-toggle-split')
          .click();

        cy.get('@copiedLayout')
          .find('[data-layout-dropdown-enforce-to-selected]')
          .should('be.visible')
          .click({ force: true });

        // Assert layout still applied to first case
        cy.get('[data-sidebar-cases-ungrouped]').first().click();
        problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
          lineCases.forEach((lineCase) => {
            cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
              .invoke('text')
              .should('contain', lineCase.text);
          });
        });

        // Assert copied layout applied correctly to last case
        cy.get('[data-sidebar-cases-ungrouped]').last().click();
        const caseTypesUpdated = [
          { type: 'multiline', text: T.problemCreatorLineArray },
          { type: 'array', text: T.problemCreatorLineArray },
          { type: 'matrix', text: T.problemCreatorLineMatrix },
          { type: 'line', text: T.problemCreatorLineLine },
        ];
        problemCreatorPage.getLineIDs(caseTypesUpdated).then((lineCases) => {
          lineCases.forEach((lineCase) => {
            cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
              .invoke('text')
              .should('contain', lineCase.text);
          });
        });
=======
    cy.login(loginOptions);
    profilePage.updatePreferredLanguage('es');
    cy.logout();
  });

  it('Should write and verify the problem statement', () => {
    cy.login(loginOptions);

    problemCreatorPage.visit();

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

    problemCreatorPage.visit();

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

    problemCreatorPage.visit();

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

    problemCreatorPage.visit();

    cy.get('[data-problem-creator-tab="cases"]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="case"]').click();

    cy.get('[data-problem-creator-case-input="name"]').type('Hello case');
    cy.get('[data-problem-creator-case-input="name"]').should(
      'have.value',
      'hellocase',
    );

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-problem-creator-tab="cases"]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="group"]').click();

    cy.get('[data-problem-creator-group-input="name"]').type('Hello group!');
    cy.get('[data-problem-creator-group-input="name"]').should(
      'have.value',
      'hellogroup',
    );

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="multiple-cases"]').click();

    cy.get('[data-problem-creator-multiple-cases-input="prefix"]').type(
      'hello',
    );
    cy.get('[data-problem-creator-multiple-cases-input="suffix"]').type(
      'there',
    );
    cy.get('[data-problem-creator-multiple-cases-input="count"]').clear();
    cy.get('[data-problem-creator-multiple-cases-input="count"]').type('10');

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-sidebar-ungrouped-cases="count"]')
      .invoke('text')
      .then((text) => {
        expect(text.trim()).contain('11');
      });

    cy.get('[data-sidebar-groups="grouped"]').should('have.length', 1);

    cy.get('[data-sidebar-groups="count"]')
      .invoke('text')
      .then((text) => {
        expect(text.trim()).contain('0');
      });
  });

  it('Should add and edit layouts', () => {
    cy.login(loginOptions);

    problemCreatorPage.visit();

    cy.get('[data-problem-creator-tab="cases"]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="case"]').click();

    cy.get('[data-problem-creator-case-input="name"]').type('Hello case');
    cy.get('[data-problem-creator-case-input="name"]').should(
      'have.value',
      'hellocase',
    );

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-sidebar-groups="ungrouped"]').click();

    cy.get('[data-sidebar-cases-ungrouped]').first().click();

    const caseTypes = [
      { type: 'multiline', text: T.problemCreatorLineMultiline },
      { type: 'array', text: T.problemCreatorLineArray },
      { type: 'matrix', text: T.problemCreatorLineMatrix },
      { type: 'line', text: T.problemCreatorLineLine },
    ];

    caseTypes.forEach(() => {
      cy.get('[data-edit-case-add-line]').click();
    });

    problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
      lineCases.forEach((lineCase) => {
        cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`).click();
        cy.get(
          `[data-array-modal-dropdown-kind="${lineCase.id}-${lineCase.type}"]`,
        ).click();

        problemCreatorPage.fillInformationForCaseType(lineCase);
      });
    });

    cy.get('[data-toggle-layout-sidebar]').click();

    cy.get('[data-add-layout-from-selected-case]').click();

    cy.get('[data-close-layout-sidebar]').click();

    cy.get('[data-add-window]').click();

    cy.get('[data-problem-creator-add-panel-tab="case"]').click();

    cy.get('[data-problem-creator-case-input="name"]').type('Hello case 2');

    cy.get('[data-problem-creator-add-panel-submit]').click();

    cy.get('[data-toggle-layout-sidebar]').click();

    cy.get('[data-layout-dropdown]>button.dropdown-toggle-split').click();
    cy.get('[data-layout-dropdown-enforce-to-all]').click();

    cy.get('[data-toggle-layout-sidebar]').click();

    cy.get('[data-sidebar-cases-ungrouped]').last().click();

    problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
      lineCases.forEach((lineCase) => {
        cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
          .find('button.dropdown-toggle')
          .should('contain.text', lineCase.text);
      });
    });

    cy.get('[data-toggle-layout-sidebar]').click();

    cy.get('[data-layout-dropdown]>button.dropdown-toggle-split').click();
    cy.get('[data-layout-dropdown-copy]').click();

    cy.get('[data-layout-dropdown]').eq(1).click();

    cy.get('[data-line-info-dropdown]').eq(4).click();
    cy.get('[data-line-info-dropdown-item="array"]').eq(4).click();

    cy.get('button.dropdown-toggle-split').eq(3).click();
    cy.get('[data-layout-dropdown-enforce-to-selected]').eq(1).click();

    cy.get('[data-sidebar-cases-ungrouped]').first().click();

    problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
      lineCases.forEach((lineCase) => {
        cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
          .find('button.dropdown-toggle')
          .should('contain.text', lineCase.text);
      });
    });

    cy.get('[data-sidebar-cases-ungrouped]').last().click();

    const caseTypesUpdated = [
      { type: 'multiline', text: T.problemCreatorLineArray },
      { type: 'array', text: T.problemCreatorLineArray },
      { type: 'matrix', text: T.problemCreatorLineMatrix },
      { type: 'line', text: T.problemCreatorLineLine },
    ];

    problemCreatorPage.getLineIDs(caseTypesUpdated).then((lineCases) => {
      lineCases.forEach((lineCase) => {
        cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
          .find('button.dropdown-toggle')
          .should('contain.text', lineCase.text);
>>>>>>> d99df7660 (added the test files back)
      });
    });
  });
});
