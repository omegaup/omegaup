import { v4 as uuid } from 'uuid';
import { ContestOptions, GroupOptions, LoginOptions } from '../support/types';
import { contestPage } from '../support/pageObjects/contestPage';
import { loginPage } from '../support/pageObjects/loginPage';
import { addSubtractDaysToDate } from '../support/commands';

describe('Contest Test', () => {
  let virtualContestDetails: ContestOptions;
  let contestWithHalfTimeVisibleScoreboard: ContestOptions;
  let loginOptionstoVerify: LoginOptions;

  before(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');

    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const users = [userLoginOptions[0].username];

    const now = new Date();

    contestOptions.startDate = addSubtractDaysToDate(now, { days: -1 });
    const milliseconds = 200 * 1000;
    let newEndDate = new Date();
    newEndDate = new Date(newEndDate.getTime() + milliseconds);
    contestOptions.endDate = newEndDate;

    virtualContestDetails = contestOptions;
    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();
  });

  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should create a contest when the scoreboard is shown half the time', () => {
    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(3);
    const users = [userLoginOptions[0].username];
    loginOptionstoVerify = userLoginOptions[1];

    contestOptions.scoreBoardVisibleTime = '50';
    const now = new Date();
    contestOptions.startDate = now;
    const milliseconds = 480 * 1000;
    let newEndDate = new Date();
    newEndDate = new Date(newEndDate.getTime() + milliseconds);
    contestOptions.endDate = newEndDate;

    contestWithHalfTimeVisibleScoreboard = contestOptions;
    cy.login(userLoginOptions[2]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();
  });

  it('Should create a contest with different start', () => {
    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const users = [userLoginOptions[0].username];
    const now = new Date();

    contestOptions.differentStart = true;
    contestOptions.differentStartTime = "60";
    contestOptions.startDate = addSubtractDaysToDate(now, { days: -1 });
    contestOptions.endDate = addSubtractDaysToDate(now, { days: 1 });
    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.get('.clock').should('contain', "59");
    cy.logout();
  });

  it('Should create a contest and retrieve it', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(3);

    const groupOptions: GroupOptions = {
      groupTitle: 'ut_group_' + uuid(),
      groupDescription: 'group description',
    };

    loginPage.giveAdminPrivilage(
      'GroupIdentityCreator',
      userLoginOptions[0].username,
    );
  
    cy.login(userLoginOptions[0]);
    contestPage.createGroup(groupOptions);
    contestPage.addIdentitiesGroup();
    cy.logout();

    const contestOptions = contestPage.generateContestOptions();

    const users = [userLoginOptions[1].username, userLoginOptions[2].username];
    cy.login(userLoginOptions[0])
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[1]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    contestPage.updateScoreboardForContest(contestOptions.contestAlias);

    cy.login(userLoginOptions[0])
    cy.visit(`/arena/${contestOptions.contestAlias}/`);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard-username]')
      .first()
      .should('contain', userLoginOptions[1].username);
    cy.get('[data-table-scoreboard-username]')
      .last()
      .should('contain', userLoginOptions[2].username);
    cy.logout();
  });

  it('Should create a contest and add a clarification.', () => {
    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(2);

    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, [
      userLoginOptions[0].username,
    ]);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    contestPage.createClarificationUser(contestOptions, 'Question 1');
    cy.logout();

    cy.login(userLoginOptions[1]);
    contestPage.answerClarification(contestOptions, 'No');
    cy.logout();
  });

  it('Should create a contest and review ranking', () => {
    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(5);

    const groupOptions: GroupOptions = {
      groupTitle: 'ut_group_' + uuid(),
      groupDescription: 'group description',
    };

    loginPage.giveAdminPrivilage(
      'GroupIdentityCreator',
      userLoginOptions[4].username,
    );
  
    cy.login(userLoginOptions[4]);
    contestPage.createGroup(groupOptions);
    contestPage.addIdentitiesGroup();
    cy.logout();

    const users: Array<string> = [];
    userLoginOptions.forEach((loginDetails) => {
      users.push(loginDetails.username);
    });

    cy.login(userLoginOptions[4]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    cy.login(userLoginOptions[2]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    contestPage.updateScoreboardForContest(contestOptions.contestAlias);

    cy.login(userLoginOptions[4]);
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

  it('Should test practice mode in contest', () => {
    const contestOptions = contestPage.generateContestOptions();
    const now = new Date();
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const users = [userLoginOptions[0].username];

    contestOptions.startDate = addSubtractDaysToDate(now, { days: -1 });
    contestOptions.endDate = now;

    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

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

    cy.login(userLoginOptions[1]);
    contestPage.answerClarification(contestOptions, 'No');
    cy.logout();
  });

  it(
    'Should create a contest and review contest ranking when the scoreboard shows' +
      ' time has finished',
    () => {
      const contestOptions = contestPage.generateContestOptions();
      const userLoginOptions = loginPage.registerMultipleUsers(3);
      const users = [
        userLoginOptions[0].username,
        userLoginOptions[1].username,
      ];

      cy.login(userLoginOptions[2]);
      contestPage.createContest(contestOptions, users);
      cy.logout();

      cy.login(userLoginOptions[0]);
      cy.enterContest(contestOptions);
      cy.createRunsInsideContest(contestOptions);
      cy.logout();

      contestPage.updateScoreboardForContest(contestOptions.contestAlias);

      cy.login(userLoginOptions[2]);
      cy.visit(`/arena/${contestOptions.contestAlias}/`);
      cy.get('a[href="#ranking"]').click();
      cy.get('[data-table-scoreboard]').should('be.visible');
      cy.get('[data-table-scoreboard-username]').should('have.length', 2);
      cy.get(`.${userLoginOptions[0].username} > td:nth-child(4)`).should(
        'contain',
        '+100.00',
      );
      cy.get(`.${userLoginOptions[1].username} > td:nth-child(4)`).should(
        'contain',
        '-',
      );
      cy.logout();
    },
  );

  it('Should create a contest when the scoreboard is never shown', () => {
    const contestOptions = contestPage.generateContestOptions();
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const users = [userLoginOptions[0].username];

    contestOptions.scoreBoardVisibleTime = '0';
    cy.login(userLoginOptions[1])
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(4)`).should(
      'contain',
      '0.00',
    );
    cy.logout();
  });

  it('Should verify the scorebaord is not upadating after half of time', () => {
    cy.login(loginOptionstoVerify);
    cy.enterContest(contestWithHalfTimeVisibleScoreboard);
    cy.createRunsInsideContest(contestWithHalfTimeVisibleScoreboard);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.logout();
  });

  it('Should give a past contest as a virtual contest', () => {
    const contestOptions = virtualContestDetails;
    const userLoginOptions = loginPage.registerMultipleUsers(1);
    const virtualContestUrl = `/contest/${contestOptions.contestAlias}/virtual/`;

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
    cy.get('[data-table-scoreboard-username]').should('have.length', 2);
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(4)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();
  });
});
