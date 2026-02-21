import { v4 as uuid } from 'uuid';
import {
  ContestOptions,
  GroupOptions,
  LoginOptions,
  TeamGroupOptions,
} from '../support/types';
import { DEFAULT_PASSWORD } from '../support/constants';
import { contestPage } from '../support/pageObjects/contestPage';
import { loginPage } from '../support/pageObjects/loginPage';
import { getISODateTime, addSubtractDateTime } from '../support/commands';
import { profilePage } from '../support/pageObjects/profilePage';
import { groupPage } from '../support/pageObjects/groupPage';

describe('Contest Test', () => {
  let virtualContestDetails: ContestOptions;
  let contestWithHalfTimeVisibleScoreboard: ContestOptions;
  let loginOptionstoVerify: LoginOptions;
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
    const milliseconds = 200 * 1000;
    let newEndDate = now;
    newEndDate = new Date(newEndDate.getTime() + milliseconds);
    contestOptions.endDate = newEndDate;
    identityLogin = {
      username:
        groupOptions.groupTitle.split('-').slice(0, -1).join('-') +
        ':identity_1',
      password: DEFAULT_PASSWORD,
    };

    loginPage.giveAdminPrivilege(
      'GroupIdentityCreator',
      loginOptions[0].username,
    );

    cy.login(loginOptions[0]);
    groupPage.createGroup(groupOptions);
    groupPage.addIdentitiesGroup();
    contestPage.setPasswordForIdentity(
      identityLogin.username,
      DEFAULT_PASSWORD,
    );
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

    contestOptions.scoreBoardVisibleTime = '50';
    const now = new Date();
    contestOptions.startDate = now;
    const milliseconds = 480 * 1000;
    let newEndDate = now;
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
    cy.get(`.${userLoginOptions[0].username} > td:nth-child(5)`).should(
      'contain',
      '+100.00',
    );
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

  it('Should create a contest and retrieve it', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(3);

    const groupOptions: GroupOptions = {
      groupTitle: 'ut_group_' + uuid(),
      groupDescription: 'group description',
    };

    loginPage.giveAdminPrivilege(
      'GroupIdentityCreator',
      userLoginOptions[0].username,
    );

    cy.login(userLoginOptions[0]);
    groupPage.createGroup(groupOptions);
    groupPage.addIdentitiesGroup();
    cy.logout();

    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[0],
    );

    const users = [userLoginOptions[1].username, userLoginOptions[2].username];
    cy.login(userLoginOptions[0]);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(userLoginOptions[1]);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    contestPage.updateScoreboardForContest(contestOptions.contestAlias);

    cy.login(userLoginOptions[0]);
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

  it('Should create a contest and review ranking', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(5);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[4],
    );

    const groupOptions: GroupOptions = {
      groupTitle: 'ut_group_' + uuid(),
      groupDescription: 'group description',
    };

    loginPage.giveAdminPrivilege(
      'GroupIdentityCreator',
      userLoginOptions[4].username,
    );

    cy.login(userLoginOptions[4]);
    groupPage.createGroup(groupOptions);
    groupPage.addIdentitiesGroup();
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
    const userLoginOptions = loginPage.registerMultipleUsers(2);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[1],
    );
    const now = new Date();
    const users = [userLoginOptions[0].username];

    contestOptions.startDate = now;
    contestOptions.endDate = addSubtractDateTime(now, { minutes: 5 });

    cy.login(userLoginOptions[1]);
    contestPage.createContest(contestOptions, users);

    // Update start time to 5 minutes ago and end time to 1 minute ago
    cy.get(`a[href="/contest/${contestOptions.contestAlias}/edit/"]`).click();
    cy.waitUntil(() =>
      cy
        .url()
        .should('include', `/contest/${contestOptions.contestAlias}/edit/`),
    );
    const startDate = addSubtractDateTime(now, { minutes: -5 });
    const endDate = addSubtractDateTime(now, { minutes: -1 });
    cy.get('[data-start-date]').type(getISODateTime(startDate));
    cy.get('[data-end-date]').type(getISODateTime(endDate));
    cy.get('button[type="submit"]').click();

    cy.logout();

    cy.login(userLoginOptions[0]);
    cy.visit(`/arena/${contestOptions.contestAlias}/`);
    cy.get('[data-start-contest]').click();
    cy.get(`a[href="/arena/${contestOptions.contestAlias}/practice/"]`)
      .click()
      .then(() => {
        const newContestAlias = contestOptions.contestAlias + '/practice';
        contestOptions.contestAlias = newContestAlias;
        // Wait for the practice mode page to fully load before creating runs
        cy.waitUntil(() => cy.url().should('include', newContestAlias));
        // Add additional wait for the page to be fully interactive
        cy.wait(1000);
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
      const userLoginOptions = loginPage.registerMultipleUsers(3);
      const contestOptions = contestPage.generateContestOptions(
        userLoginOptions[2],
      );
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
      cy.get(`.${userLoginOptions[0].username} > td:nth-child(5)`).should(
        'contain',
        '+100.00',
      );
      cy.get(`.${userLoginOptions[1].username} > td:nth-child(5)`).should(
        'contain',
        '-',
      );
      cy.logout();
    },
  );

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

  it('Should disqualify submissions in batch', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(5);
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[4],
      true,
      2,
    );

    const users: Array<string> = [];
    const contestAdmin = userLoginOptions[4];
    const contestant1 = userLoginOptions[0];
    const contestant2 = userLoginOptions[2];
    userLoginOptions.forEach((loginDetails) => {
      users.push(loginDetails.username);
    });

    cy.login(contestAdmin);
    contestPage.createContest(contestOptions, users);
    cy.logout();

    cy.login(contestant1);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    contestOptions.statusCheck = true;
    cy.login(contestant2);
    cy.enterContest(contestOptions);
    cy.createRunsInsideContest(contestOptions);
    cy.logout();

    cy.login(contestAdmin);
    cy.visit(`arena/${contestOptions.contestAlias}`);
    cy.get('a.nav-link[href="#runs"]').click();
    cy.get('[data-runs-actions-button]').first().click();
    cy.get('[data-actions-disqualify-by-user]').first().click();
    // 2 submissions should be disqualified
    cy.get('.status-disqualified').its('length').should('eq', 2);
    cy.logout();
  });

  it('Should Reduge the submission in the contest', () => {
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
    cy.get('[data-runs-actions-button]').first().click();
    cy.get('[data-actions-rejudge]').should('be.visible').click();
    cy.get('a[problem-navigation-button]').click();
    cy.get('a.nav-link[href="#runs"]').click();
    cy.get('[data-runs-actions-button]').first().click();
    cy.get('[data-actions-rejudge]').should('be.visible').click();
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

  it('Should create and update a contest for teams', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(1);
    const groupTitle = 'ut_teamgroup_' + uuid();
    const contestOptions = contestPage.generateContestOptions(
      userLoginOptions[0],
      true,
      1,
      true,
      groupTitle,
    );
    const teamGroupOptions: TeamGroupOptions = {
      groupTitle,
      groupDescription: 'group description',
      noOfContestants: '2',
    };

    cy.login(userLoginOptions[0]);
    groupPage.createTeamGroup(teamGroupOptions);
    cy.visit('/');
    contestPage.createContest(contestOptions, []);

    // Update contest title and description
    cy.waitUntil(() =>
      cy
        .url()
        .should('include', `/contest/${contestOptions.contestAlias}/edit/`),
    );
    const newContestTitle = 'New Contest Title';
    const newContestDescription = 'New Contest Description';
    cy.get('[data-title]').clear().type(newContestTitle);
    cy.get('[data-description]').clear().type(newContestDescription);
    cy.get('button[type="submit"]').click();

    cy.get('[name="title"]').should('have.value', newContestTitle);
    cy.get('[name="description"]').should('have.value', newContestDescription);
    cy.logout();
  });
});
