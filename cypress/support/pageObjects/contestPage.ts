import 'cypress-file-upload';
import 'cypress-wait-until';
import { v4 as uuid } from 'uuid';

import { ContestOptions, GroupOptions } from '../types';
import { addSubtractDaysToDate } from '../commands';

enum ScoreMode {
  AllOrNothing = 'all_or_nothing',
  Partial = 'partial',
  MaxPerGroup = 'max_per_group',
}

export class ContestPage {
  createGroup(groupOptions: GroupOptions): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-user-groups]').click();

    cy.get('[href="/group/new/"]').click();

    cy.get('[name="title"]').type(groupOptions.groupTitle);
    cy.get('[name="description"]').type(groupOptions.groupDescription);

    cy.get('[data-group-new]').submit();
  }

  addIdentitiesGroup(): void {
    cy.get('[href="#identities"]').click();
    cy.get('[name="identities"]').attachFile('identities.csv');

    cy.get('[data-identity-username]').then((rawHTMLElements) => {
      const userNames: Array<string> = [];
      Cypress.$.makeArray(rawHTMLElements).forEach((element) => {
        cy.task('log', element.innerText);
        userNames.push(element.innerText);
      });

      cy.wrap(userNames).as('userNamesList');
    });

    const uploadedPasswords: Array<string> = [];
    cy.get('[data-identity-password]').then((rawHTMLElements) => {
      uploadedPasswords.concat(
        Cypress.$.makeArray(rawHTMLElements).map((el) => el.innerText),
      );
    });

    cy.get('[name="create-identities"]').click();
    cy.get('#alert-close').click();
    cy.waitUntil(() => {
      return cy.get('#alert-close').should('not.be.visible');
    });

    cy.get('[href="#members"]').click();
    cy.get('@userNamesList').then((textArray) => {
      cy.get('[data-members-username]')
        .should('have.length', textArray.length)
        .then((rawHTMLElements) => {
          return Cypress.$.makeArray(rawHTMLElements).map((el) => el.innerText);
        })
        .should('deep.equal', textArray);
    });
  }

  addStudentsBulk(users: Array<string>): void {
    cy.get('a[data-nav-contest-edit]').click();
    cy.get('a[data-nav-contestant]').click();

    cy.get('textarea[data-contestant-names]').type(users.join(', '));
    cy.get('.user-add-bulk').click();

    cy.get('[data-uploaded-contestants]').then((rawHTMLElements) => {
      const constestantNames: Array<string> = [];
      Cypress.$.makeArray(rawHTMLElements).forEach((element) => {
        cy.task('log', element.innerText);
        constestantNames.push(element.innerText);
      });

      cy.wrap(constestantNames).as('savedConstestantNames');
    });

    cy.get('@savedConstestantNames').should('deep.equal', users);
  }

  createClarificationUser(
    contestOptions: ContestOptions,
    question: string,
  ): void {
    cy.get('a[href="#clarifications"]').click();
    cy.waitUntil(() =>
      cy.get('[data-tab-clarifications]').should('be.visible'),
    );

    cy.get('a[data-new-clarification-button]').click();
    cy.get('[data-new-clarification-problem]').select(
      contestOptions.problems[0].problemAlias,
    );
    cy.get('[data-new-clarification-message]')
      .should('be.visible')
      .type(question);

    cy.get('[data-new-clarification]').submit();
    cy.get('[data-form-clarification-message]').should('have.text', question);
  }

  answerClarification(contestOptions: ContestOptions, answer: string): void {
    cy.loginAdmin();
    cy.visit(`/arena/${contestOptions.contestAlias}/`);
    cy.get('a[href="#clarifications"]').click();
    cy.get('[data-tab-clarifications]').should('be.visible');
    cy.get('[data-select-answer]').select(answer);
    cy.get('[data-form-clarification-answer]').submit();
    cy.get('[data-form-clarification-resolved-answer]').should(
      'contain',
      answer,
    );
    cy.logout();
  }

  updateScoreboardForContest(contestAlias: string): void {
    const encodedContestAlias = encodeURIComponent(contestAlias);
    const scoreboardRefreshUrl = `/api/scoreboard/refresh/alias/${encodedContestAlias}/token/secret`;

    cy.request(scoreboardRefreshUrl).then((resp) => {
      expect(resp.status).to.eq(200);
    });
  }

  createContestAsAdmin(
    contestOptions: ContestOptions,
    users: Array<string>,
  ): void {
    cy.loginAdmin();
    cy.createContest(contestOptions);

    cy.location('href').should('include', contestOptions.contestAlias);
    cy.get('[name="title"]').should('have.value', contestOptions.contestAlias);
    cy.get('[name="alias"]').should('have.value', contestOptions.contestAlias);
    cy.get('[name="description"]').should(
      'have.value',
      contestOptions.description,
    );

    cy.addProblemsToContest(contestOptions);

    this.addStudentsBulk(users);
    cy.changeAdmissionModeContest(contestOptions);

    cy.get('a[data-contest-link-button]').click();
    cy.url().should('include', '/arena/' + contestOptions.contestAlias);

    cy.get('a[href="#ranking"]').click();
    cy.waitUntil(() => cy.get('[data-table-scoreboard]').should('be.visible'));
    cy.logout();
  }

  generateContestOptions(): ContestOptions {
    const now = new Date();

    const contestOptions: ContestOptions = {
      contestAlias: 'contest' + uuid().slice(0, 5),
      description: 'Test Description',
      startDate: addSubtractDaysToDate(now, { days: -1 }),
      endDate: addSubtractDaysToDate(now, { days: 2 }),
      showScoreboard: true,
      basicInformation: false,
      scoreMode: ScoreMode.Partial,
      requestParticipantInformation: 'no',
      admissionMode: 'public',
      problems: [
        {
          problemAlias: 'sumas',
          tag: 'Recursion',
          autoCompleteTextTag: 'Recur',
          problemLevelIndex: 1,
        },
      ],
      runs: [
        {
          problemAlias: 'sumas',
          fixturePath: 'main.cpp',
          language: 'cpp11-gcc',
          valid: true,
          status: 'AC',
        },
      ],
    };

    return contestOptions;
  }
}

export const contestPage = new ContestPage();
