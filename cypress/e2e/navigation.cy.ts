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
    // Ensure the page is fully loaded
    cy.get('body').should('be.visible');
  });

  afterEach(() => {
    // Cleanup and error checking after each test
    cy.get('body').then(($body) => {
      if ($body.find('.error-message').length > 0) {
        cy.log('Error message found on page after test');
      }
    });
  });

  it('Should change password', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const newPassword = 'newP@55w0rd';

    cy.login(loginOptions[0])
      .should('not.throw')
      .then(() => {
        profilePage.changePassword(loginOptions[0].password, newPassword)
          .should('be.fulfilled');
      });

    cy.logout()
      .should('not.throw');

    loginOptions[0].password = newPassword;
    cy.login(loginOptions[0])
      .should('not.throw');
    cy.logout()
      .should('not.throw');
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

    cy.login(loginOptions[0])
      .should('not.throw');

    // Wait for profile form to be ready
    cy.get('form').should('exist').and('be.visible');
    
    profilePage.updateProfileInformation(userBasicInformation)
      .should('be.fulfilled');
    
    profilePage.verifyProfileInformation(userBasicInformation)
      .should('be.fulfilled');
    
    cy.logout()
      .should('not.throw');
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

    cy.login(loginOptions[0])
      .should('not.throw');

    // Wait for form and ensure country select is populated
    cy.get('form').should('exist').and('be.visible');
    cy.get('[data-country-select]').should('exist').and('be.visible');
    
    profilePage.updateProfileInformation(userBasicInformation)
      .should('be.fulfilled');
    
    // Check for warning with retry and timeout
    cy.get('[data-state-warning]', { timeout: 10000 })
      .should('be.visible')
      .and('contain.text', 'Please select a state');
    
    cy.logout()
      .should('not.throw');
  });

  it('Should update preferences', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const userPreferences: UserPreferences = {
      language: 'en',
      programmingLanguage: 'cpp17-gcc',
      useCase: 'learning',
      objective: 'competitive',
    };

    cy.login(loginOptions[0])
      .should('not.throw');

    profilePage.updatePreferences(userPreferences)
      .should('be.fulfilled');
    
    // Verify preferences were saved
    profilePage.verifyPreferences(userPreferences)
      .should('be.fulfilled');
    
    cy.logout()
      .should('not.throw');
  });

  it('Should update school', () => {
    const loginOptions = loginPage.registerMultipleUsers(1);
    const schoolDetails: SchoolDetails = {
      name: 'MIT',
      grade: 'bachelors',
      enrolledStatus: true,
    };

    cy.login(loginOptions[0])
      .should('not.throw');

    profilePage.updateSchoolDetails(schoolDetails)
      .should('be.fulfilled');
    
    profilePage.verifySchoolDetails(schoolDetails)
      .should('be.fulfilled');
    
    cy.logout()
      .should('not.throw');
  });
});