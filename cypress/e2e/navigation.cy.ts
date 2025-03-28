import { getISODate } from '../support/commands';
import { loginPage } from '../support/pageObjects/loginPage';
import { profilePage } from '../support/pageObjects/profilePage';
import {
  SchoolDetails,
  UserInformation,
  UserPreferences,
} from '../support/types';

describe('Navigation Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  it('Should change password', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const newPassword = 'newP@55w0rd';

    cy.login(loginOptions[0]);
    profilePage.changePassword(loginOptions[0].password, newPassword);
    cy.logout();

    loginOptions[0].password = newPassword;
    cy.login(loginOptions[0]);
    cy.logout();
  });

  it('Should update basic profile information', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const userBasicInformation: UserInformation = {
      name: 'Test User',
      gender: 'male',
      country: 'Mexico',
      state: 'Colima',
      dateOfBirth: getISODate(
        new Date(new Date().setFullYear(new Date().getFullYear() - 20)),
      ),
    };

    cy.login(loginOptions[0]);
    profilePage.updateProfileInformation(userBasicInformation);
    profilePage.verifyProfileInformation(userBasicInformation);
    cy.logout();
  });

  it('Should update preferences', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const userPreferences: UserPreferences = {
      language: 'en',
      programmingLanguage: 'cpp17-gcc',
      useCase: 'learning',
      objective: 'competitive',
    };

    cy.login(loginOptions[0]);
    profilePage.updatePreferences(userPreferences);
    cy.logout();
  });

  it('Should update school', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const schoolDetails: SchoolDetails = {
      name: 'MIT',
      grade: 'bachelors',
      enrolledStatus: true,
    };

    cy.login(loginOptions[0]);
    profilePage.updateSchoolDetails(schoolDetails);
    profilePage.verifySchoolDetails(schoolDetails);
    cy.logout();
  });

  it('Should navigate to enrolled contests and verify filter', () => {
    const userLoginOptions = loginPage.registerMultipleUsers(1);
    cy.login(userLoginOptions[0]);

    cy.get('[data-nav-user]').should('be.visible');
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-user-contests-enrolled]').should('be.visible');

    cy.get('[data-nav-user-contests-enrolled]').click();

    cy.waitUntil(() =>
      cy
        .url()
        .should(
          'include',
          '/arena/?page=1&tab_name=current&sort_order=none&filter=signedup',
        ),
    );

    cy.get('[data-dropdown-filter]>button').click();

    cy.get('.dropdown-menu.show').should('be.visible');

    cy.get('[data-filter-by-signed-up].dropdown-item')
      .should('exist')
      .and('be.visible');

    cy.get('[data-filter-by-signed-up].dropdown-item svg.fa-check').should(
      'exist',
    );
  });
});
