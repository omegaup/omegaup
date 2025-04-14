import { profilePage } from '../support/pageObjects/profilePage';
import { problemPage } from '../support/pageObjects/problemPage';
import { loginPage } from '../support/pageObjects/loginPage';
import { LoginOptions, ProblemOptions, RunOptions } from '../support/types';
import * as Util from '../../frontend/www/js/omegaup/grader/util';
import * as JSZip from 'jszip';

describe('Test IDE', () => {
  let problemOptions: ProblemOptions[];

  let loginOptions: LoginOptions[];
  before(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');

    loginOptions = loginPage.registerMultipleUsers(1);
    problemOptions = problemPage.generateProblemOptions(3);

    cy.login(loginOptions[0]);
    cy.createProblem(problemOptions[0]);

    cy.logout();
  });

  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should verify that zip files working as intended', () => {
    cy.login(loginOptions[0]);
    cy.visit('/grader/ephemeral/');

    cy.get('[data-zip-download]').should('be.visible').click();
    cy.wait(1000); // wait a little bit to make sure the file is ready
    cy.get('[data-zip-download]').should('be.visible').click(); // cypress/downloads

    const fileName = `${Util.DUMMY_PROBLEM.alias}.zip`;
    const filePath = `cypress/downloads/${fileName}`;
    cy.get('[data-zip-upload]').should('be.visible');
    cy.get('input[type="file"]').selectFile(filePath, {
      force: true,
      action: 'drag-drop',
    });

    cy.get('input[type="file"]').then(($input) => {
      const file = ($input[0] as HTMLInputElement).files?.[0];
      // cypress promise is aware, this promise will run
      return new Cypress.Promise((resolve, reject) => {
        if (!file) {
          reject(new Error(`file not found`));
          return;
        }
        const reader = new FileReader();

        reader.onload = () => {
          JSZip.loadAsync(reader.result as ArrayBuffer)
            .then((zip) => {
              cy.log('log', Object.keys(zip.files));
              expect(zip.files).to.have.property('settings.json');
              expect(zip.files).to.have.property('cases/');
              expect(zip.files).to.have.property('testplan');
              resolve(zip);
            })
            .catch((err) => {
              reject(new Error(`error loading zip: ${err.message}`));
            });
        };

        reader.onerror = () => reject(new Error('Error reading file'));

        reader.readAsArrayBuffer(file);
      });
    });

    cy.visit('/');
    cy.logout();
  });

  it('Should create a problem of type output only and display cat langauge only', () => {
    cy.login(loginOptions[0]);

    const catProblemOptions: ProblemOptions = {
      ...problemOptions[1],
      languagesValue: 'cat',
    };
    cy.createProblem(catProblemOptions);

    cy.visit(`arena/problem/${catProblemOptions.problemAlias}/`);
    cy.reload();

    cy.get('[data-language-select] option').should('have.length', 1);
    cy.get('[data-language-select] option[value="cat"]').should('exist');

    cy.logout();
  });

  it('Should create an interactive problem and verify its visible', () => {
    cy.login(loginOptions[0]);

    const interactiveProblemOptions: ProblemOptions = {
      ...problemOptions[2],
      zipFile: 'testproblem_interactive.zip',
    };
    cy.createProblem(interactiveProblemOptions);

    cy.visit(`arena/problem/${interactiveProblemOptions.problemAlias}/`);
    cy.reload();

    cy.get('.download-os').should('be.visible');
    cy.get('.download-lang').should('be.visible');

    cy.get(`li[title="code"]`).should('be.visible');
    cy.get(`li[title="logs.txt"]`).should('be.visible');

    cy.get(`li[title="cases"]`).should('be.visible').click();
    cy.get(`li[title="diff"]`).should('be.visible');

    cy.logout();
  });

  it('Should check that interactive problem template loads properly', () => {
    cy.login(loginOptions[0]);

    cy.visit(`arena/problem/${problemOptions[2].problemAlias}/`);
    cy.reload();

    cy.get('.view-line span span').then(($spans) => {
      const concatText = Array.from($spans, (span) =>
        span.innerText.replace(/\s/g, ''),
      ).join('');

      cy.fixture('interactive_template.cpp').then((fileContent) => {
        expect(concatText).to.equal(fileContent.replace(/\s/g, ''));
        cy.task('log', fileContent);
      });
    });

    cy.logout();
  });

  it('Should display full list of supported languages in profile prefrences page', () => {
    cy.login(loginOptions[0]);

    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-preferences"]').click();
    cy.get('[data-preferred-language]').should('exist');

    cy.get('[data-preferred-language]').as('selectMenu').should('exist');
    Object.keys(Util.supportedLanguages).forEach((language) => {
      // cannot select cat language
      if (language === 'cat') return;
      cy.get('@selectMenu').find(`option[value="${language}"]`).should('exist');
    });

    cy.logout();
  });

  it('Should verify that original editor of diff is updated basted on output', () => {
    cy.login(loginOptions[0]);

    cy.visit(`arena/problem/${problemOptions[0].problemAlias}/`);
    cy.reload();

    const caseName = 'test';
    const caseOutput = '1st\n2nd\n3rd';

    cy.get('input[data-case-name]').type(caseName);
    cy.get('[data-add-button]').should('be.visible').click();

    cy.get(`textarea[data-title="${caseName}.out"]`, { timeout: 10000 })
      .first()
      .should('be.visible')
      .type(caseOutput);
<<<<<<< HEAD

    cy.get('li[title="diff"]').should('be.visible').click();

    cy.get('.editor.original .view-line span span', { timeout: 10000 })
      .should('have.length.greaterThan', 0)
=======
    cy.get(`li[title="diff"]`).should('be.visible').click();

    cy.get('.editor.original .view-line span span') // lhs is the original text
>>>>>>> d99df7660 (added the test files back)
      .then(($spans) => {
        const concatText = Array.from($spans, (span) => span.innerText).join(
          '\n',
        );
        expect(concatText).to.equal(caseOutput);
      });
    cy.logout();
  });

  it('Should verify that zip files after clicking run', () => {
    cy.login(loginOptions[0]);

    cy.visit(`arena/problem/${problemOptions[0].problemAlias}/`);
    cy.reload();

    cy.get('[data-run-button]').should('be.visible').click();
    cy.get(`li[title="files.zip"]`).should('be.visible').click();

    const extensions = ['err', 'out', 'meta'];
    extensions.forEach((extension) => {
      cy.get(`button[title="Main/compile.${extension}"]`).should('be.visible');
    });

    cy.logout();
  });

  it('Should verify log data after clicking run', () => {
    cy.login(loginOptions[0]);

    cy.visit(`arena/problem/${problemOptions[0].problemAlias}/`);
    cy.reload();

    cy.get('[data-run-button]').should('be.visible').click();
    cy.get(`li[title="logs.txt"]`).should('be.visible').click();

    cy.get('[data-language-select]')
      .invoke('val')
      .then((selectedLanguage) => {
        cy.get('textarea[data-title="logs.txt"]')
          .invoke('val')
          .should('not.be.empty', { timeout: 10000 }) // the following are some of the text that should exist
          .should('contain', `Language:${selectedLanguage}`)
          .should('contain', 'client')
          .should('contain', 'runner');
      });
    cy.logout();
  });

  it('Should create a new case for a problem', () => {
    cy.login(loginOptions[0]);

    cy.visit(`arena/problem/${problemOptions[0].problemAlias}/`);
    cy.reload();

    const caseName = 'test';
    cy.get('input[data-case-name]').type(caseName);
    cy.get('[data-add-button]').should('be.visible').click();
    cy.get(`li[title="${caseName}.in"]`).should('be.visible');

    cy.logout();
  });

  it('Should change preferred language for user and follow hierarchical order to define the programming language', () => {
    cy.login(loginOptions[0]);

    // update preferred langauge to py2
    profilePage.updatePreferredProgrammingLanguage('py2');
    // go to the link with the editor
    cy.visit(`arena/problem/${problemOptions[0].problemAlias}/`);

    cy.get('[data-language-select]')
      .should('be.visible')
      .find('option:selected')
      .should('have.value', 'py2');

    // make the submission with cpp20
    const runOptions: RunOptions = {
      problemAlias: problemOptions[0].problemAlias,
      fixturePath: 'main.cpp',
      language: 'cpp20-gcc',
      valid: true,
      status: 'AC',
    };
    problemPage.createRun(runOptions);

    // reload the page, check the language again
    // clear session storage before reloading
    cy.clearAllSessionStorage();
    cy.reload();

    cy.get('.close:visible').each(($button) => {
      cy.wrap($button).click();
    });

    cy.get('[data-language-select]')
      .should('be.visible')
      .find('option:selected')
      .should('have.value', 'cpp20-gcc');

    cy.logout();
  });
});
