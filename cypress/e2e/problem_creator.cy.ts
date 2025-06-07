import { LoginOptions } from '../support/types';
import T from '../../frontend/www/js/omegaup/lang';
import { problemCreatorPage } from '../support/pageObjects/problemCreatorPage';
import { profilePage } from '../support/pageObjects/profilePage';

describe('Problem Creator Tests', () => {
  const loginOptions: LoginOptions = {
    username: 'user',
    password: 'user',
  };

  function openLayoutDropdownAndClick(selector: string) {
    cy.get('[data-layout-dropdown]').first().within(() => {
      cy.get('button.dropdown-toggle-split').click();
      cy.get(selector).click({ force: true }); // ensure click even if dropdown menu has display:none initially
    });
  }

  const runTests = (isAuthenticated: boolean) => {
    beforeEach(() => {
      cy.clearCookies();
      cy.clearLocalStorage();
      cy.visit('/');

      // Disable Intro.js overlays
      cy.setCookie('has-visited-problem-creator', 'true');
      cy.setCookie('has-visited-cases-tab', 'true');
      cy.setCookie('has-visited-code-tab', 'true');
      cy.setCookie('has-visited-solution-tab', 'true');

      cy.window().then((win) => {
        win.localStorage.setItem('has-visited-problem-creator', 'true');
        win.localStorage.setItem('has-visited-cases-tab', 'true');
        win.localStorage.setItem('has-visited-code-tab', 'true');
        win.localStorage.setItem('has-visited-solution-tab', 'true');
      });

      if (isAuthenticated) {
        cy.login(loginOptions);
        profilePage.updatePreferredLanguage('es');

        // Skip Intro.js overlay if visible
        cy.get('body').then(($body) => {
          if ($body.find('.introjs-overlay').length > 0) {
            cy.get('.introjs-skipbutton').click({ force: true });
          }
        });
      }
    });

    it(`Should be accessible ${isAuthenticated ? 'with' : 'without'} login`, () => {
      problemCreatorPage.visit();
      cy.url().should('include', '/problem/creator/');
      cy.get('[data-problem-creator-tab="statement"]').should('be.visible');
      cy.get('[data-problem-creator-tab="solution"]').should('be.visible');
      cy.get('[data-problem-creator-tab="code"]').should('be.visible');
      cy.get('[data-problem-creator-tab="cases"]').should('be.visible');
    });

    it(`Should write and verify the problem statement ${isAuthenticated ? 'with' : 'without'} login`, () => {
      problemCreatorPage.visit();
      cy.get('[data-problem-creator-tab="statement"]').click();
      cy.get('[data-problem-creator-editor-markdown]').type('Hello omegaUp!');
      cy.get('[data-problem-creator-save-markdown]').click();

      const expectedHeader = isAuthenticated ? 'Previsualización' : 'Preview';
      cy.get('[data-problem-creator-previewer-markdown]').should(
        'have.html',
        `<h1>${expectedHeader}</h1>\n\n<p>Hello omegaUp!</p>`,
      );
    });

    it(`Should write and verify the problem solution ${isAuthenticated ? 'with' : 'without'} login`, () => {
      problemCreatorPage.visit();
      cy.get('[data-problem-creator-tab="solution"]').click();
      cy.get('[data-problem-creator-solution-editor-markdown]').type('Hello **solution**!');
      cy.get('[data-problem-creator-solution-save-markdown]').click();

      const expectedHeader = isAuthenticated ? 'Previsualización' : 'Preview';
      cy.get('[data-problem-creator-solution-previewer-markdown]').should(
        'have.html',
        `<h1>${expectedHeader}</h1>\n\n<p>Hello <strong>solution</strong>!</p>`,
      );
    });

    it(`Should upload and verify the problem code ${isAuthenticated ? 'with' : 'without'} login`, () => {
      problemCreatorPage.visit();
      cy.get('[data-problem-creator-tab="code"]').click();
      cy.get('[data-problem-creator-code-input]').attachFile('../fixtures/main.rs');

      cy.wait(1000).then(() => {
        cy.get('.CodeMirror-line').eq(1).should('have.text', 'println!("Hello omegaUp!");');
        cy.get('[data-problem-creator-code-language]')
          .should('have.value', 'rs')
          .find('option:selected')
          .should('contain', 'Rust (1.56.1)');
      });
    });

    it(`Should add groups, cases and multiple cases ${isAuthenticated ? 'with' : 'without'} login`, () => {
      problemCreatorPage.visit();
      cy.get('[data-problem-creator-tab="cases"]').click();

      cy.get('[data-add-window]').click();
      cy.get('[data-problem-creator-add-panel-tab="case"]').click();
      cy.get('[data-problem-creator-case-input="name"]').type('Hello case');
      cy.get('[data-problem-creator-add-panel-submit]').click();

      cy.get('[data-add-window]').click();
      cy.get('[data-problem-creator-add-panel-tab="group"]').click();
      cy.get('[data-problem-creator-group-input="name"]').type('Hello group!');
      cy.get('[data-problem-creator-add-panel-submit]').click();

      cy.get('[data-add-window]').click();
      cy.get('[data-problem-creator-add-panel-tab="multiple-cases"]').click();
      cy.get('[data-problem-creator-multiple-cases-input="prefix"]').type('hello');
      cy.get('[data-problem-creator-multiple-cases-input="suffix"]').type('there');
      cy.get('[data-problem-creator-multiple-cases-input="count"]').clear().type('10');
      cy.get('[data-problem-creator-add-panel-submit]').click();

      cy.get('[data-sidebar-ungrouped-cases="count"]').invoke('text').should((text) => {
        expect(text.trim()).to.contain('11');
      });
      cy.get('[data-sidebar-groups="grouped"]').should('have.length', 1);
      cy.get('[data-sidebar-groups="count"]').invoke('text').should((text) => {
        expect(text.trim()).to.contain('0');
      });
    });

    it(`Should add and edit layouts ${isAuthenticated ? 'with' : 'without'} login`, () => {
      if (isAuthenticated) {
        
      
      problemCreatorPage.visit();
      cy.get('[data-problem-creator-tab="cases"]').click();

      cy.get('[data-add-window]').click();
      cy.get('[data-problem-creator-add-panel-tab="case"]').click();
      cy.get('[data-problem-creator-case-input="name"]').type('Hello case');
      cy.get('[data-problem-creator-add-panel-submit]').click();

      cy.get('[data-sidebar-groups="ungrouped"]').click();
      cy.get('[data-sidebar-cases-ungrouped]').first().click();

      const caseTypes = [
        { type: 'multiline', text: isAuthenticated ? T.problemCreatorLineMultiline : 'multilines' },
        { type: 'array', text: isAuthenticated ? T.problemCreatorLineArray : 'array' },
        { type: 'matrix', text: isAuthenticated ? T.problemCreatorLineMatrix : 'matrix' },
        { type: 'line', text: isAuthenticated ? T.problemCreatorLineLine : 'single line' },
      ];

      caseTypes.forEach(() => {
        cy.get('[data-edit-case-add-line]').click();
      });

      problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
        lineCases.forEach((lineCase) => {
          cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`).click();
          cy.get(`[data-array-modal-dropdown-kind="${lineCase.id}-${lineCase.type}"]`).click();
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
      openLayoutDropdownAndClick('[data-layout-dropdown-enforce-to-all]');

      cy.get('[data-toggle-layout-sidebar]').click();
      cy.get('[data-sidebar-cases-ungrouped]').last().click();

      problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
        lineCases.forEach((lineCase) => {
          cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
            .invoke('text')
            .then((text) => {
              expect(text.toLowerCase()).to.include(lineCase.text.toLowerCase());
            });
        });
      });

      cy.get('[data-toggle-layout-sidebar]').click();
      openLayoutDropdownAndClick('[data-layout-dropdown-copy]');

      cy.get('[data-layout-dropdown]').eq(1).click();
      cy.get('[data-line-info-dropdown]').eq(4).click();
      cy.get('[data-line-info-dropdown-item="array"]').eq(4).click();

      cy.get('button.dropdown-toggle-split').eq(3).click();
      cy.get('[data-layout-dropdown-enforce-to-selected]').eq(1).click();

      cy.get('[data-sidebar-cases-ungrouped]').first().click();
      problemCreatorPage.getLineIDs(caseTypes).then((lineCases) => {
        lineCases.forEach((lineCase) => {
          cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
            .invoke('text')
            .then((text) => {
              expect(text.toLowerCase()).to.include(lineCase.text.toLowerCase());
            });
        });
      });

      cy.get('[data-sidebar-cases-ungrouped]').last().click();

      const caseTypesUpdated = [
        { type: 'multiline', text: isAuthenticated ? T.problemCreatorLineArray : 'multilines' },
        { type: 'array', text: isAuthenticated ? T.problemCreatorLineArray : 'array' },
        { type: 'matrix', text: isAuthenticated ? T.problemCreatorLineMatrix : 'matrix' },
        { type: 'line', text: isAuthenticated ? T.problemCreatorLineLine : 'single line' },
      ];

      problemCreatorPage.getLineIDs(caseTypesUpdated).then((lineCases) => {
        lineCases.forEach((lineCase) => {
          cy.get(`[data-array-modal-dropdown="${lineCase.id}"]`)
            .invoke('text')
            .then((text) => {
              expect(text.toLowerCase()).to.include(lineCase.text.toLowerCase());
            });
        });
      });
    }
  });
  };

  describe('Unauthenticated Tests', () => {
    runTests(false);
  });

  describe('Authenticated Tests', () => {
    runTests(true);
  });
});