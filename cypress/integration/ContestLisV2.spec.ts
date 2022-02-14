import { v4 as uuid } from 'uuid';
import {
    addDaysToTodaysDate,
  } from '../support/commands';
import {
  ContestOptions,
  LoginOptions,
} from '../support/types';

describe('Basic ContestListv2 Tests', () => {
    beforeEach(() => {
      cy.clearCookies();
      cy.clearLocalStorage();
      cy.visit('/');
    });

    it('Should land on ArenaV2 Page', function() {
        cy.visit('http://127.0.0.1:8001/arenav2');
        cy.get('.title').should('be.visible');
      });

    it('Should change tabs on ArenaV2 Page', function() {
        cy.visit('http://127.0.0.1:8001/arenav2');
        cy.get('#__BVID__27___BV_tab_button__').should('have.class', 'active');
        cy.get('#__BVID__29___BV_tab_button__').click();
        cy.get('#__BVID__29___BV_tab_button__').should('have.class', 'active');
        cy.get('#__BVID__31___BV_tab_button__').click();
        cy.get('#__BVID__31___BV_tab_button__').should('have.class', 'active');
    });

    it('Should show future contest', function() {
        const loginOptions: LoginOptions = {
           username: 'user',
           password: 'user',
        };
        cy.login(loginOptions);
        const contestOptions: ContestOptions = {
            contestAlias: 'contest' + uuid().slice(0, 5),
            description: 'Test Description',
            startDate: new Date(),
            endDate: addDaysToTodaysDate({days: 2}),
            showScoreboard: true,
            basicInformation: false,
            partialPoints: true,
            requestParticipantInformation: 'no',
        };
        cy.createContest(contestOptions);
        cy.visit('http://127.0.0.1:8001/arenav2');
        /* ==== Generated with Cypress Studio ==== */
        cy.get('#__BVID__33___BV_tab_button__').click();
        cy.get('#__BVID__33 > :nth-child(1) > .card-body > .container > :nth-child(1) > :nth-child(1) > .card-text > h5 > a').should('be.visible');
        /* ==== End Cypress Studio ==== */
    });
});