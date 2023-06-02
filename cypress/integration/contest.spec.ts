import 'cypress-wait-until';
import { v4 as uuid } from 'uuid';
import {
  ContestOptions,
  GroupOptions,
  LoginOptions
} from '../support/types';
import { addIdentitiesGroup, addStudentsBulk, createClarificationUser, createGroup, updateScoreboardForContest } from '../support/utils/common'
import { addSubtractDaysToDate } from '../support/commands';

describe('Contest Test', () => {
    beforeEach(() => {
      cy.clearCookies();
      cy.clearLocalStorage();
      cy.visit('/');
    });

    it('Should create a contest and retrieve it', () => {
        const adminLoginOptions: LoginOptions = {
          username: 'omegaup',
          password: 'omegaup',
        };

        const groupOptions: GroupOptions = {
          groupTitle: 'ut_group_' + uuid(),
          groupDescription: 'group description', 
        };

        cy.login(adminLoginOptions);

        createGroup(groupOptions);
        addIdentitiesGroup();

        const userLoginOptions1: LoginOptions = {
          username: 'utGroup_' + uuid(),
          password: 'P@55w0rd',
        };
        cy.register(userLoginOptions1);
        cy.logout();

        const userLoginOptions2: LoginOptions = {
          username: 'utGroup_' + uuid(),
          password: 'P@55w0rd',
        };
        cy.register(userLoginOptions2);
        cy.logout();

        const now = new Date();

        enum ScoreMode {
          AllOrNothing = 'all_or_nothing',
          Partial = 'partial',
          MaxPerGroup = 'max_per_group',
        }
  
        const contestOptions: ContestOptions = {
          contestAlias: 'contest' + uuid().slice(0, 5),
          description: 'Test Description',
          startDate: addSubtractDaysToDate(now, {days: -1}),
          endDate: addSubtractDaysToDate(now, {days: 2}),
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
              status: 'AC'
            }
          ]
        };
        
        cy.login(adminLoginOptions);
        cy.createContest(contestOptions);

        cy.location('href').should('include', contestOptions.contestAlias);
        cy.get('[name="title"]').should('have.value', contestOptions.contestAlias);
        cy.get('[name="alias"]').should('have.value', contestOptions.contestAlias);
        cy.get('[name="description"]').should(
          'have.value',
          contestOptions.description,
        );

        cy.addProblemsToContest(contestOptions);
        const users = [userLoginOptions1.username, userLoginOptions2.username];
        addStudentsBulk(users);
        cy.changeAdmissionModeContest(contestOptions);
        
        cy.get('a[data-contest-link-button]').click();
        cy.url().should('include', '/arena/' + contestOptions.contestAlias);
        
        cy.get('a[href="#ranking"]').click();
        cy.waitUntil(() =>
          cy.get('.omegaup-scoreboard').should('be.visible')
        );
        cy.logout();

        cy.login(userLoginOptions1);
        cy.enterContest(contestOptions);
        cy.createRunsInsideContest(contestOptions);
        cy.pause();
        cy.logout();

        cy.login(userLoginOptions2);
        cy.enterContest(contestOptions);
        contestOptions.runs[0].fixturePath = 'main_wrong.cpp';
        contestOptions.runs[0].status = 'PA';
        cy.createRunsInsideContest(contestOptions);
        cy.pause();
        cy.logout();

        // updateScoreboardForContest(contestOptions.contestAlias);
        
        cy.login(adminLoginOptions);
        cy.get('a[data-nav-user]').click();
        cy.get('a[data-nav-user-contests]').click();

        cy.pause();


    });

});
