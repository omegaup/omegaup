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

  it('Should display warning if state is not selected', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const userBasicInformation: UserInformation = {
      name: 'Test User',
      gender: 'male',
      country: 'Mexico',
      state: '',
      dateOfBirth: getISODate(
        new Date(new Date().setFullYear(new Date().getFullYear() - 20)),
      ),
    };

    cy.login(loginOptions[0]);
    profilePage.updateProfileInformation(userBasicInformation);
    cy.get('[data-state-warning]').should('be.visible');
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
});
