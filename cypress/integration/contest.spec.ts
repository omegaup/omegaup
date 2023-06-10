import { v4 as uuid } from 'uuid';
import { GroupOptions } from '../support/types';
import { contestPage } from '../support/pageObjects/contestPage';
import { loginPage } from '../support/pageObjects/loginPage';
import { addSubtractDaysToDate } from '../support/commands';

describe('Contest Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should create a contest and retrieve it', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(2);

    const groupOptions: GroupOptions = {
      groupTitle: 'ut_group_' + uuid(),
      groupDescription: 'group description',
    };

    cy.loginAdmin();
    contestPage.createGroup(groupOptions);
    contestPage.addIdentitiesGroup();
    cy.logout();

    const contestOptions = contestPage.generateContestOptions();

    const users = [userLoginOptions[0].username, userLoginOptions[1].username];
    contestPage.createContestAsAdmin(contestOptions, users);

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    contestPage.updateScoreboardForContest(contestOptions.contestAlias);

    cy.loginAdmin();
    cy.visit(`/arena/${contestOptions.contestAlias}/`);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard-username]')
      .first()
      .should('contain', userLoginOptions[0].username);
    cy.get('[data-table-scoreboard-username]')
      .last()
      .should('contain', userLoginOptions[1].username);
    cy.logout();
  });

  it('Should create a contest and add a clarification.', () => {
    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(1);

    contestPage.createContestAsAdmin(contestOptions, [
      userLoginOptions[0].username,
    ]);

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    contestPage.createClarificationUser(contestOptions, 'Question 1');
    cy.logout();

    contestPage.answerClarification(contestOptions, 'No');
  });

  it('Should create a contest and review ranking', () => {
    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(4);

    const groupOptions: GroupOptions = {
      groupTitle: 'ut_group_' + uuid(),
      groupDescription: 'group description',
    };

    cy.loginAdmin();
    contestPage.createGroup(groupOptions);
    contestPage.addIdentitiesGroup();
    cy.logout();

    const users: Array<string> = [];
    userLoginOptions.forEach((loginDetails) => {
      users.push(loginDetails.username);
    });

    contestPage.createContestAsAdmin(contestOptions, users);

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    cy.login(userLoginOptions[2]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    contestPage.updateScoreboardForContest(contestOptions.contestAlias);

    cy.loginAdmin();
    cy.visit(`/arena/${contestOptions.contestAlias}/`);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 4);
    cy.get(`.${userLoginOptions[2].username} > td:nth-child(2)`).should(
      'contain',
      '1',
    );
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(2)`).should(
      'contain',
      '1',
    );
    cy.get(`.${userLoginOptions[1].username} > td:nth-child(2)`).should(
      'contain',
      '3',
    );
    cy.get(`.${userLoginOptions[3].username} > td:nth-child(2)`).should(
      'contain',
      '3',
    );
    cy.logout();
  });

  it('Should give a past contest as a virtual contest', () => {
    const contestOptions = contestPage.generateContestOptions();
    const now = new Date();
    const userLoginOptions = loginPage.registerMultipleUsers(1);
    const users = [userLoginOptions[0].username];
    const virtualContestUrl = `/contest/${contestOptions.contestAlias}/virtual/`;

    contestOptions.startDate = addSubtractDaysToDate(now, { days: -1 });
    contestOptions.endDate = now;

    contestPage.createContestAsAdmin(contestOptions, users);

    cy.login(userLoginOptions[0]);
    cy.visit(virtualContestUrl);
    cy.get('[data-schedule-virtual-button]').click();
    cy.get('[data-contest-link-button]').click();
    cy.url().should('include', contestOptions.contestAlias);
    cy.url().then((url) => {
      const virtualContestCode = url.split('/')[4].split('-')[2];
      const newContestAlias =
        contestOptions.contestAlias + '-virtual-' + virtualContestCode;

      contestOptions.contestAlias = newContestAlias;
      cy.createRunsInsideContest(contestOptions);
    });
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();
  });

  it('Should test practice mode in contest', () => {
    const contestOptions = contestPage.generateContestOptions();
    const now = new Date();
    const userLoginOptions = loginPage.registerMultipleUsers(1);
    const users = [userLoginOptions[0].username];

    contestOptions.startDate = addSubtractDaysToDate(now, { days: -1 });
    contestOptions.endDate = now;

    contestPage.createContestAsAdmin(contestOptions, users);

    cy.login(userLoginOptions[0]);
    cy.visit(`/arena/${contestOptions.contestAlias}/`);
    cy.get('[data-start-contest]').click();
    cy.get(`a[href="/arena/${contestOptions.contestAlias}/practice/"]`)
      .click()
      .then(() => {
        const newContestAlias = contestOptions.contestAlias + '/practice';
        contestOptions.contestAlias = newContestAlias;
        cy.createRunsInsideContest(contestOptions);
      });
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-markdown-statement] > p > a')
      .should('have.attr', 'href')
      .and('include', contestOptions.contestAlias);
    contestPage.createClarificationUser(contestOptions, 'Question 1');
    cy.logout();

    contestPage.answerClarification(contestOptions, 'No');
  });
});
