import { v4 as uuid } from 'uuid';
import {
  ContestOptions,
  GroupOptions,
  LoginOptions
} from '../support/types';
import { addIdentitiesGroup, createGroup } from '../support/utils/common'
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

        const userLoginOptions: LoginOptions = {
          username: 'utGroup_' + uuid(),
          password: 'P@55w0rd',
        };

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
            },
            {
              problemAlias: 'sumas',
              fixturePath: 'main.cpp',
              language: 'cpp11-gcc',
              valid: false,
            },
          ]
        };
        
        cy.createContest(contestOptions);

        cy.location('href').should('include', contestOptions.contestAlias);
        cy.get('[name="title"]').should('have.value', contestOptions.contestAlias);
        cy.get('[name="alias"]').should('have.value', contestOptions.contestAlias);
        cy.get('[name="description"]').should(
          'have.value',
          contestOptions.description,
        );

        cy.addProblemsToContest(contestOptions);
        cy.changeAdmissionModeContest(contestOptions);
        cy.register(userLoginOptions);
        cy.logout();

    });

});
