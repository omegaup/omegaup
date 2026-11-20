import { v4 as uuid } from 'uuid';
import { ContestOptions, GroupOptions, LoginOptions } from '../support/types';
import { contestPage } from '../support/pageObjects/contestPage';
import { loginPage } from '../support/pageObjects/loginPage';
import { addSubtractDateTime } from '../support/commands';
import { profilePage } from '../support/pageObjects/profilePage';
import { groupPage } from '../support/pageObjects/groupPage';

// ponytail: set contest window via API instead of relying on slow intermediate tests to burn 200-240s of wall clock.
const updateContestDatesAsAdmin = (
  adminLogin: LoginOptions,
  contestAlias: string,
  dates: { start_time?: number; finish_time: number },
): void => {
  cy.request({
    method: 'POST',
    url: '/api/user/login/',
    form: true,
    body: {
      usernameOrEmail: adminLogin.username,
      password: adminLogin.password,
    },
  })
    .its('status')
    .should('eq', 200);
  cy.request({
    method: 'POST',
    url: '/api/contest/update/',
    form: true,
    body: {
      contest_alias: contestAlias,
      ...dates,
    },
  })
    .its('status')
    .should('eq', 200);
  contestPage.updateScoreboardForContest(contestAlias);
  cy.clearCookies();
};

describe('Contest Test', () => {
  let virtualContestDetails: ContestOptions;
  let contestWithHalfTimeVisibleScoreboard: ContestOptions;
  let loginOptionstoVerify: LoginOptions;
  let halfTimeContestAdmin: LoginOptions;
  let halfTimeStartSec = 0;
  let halfTimeFirstRunEndSec = 0;
  let identityLogin: LoginOptions;
  let identityContestAlias: string;

  before(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');

    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[1],
    );
    const users = [userLoginOptions[0].username];

    const now = new Date();
    contestOptions.startDate = now;
    contestOptions.endDate = new Date(now.getTime() + 200 * 1000);

    virtualContestDetails = contestOptions;
    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, users);
    cy.then(() => {
      const nowSec = Math.floor(Date.now() / 1000);
      cy.request({
        method: 'POST',
        url: '/api/contest/update/',
        form: true,
        body: {
          contest_alias: contestOptions.contestAlias,
          start_time: nowSec - 300,
          finish_time: nowSec + 300,
        },
      })
        .its('status')
        .should('eq', 200);
    });
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    cy.then(() => {
      const nowSec = Math.floor(Date.now() / 1000);
      updateContestDatesAsAdmin(
        userLoginOptions[1],
        contestOptions.contestAlias,
        {
          finish_time: nowSec,
        },
      );
    });
    cy.wait(1100);
  });

  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should create a contest and participate in it as a identity', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const groupOptions: GroupOptions = {
      groupTitle: 'ut_group_' + uuid(),
      groupDescription: 'group description',
    };
    const contestOptions = contestPage.generateContestOptions(loginOptions[0]);
    identityContestAlias = contestOptions.contestAlias;
    const now = new Date();

    contestOptions.startDate = now;
    contestOptions.endDate = new Date(now.getTime() + 200 * 1000);
    identityLogin = {
      username:
        groupOptions.groupTitle.split('-').slice(0, -1).join('-') +
        ':identity_1',
      password: '12345678',
    };

    loginPage.giveAdminPrivilege(
      'GroupIdentityCreator',
      loginOptions[0].username,
    );

    cy.login(loginOptions[0]);
    groupPage.createGroup(groupOptions);
    groupPage.addIdentitiesGroup();
    contestPage.setPasswordForIdentity(identityLogin.username, '12345678');
    contestPage.createContest(contestOptions, [identityLogin.username]);
    cy.logout();

    cy.login(identityLogin);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get('.points').should('contain', '+100.00');
    cy.logout();
  });

  it('Should create a contest when the scoreboard is shown half the time', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(3);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[2],
    );
    const users = [userLoginOptions[0].username];
    loginOptionstoVerify = userLoginOptions[1];
    halfTimeContestAdmin = userLoginOptions[2];

    contestOptions.scoreBoardVisibleTime = '50';
    const now = new Date();
    contestOptions.startDate = now;
    contestOptions.endDate = new Date(now.getTime() + 480 * 1000);

    contestWithHalfTimeVisibleScoreboard = contestOptions;
    cy.login(userLoginOptions[2]);
    contestPage.createContest(contestOptions, users);
    cy.then(() => {
      halfTimeStartSec = Math.floor(Date.now() / 1000) - 600;
      cy.request({
        method: 'POST',
        url: '/api/contest/update/',
        form: true,
        body: {
          contest_alias: contestOptions.contestAlias,
          start_time: halfTimeStartSec,
          finish_time: halfTimeStartSec + 1800,
        },
      })
        .its('status')
        .should('eq', 200);
    });
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.then(() => {
      halfTimeFirstRunEndSec = Math.floor(Date.now() / 1000);
    });
    cy.logout();
  });

  it('Should create a contest with different start', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[1],
    );
    const users = [userLoginOptions[0].username];
    const now = new Date();

    contestOptions.differentStart = true;
    contestOptions.differentStartTime = '60';
    contestOptions.startDate = now;
    contestOptions.endDate = addSubtractDateTime(now, { days: 1 });
    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.get('.clock').should('contain', '59');
    cy.logout();
  });

  it('Should create a contest and add a clarification.', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[1],
    );

    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, [userLoginOptions[0].username]);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    contestPage.createClarificationUser(contestOptions, 'Question 1');
    cy.logout();

    cy.login(userLoginOptions[1]);
    contestPage.answerClarification(contestOptions, 'No');
    cy.logout();
  });

  it('Should verify the scorebaord is not upadating after half of time', () => {
    cy.wait(2000).then(() => {
      const nowSec = Math.floor(Date.now() / 1000);
      const finishSec = halfTimeFirstRunEndSec + nowSec - halfTimeStartSec;
      updateContestDatesAsAdmin(
        halfTimeContestAdmin,
        contestWithHalfTimeVisibleScoreboard.contestAlias,
        { finish_time: finishSec },
      );
    });
    cy.visit('/');
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
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
    cy.logout();
  });

  it('Should merge identities', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);

    cy.login(loginOptions[0]);
    profilePage.mergeIdentities(identityLogin);
    profilePage.changeIdentity(identityLogin.username);
    contestPage.verifySubmissionInPastContest(
      identityLogin.username,
      identityContestAlias,
    );
    cy.logout();
  });

  it('Should create a contest when the scoreboard is never shown', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[1],
    );
    const users = [userLoginOptions[0].username];

    contestOptions.scoreBoardVisibleTime = '0';
    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.get('a[href="#ranking"]').click();
    cy.get('[data-table-scoreboard]').should('be.visible');
    cy.get('[data-table-scoreboard-username]').should('have.length', 1);
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(5)`).should(
      'contain',
      '0.00',
    );
    cy.logout();
  });

  it('Should keep the submissions tab active when opening run details as contest admin', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[1],
    );
    const contestant = [userLoginOptions[0].username];
    const contestAdmin = userLoginOptions[1];

    cy.login(contestAdmin);
    contestPage.createContest(contestOptions, contestant);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    cy.login(contestAdmin);
    cy.visit(`arena/${contestOptions.contestAlias}`);
    cy.get('a.nav-link[href="#runs"]').click();
    cy.intercept({ method: 'POST', url: '/api/run/details/' }).as('runDetails');
    cy.get('[data-runs-actions-button]').first().click();
    cy.get('[data-runs-show-details-button]').first().click();
    cy.wait('@runDetails').its('response.statusCode').should('eq', 200);
    cy.location('hash').should('match', /^#runs\/all\/show-run:[0-9a-f]+/);
    cy.location('hash').should('not.contain', '##runs');
    cy.get('a.nav-link[href="#runs"]').should('have.class', 'active');
    cy.get('[data-overlay]').should('exist');
    cy.get('[data-run-details-view]').should('exist');
    cy.get('[data-overlay-popup] button.close').click({ force: true });
    cy.logout();
  });

  it('Should disqualify and requalify the submission in the contest', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[1],
    );
    const contestant = [userLoginOptions[0].username];
    const contestAdmin = userLoginOptions[1];

    cy.login(contestAdmin);
    contestPage.createContest(contestOptions, contestant);
    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    cy.login(userLoginOptions[1]);

    cy.visit(`arena/${contestOptions.contestAlias}`);
    cy.get('a.nav-link[href="#runs"]').click();
    cy.get('a[problem-navigation-button]').click();
    cy.get('a.nav-link[href="#runs"]').click();
    cy.get('[data-runs-actions-button]').first().click();
    cy.get('[data-actions-disqualify]').should('be.visible').click();
    cy.get('td.numeric.status-disqualified').its('length').should('eq', 1);
    cy.get('[data-actions-requalify]').click({ force: true });
    cy.get('td.numeric.status-ac').its('length').should('eq', 1);
    cy.logout();
  });
});
