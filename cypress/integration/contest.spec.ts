import { v4 as uuid } from 'uuid';
import {
  GroupOptions,
} from '../support/types';
import {  addIdentitiesGroup, createClarificationUser, createContestAdmin, createGroup, generateContestOptions, registerMultileUsers, updateScoreboardForContest } from '../support/utils/common'

describe('Contest Test', () => {
    beforeEach(() => {
      cy.clearCookies();
      cy.clearLocalStorage();
      cy.visit('/');
    });

    it('Should create a contest and retrieve it', () => {
      const userLoginOptions = registerMultileUsers(2);

      const groupOptions: GroupOptions = {
        groupTitle: 'ut_group_' + uuid(),
        groupDescription: 'group description', 
      };

      cy.loginAdmin();
      createGroup(groupOptions);
      addIdentitiesGroup();
      cy.logout();

      const contestOptions = generateContestOptions();

      const users = [userLoginOptions[0].username, userLoginOptions[1].username];
      createContestAdmin(contestOptions, users);

      cy.login(userLoginOptions[0]);
      cy.enterContest(contestOptions);
      cy.createRunsInsideContest(contestOptions);
      cy.logout();

      updateScoreboardForContest(contestOptions.contestAlias);
      
      cy.loginAdmin();
      cy.get('a[data-nav-contests]').click();
      cy.get('a[data-nav-contests-arena]').click();
      cy.get(`a[href="/arena/${contestOptions.contestAlias}/"]`).first().click();
      cy.get('a[href="#ranking"]').click();
      cy.get('[data-table-scoreboard-username]').first().should('contain', userLoginOptions[0].username);
      cy.get('[data-table-scoreboard-username]').last().should('contain', userLoginOptions[1].username);
      cy.logout();
    });

    it('Should create a contest and add a clarification.', () => {
      const contestOptions = generateContestOptions();
      const userLoginOptions = registerMultileUsers(1);

      createContestAdmin(contestOptions, [userLoginOptions[0].username]);

      cy.login(userLoginOptions[0]);
      cy.enterContest(contestOptions);
      createClarificationUser(contestOptions, 'Question 1');
      cy.logout();

      cy.loginAdmin();
      cy.get('a[data-nav-contests]').click();
      cy.get('a[data-nav-contests-arena]').click();
      cy.get(`a[href="/arena/${contestOptions.contestAlias}/"]`).first().click();
      cy.get('a[href="#clarifications"]').click();
      cy.get('[data-tab-clarifications]').should('be.visible')
      cy.get('[data-select-answer]').select('No');
      cy.get('[data-form-clarification-answer]').submit();
      cy.get('[data-form-clarification-resolved-answer]').should('contain', 'No');
      cy.logout();
    });
    
    it.only('Should create a contest and reviewing ranking', () => {
      const contestOptions = generateContestOptions();
      const userLoginOptions = registerMultileUsers(4);
  
      const groupOptions: GroupOptions = {
        groupTitle: 'ut_group_' + uuid(),
        groupDescription: 'group description', 
      };

      cy.loginAdmin();
      createGroup(groupOptions);
      addIdentitiesGroup();
      cy.logout();

      const users: Array<string> = [];
      userLoginOptions.forEach((loginDetails) => {
        users.push(loginDetails.username);
      });

      createContestAdmin(contestOptions, users);

      cy.login(userLoginOptions[0]);
      cy.enterContest(contestOptions);
      cy.createRunsInsideContest(contestOptions);
      cy.logout();

      cy.login(userLoginOptions[2]);
      cy.enterContest(contestOptions);
      cy.createRunsInsideContest(contestOptions);
      cy.logout();

      updateScoreboardForContest(contestOptions.contestAlias);

      cy.loginAdmin();
      cy.get('a[data-nav-contests]').click();
      cy.get('a[data-nav-contests-arena]').click();
      cy.get(`a[href="/arena/${contestOptions.contestAlias}/"]`).first().click();
      cy.get('a[href="#ranking"]').click();
      cy.pause();
    });

});
