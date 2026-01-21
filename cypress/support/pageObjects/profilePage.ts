import {
  LoginOptions,
  SchoolDetails,
  UserInformation,
  UserPreferences,
} from '../types';

export class ProfilePage {
  addUsername(userName: string): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-basic-information"]').click();
    cy.get('[data-name]').type(userName);
    cy.get('[data-save-profile-changes-button]').click();
    // Wait for success notification and dismiss it
    cy.get('.alert[role="alert"]')
      .should('be.visible')
      .find('[data-alert-close]')
      .click();
  }
  updatePreferredLanguage(preferredLanguage: string): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-preferences"]').click();
    cy.get('[data-preference-language]').select(preferredLanguage);
    cy.get('[data-preference-save-button]').click();
  }
  updatePreferredProgrammingLanguage(preferredLanguage: string): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-preferences"]').click();
    cy.get('[data-preferred-language]').select(preferredLanguage);
    cy.get('[data-preference-save-button]').click();
  }

  navigateToMyProblemsPage(): void {
    cy.get('[data-nav-user]').click();
    cy.get('a[href="/profile/#created-content"]').click();
    cy.get('a[href="/problem/mine/"]').click();
  }

  verifyProblemIsVisible(problemAlias: string): void {
    cy.get(`a[href="/arena/problem/${problemAlias}/"]`).should('be.visible');
  }

  verifyProblemIsNotVisible(problemAlias: string): void {
    cy.get(`a[href="/arena/problem/${problemAlias}/"]`).should('not.exist');
  }

  deleteProblem(problemAlias: string): void {
    cy.get(`[data-delete-problem="${problemAlias}"]`).click();
    cy.get('.modal-footer>button.btn-danger').click();
  }

  deleteProblemsInBatch(problemAliases: string[]): void {
    problemAliases.forEach((problemAlias) => {
      cy.get(
        `input[type="checkbox"][data-selected-problem="${problemAlias}"]`,
      ).click();
    });
    cy.get('select[data-selected-problems]').select('2');
    cy.get('[data-visibility-action]').click();
    cy.get('.modal-footer>button.btn-danger').click();
  }

  changePassword(oldPassword: string, newPassword: string): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#change-password"]').click();
    cy.get('[data-old-password]').type(oldPassword);
    cy.get('[data-new-password]').type(newPassword);
    cy.get('[data-new-password2]').type(newPassword);
    cy.get('[data-save-changed-password]').click();
  }

  updateProfileInformation(userBasicInformation: UserInformation): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-basic-information"]').click();
    cy.get('[data-name]').type(userBasicInformation.name);
    cy.get('[data-gender]').select(userBasicInformation.gender);
    cy.get('[data-countries]').select(userBasicInformation.country);
    cy.get('[data-states]').select(userBasicInformation.state);
    cy.get('[data-date-of-birth]').type(userBasicInformation.dateOfBirth);
    cy.get('[data-save-profile-changes-button]').click();
  }

  verifyProfileInformation(userBasicInformation: UserInformation): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="#data"]').click();
    cy.get('[data-user-name]').should('contain', userBasicInformation.name);
    cy.get('[data-user-country]').should(
      'contain',
      userBasicInformation.country,
    );
    cy.get('[data-user-state]').should('contain', userBasicInformation.state);
  }

  updatePreferences(userPreferences: UserPreferences): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-preferences"]').click();
    cy.get('[data-preference-language]').select(userPreferences.language);
    cy.get('[data-preferred-language]').select(
      userPreferences.programmingLanguage,
    );
    cy.get('[data-learning-teaching-objective]').select(
      userPreferences.useCase,
    );
    cy.get('[data-scholar-competitive-objective]').select(
      userPreferences.objective,
    );
    cy.get('[data-preference-save-button]').click();
    cy.get('[data-preferred-programming-languages]').should('contain', 'C++17');
  }

  updateSchoolDetails(schoolDetails: SchoolDetails): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#manage-schools"]').click();
    cy.get('[data-school-name]').click();
    cy.get('[data-school-name]').type(schoolDetails.name);
    cy.get('.tags-input-typeahead-item-highlighted-default').first().click();
    cy.get('[data-school-grade]').select(schoolDetails.grade);
    if (schoolDetails.enrolledStatus) {
      cy.get('[type="radio"]').check('true');
    } else {
      cy.get('[type="radio"]').check('false');
      cy.get('[data-graduation-date]').type(
        schoolDetails.graduationDate || '01/01/2001',
      );
    }
    cy.get('[data-save-school-changes]').click();
  }

  verifySchoolDetails(schoolDetails: SchoolDetails): void {
    cy.reload();
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="#data"]').click();
    cy.get('[data-user-school]').should('contain', schoolDetails.name);
    if (schoolDetails.graduationDate != undefined) {
      cy.get('[data-graduation-date]').should(
        'contain',
        schoolDetails.graduationDate,
      );
    }
  }

  mergeIdentities(identityLogin: LoginOptions): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#manage-identities"]').click();
    cy.get('[data-identity-username]').type(identityLogin.username);
    cy.get('[data-identity-password]').type(identityLogin.password);
    cy.get('[data-add-identity-button]').click();

    // Wait for success notification and dismiss it
    cy.get('.alert[role="alert"]')
      .should('be.visible')
      .find('[data-alert-close]')
      .click();

    cy.get('[data-added-identity-username]').should('have.length', 2);
    cy.get('[data-added-identity-username]')
      .first()
      .should('contain', identityLogin.username);
    cy.reload();
  }

  changeIdentity(username: string): void {
    cy.get('[data-nav-user]').click();
    cy.get('button').contains(username).click();
  }
}

export const profilePage = new ProfilePage();
