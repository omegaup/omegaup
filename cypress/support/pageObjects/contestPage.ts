import 'cypress-file-upload';
import 'cypress-wait-until';
import { v4 as uuid } from 'uuid';

import {
  ContestOptions,
  GroupOptions,
  LoginOptions,
  ProblemOptions,
  RunOptions,
} from '../types';
import { addSubtractDateTime, getISODateTime } from '../commands';

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
    cy.get('.introjs-skipbutton').click();
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

  // FIXME: When trying to bulk users, cypress is not able to find the results table
  // TODO: Replace multiuser add for courses/contests
  addStudentsBulk(users: Array<string>): void {
    cy.get('a[data-nav-contestant]').click();

    cy.get('textarea[data-contestant-names]').type(users.join(', '));
    cy.wait(1000); // Wait for the textarea to be updated
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
    cy.visit(`/arena/${contestOptions.contestAlias}/`);
    cy.get('a[href="#clarifications"]').click();
    cy.get('[data-tab-clarifications]').should('be.visible');
    cy.get('[data-select-answer]').select(answer);
    cy.get('[data-form-clarification-answer]').submit();
    cy.get('[data-form-clarification-resolved-answer]').should(
      'contain',
      answer,
    );
  }

  updateScoreboardForContest(contestAlias: string): void {
    const encodedContestAlias = encodeURIComponent(contestAlias);
    const scoreboardRefreshUrl = `/api/scoreboard/refresh/alias/${encodedContestAlias}/token/secret`;

    cy.request(scoreboardRefreshUrl).then((resp) => {
      expect(resp.status).to.eq(200);
    });
  }

  createContest(contestOptions: ContestOptions, users: Array<string>, shouldShowIntro: boolean = true): void {
    cy.createContest(contestOptions, shouldShowIntro);
    cy.location('href').should('include', contestOptions.contestAlias);
    cy.get('a[data-contest-new-form]').trigger('click');
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
  }

  generateContestOptions(
    loginOption: LoginOptions,
    firstTimeVisited: boolean = true,
    numberOfProblems: number = 1,
  ): ContestOptions {
    const problems = this.generateProblemOptions(numberOfProblems);
    const contestProblems: ProblemOptions[] = [];
    const contestRuns: RunOptions[] = [];

    problems.forEach( (problem) => {
      problem.firstTimeVisited = firstTimeVisited;

      cy.login(loginOption);
      cy.createProblem(problem);
      cy.logout();
      contestProblems.push({
        problemAlias: problem.problemAlias,
        tag: problem.tag,
        autoCompleteTextTag: problem.autoCompleteTextTag,
        problemLevelIndex: problem.problemLevelIndex,
      });
      contestRuns.push({
        problemAlias: problem.problemAlias,
        fixturePath: 'main.cpp',
        language: 'cpp11-gcc',
        valid: true,
        status: 'AC',
      });
      firstTimeVisited = false;
    });

    const now = new Date();
    const contestOptions: ContestOptions = {
      contestAlias: 'contest' + uuid().slice(0, 5),
      description: 'Test Description',
      startDate: now,
      endDate: addSubtractDateTime(now, { days: 2 }),
      showScoreboard: true,
      basicInformation: false,
      scoreMode: ScoreMode.Partial,
      requestParticipantInformation: 'no',
      admissionMode: 'public',
      problems: contestProblems,
      runs: contestRuns,
    };

    return contestOptions;
  }

  generateProblemOptions(noOfProblems: number): ProblemOptions[] {
    const problems: ProblemOptions[] = [];

    for (let i = 0; i < noOfProblems; i++) {
      const problemOptions: ProblemOptions = {
        problemAlias: uuid().slice(0, 10),
        tag: 'Recursion',
        autoCompleteTextTag: 'recur',
        problemLevelIndex: 0,
      };

      problems.push(problemOptions);
    }

    return problems;
  }

  setPasswordForIdentity(identityName: string, password: string): void {
    cy.get('[data-identity-change-password]').first().click();
    cy.get('input[type=password]').first().type(password);
    cy.get('input[type=password]').last().type(password);
    cy.get('[data-change-password-identity]').click();
  }

  verifySubmissionInPastContest(username: string, contestAlias: string): void {
    cy.visit(`/arena/${contestAlias}/#ranking`);
    cy.get('[data-table-scoreboard-username]').should('contain', username);
  }

  verifyContestDetails(contestOptions: ContestOptions): void {
    cy.visit(`/contest/${contestOptions.contestAlias}/edit`);
    cy.get('[name="title"]').should('have.value', contestOptions.contestAlias);
    cy.get('[name="alias"]').should('have.value', contestOptions.contestAlias);
    cy.get('[name="description"]').should(
      'have.value',
      contestOptions.description,
    );
    cy.get('[data-start-date]').should(
      'have.value',
      getISODateTime(contestOptions.startDate),
    );
    cy.get('[data-end-date]').type(getISODateTime(contestOptions.endDate));
    cy.get('[data-show-scoreboard-at-end]').should(
      'have.value',
      `${contestOptions.showScoreboard}`,
    );
    cy.get('[data-score-mode]').should(
      'have.value',
      `${contestOptions.scoreMode}`,
    );
    cy.get('[data-basic-information-required]').should(
      contestOptions.basicInformation ? 'be.checked' : 'not.be.checked',
    );
    cy.get('[data-request-user-information]').should(
      'have.value',
      contestOptions.requestParticipantInformation,
    );
  }

  mergeContests(contestAlias: string[]): void {
    cy.visit('/scoreboardmerge');
    cy.get('[data-merge-contest-name]').click();
    contestAlias.forEach((contestAlias) => {
      cy.get('[data-merge-contest-name] input').type(contestAlias + '{enter}');
    });
    cy.get('[data-merge-contest-button]').click();
  }

  verifyMergedScoreboard(users: string[]): void {
    cy.get('[data-test-merged-username]').should('have.length', users.length);
    cy.get('[data-test-merged-username]').first().should('contain', users[0]);
    cy.get('[data-test-merged-username]').last().should('contain', users[1]);
    cy.get('[data-total-merged-score]').first().should('contain', '100');
    cy.get('[data-total-merged-score]').last().should('contain', '100');
  }
}

export const contestPage = new ContestPage();
