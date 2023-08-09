import { getISODate } from '../support/commands';
import { loginPage } from '../support/pageObjects/loginPage';
import { profilePage } from '../support/pageObjects/profilePage';
import { UserInformation, UserPreferences } from '../support/types';

describe('Group Test', () => {
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
      gender: 'Male',
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
      language: 'English',
      programmingLanguage: 'C++17 (g++ 10.3)',
      useCase: 'learn programming',
      objective: 'school classes',
    };

    cy.login(loginOptions[0]);
    profilePage.updatePreferences(userPreferences);
    cy.logout();
  });
});
