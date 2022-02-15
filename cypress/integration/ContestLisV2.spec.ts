import { v4 as uuid } from 'uuid';
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

  it('Should land on ArenaV2 Page', function () {
    cy.visit('http://127.0.0.1:8001/arenav2');
    cy.get('.title').should('be.visible');
  });

  it('Should change tabs on ArenaV2 Page', function () {
    cy.visit('http://127.0.0.1:8001/arenav2');
    cy.get('a[id=__BVID__27___BV_tab_button__]').should('have.class', 'active');
    cy.get('a[id=__BVID__29___BV_tab_button__]').click();
    cy.get('a[id=__BVID__29___BV_tab_button__]').should('have.class', 'active');
    cy.get('a[id=__BVID__31___BV_tab_button__]').click();
    cy.get('a[id=__BVID__31___BV_tab_button__]').should('have.class', 'active');
  });

  const today = new Date();
  const daySeconds = 24 * 60 * 60 * 1000;
  const yesterday = new Date(today.getTime() - daySeconds);
  const tomorrow = new Date(today.getTime() + daySeconds);

  it('Should show current contest', function () {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);
    const contestAlias = 'contest' + uuid().slice(0, 5);
    const contestOptions: ContestOptions = {
      contestAlias: contestAlias,
      description: 'Test Description',
      startDate: yesterday,
      endDate: tomorrow,
      showScoreboard: true,
      basicInformation: false,
      partialPoints: true,
      requestParticipantInformation: 'no',
    };
    cy.createContest(contestOptions);
    cy.visit('http://127.0.0.1:8001/arenav2');
  });

  it('Should show future contest', function () {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);
    const contestAlias = 'contest' + uuid().slice(0, 5);
    const contestOptions: ContestOptions = {
      contestAlias: contestAlias,
      description: 'Test Description',
      startDate: tomorrow,
      endDate: new Date(tomorrow.getTime() + daySeconds),
      showScoreboard: true,
      basicInformation: false,
      partialPoints: true,
      requestParticipantInformation: 'no',
    };
    cy.createContest(contestOptions);
    cy.visit('http://127.0.0.1:8001/arenav2');
    cy.get('a[__BVID__69___BV_tab_button__]').click();
    cy.get('#__BVID__69 > :nth-child(1) > .card-body > .container > :nth-child(1) > :nth-child(1) > .card-text > h5 > a').should('have.text', contestAlias);
  });

  it('Should show past contest', function () {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);
    const contestAlias = 'contest' + uuid().slice(0, 5);
    const contestOptions: ContestOptions = {
      contestAlias: contestAlias,
      description: 'Test Description',
      startDate: new Date(yesterday.getTime() - daySeconds),
      endDate: new Date(yesterday.getTime() - daySeconds * 2),
      showScoreboard: true,
      basicInformation: false,
      partialPoints: true,
      requestParticipantInformation: 'no',
    };
    cy.createContest(contestOptions);
    cy.visit('http://127.0.0.1:8001/arenav2');
  });
});