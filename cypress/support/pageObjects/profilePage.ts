import {
  LoginOptions,
  SchoolDetails,
  UserInformation,
  UserPreferences,
} from '../types';

export class ProfilePage {
  private readonly selectors = {
    nav: {
      user: '[data-nav-user]',
      profile: '[data-nav-profile]',
    },
    links: {
      basicInfo: 'a[href="/profile/#edit-basic-information"]',
      preferences: 'a[href="/profile/#edit-preferences"]',
      createdContent: 'a[href="/profile/#created-content"]',
      myProblems: 'a[href="/problem/mine/"]',
      data: 'a[href="#data"]',
      changePassword: 'a[href="/profile/#change-password"]',
      manageSchools: 'a[href="/profile/#manage-schools"]',
      manageIdentities: 'a[href="/profile/#manage-identities"]',
    },
    profile: {
      name: '[data-name]',
      gender: '[data-gender]',
      countries: '[data-countries]',
      states: '[data-states]',
      dateOfBirth: '[data-date-of-birth]',
      saveButton: '[data-save-profile-changes-button]',
      stateWarning: '[data-state-warning]',
      alertClose: '#alert-close',
    },
    preferences: {
      language: '[data-preference-language]',
      programmingLanguage: '[data-preferred-language]',
      useCase: '[data-learning-teaching-objective]',
      objective: '[data-scholar-competitive-objective]',
      saveButton: '[data-preference-save-button]',
      preferredLanguages: '[data-preferred-programming-languages]',
    },
    school: {
      name: '[data-school-name]',
      grade: '[data-school-grade]',
      enrollmentStatus: '[type="radio"]',
      graduationDate: '[data-graduation-date]',
      saveButton: '[data-save-school-changes]',
      schoolSuggestion: '.tags-input-typeahead-item-highlighted-default',
    },
    user: {
      name: '[data-user-name]',
      country: '[data-user-country]',
      state: '[data-user-state]',
      school: '[data-user-school]',
    },
    password: {
      old: '[data-old-password]',
      new: '[data-new-password]',
      confirm: '[data-new-password2]',
      saveButton: '[data-save-changed-password]',
    },
    identity: {
      username: '[data-identity-username]',
      password: '[data-identity-password]',
      addButton: '[data-add-identity-button]',
      addedUsername: '[data-added-identity-username]',
    },
  };

  private async navigateToProfile(section: keyof typeof this.selectors.links): Promise<void> {
    try {
      cy.get(this.selectors.nav.user).should('be.visible').click();
      cy.get(this.selectors.nav.profile).should('be.visible').click();
      cy.get(this.selectors.links[section]).should('be.visible').click();
    } catch (error) {
      cy.log(`Error navigating to profile section ${section}:`, error);
      throw error;
    }
  }

  async addUsername(userName: string): Promise<void> {
    try {
      await this.navigateToProfile('basicInfo');
      cy.get(this.selectors.profile.name).should('be.visible').type(userName);
      cy.get(this.selectors.profile.saveButton).should('be.visible').click();
      cy.get(this.selectors.profile.alertClose).should('be.visible').click();
    } catch (error) {
      cy.log('Error adding username:', error);
      throw error;
    }
  }

  async updatePreferredLanguage(preferredLanguage: string): Promise<void> {
    try {
      await this.navigateToProfile('preferences');
      cy.get(this.selectors.preferences.language)
        .should('be.visible')
        .select(preferredLanguage);
      cy.get(this.selectors.preferences.saveButton).should('be.visible').click();
    } catch (error) {
      cy.log('Error updating preferred language:', error);
      throw error;
    }
  }

  async updatePreferredProgrammingLanguage(preferredLanguage: string): Promise<void> {
    try {
      await this.navigateToProfile('preferences');
      cy.get(this.selectors.preferences.programmingLanguage)
        .should('be.visible')
        .select(preferredLanguage);
      cy.get(this.selectors.preferences.saveButton).should('be.visible').click();
    } catch (error) {
      cy.log('Error updating preferred programming language:', error);
      throw error;
    }
  }

  async navigateToMyProblemsPage(): Promise<void> {
    try {
      cy.get(this.selectors.nav.user).should('be.visible').click();
      cy.get(this.selectors.links.createdContent).should('be.visible').click();
      cy.get(this.selectors.links.myProblems).should('be.visible').click();
    } catch (error) {
      cy.log('Error navigating to my problems page:', error);
      throw error;
    }
  }

  async verifyProblemIsVisible(problemAlias: string): Promise<void> {
    try {
      cy.get(`a[href="/arena/problem/${problemAlias}/"]`)
        .should('be.visible')
        .should('exist');
    } catch (error) {
      cy.log(`Error verifying problem visibility for ${problemAlias}:`, error);
      throw error;
    }
  }

  async verifyProblemIsNotVisible(problemAlias: string): Promise<void> {
    try {
      cy.get(`a[href="/arena/problem/${problemAlias}/"]`).should('not.exist');
    } catch (error) {
      cy.log(`Error verifying problem non-visibility for ${problemAlias}:`, error);
      throw error;
    }
  }

  async deleteProblem(problemAlias: string): Promise<void> {
    try {
      cy.get(`[data-delete-problem="${problemAlias}"]`).should('be.visible').click();
      cy.get('.modal-footer>button.btn-danger').should('be.visible').click();
    } catch (error) {
      cy.log(`Error deleting problem ${problemAlias}:`, error);
      throw error;
    }
  }

  async deleteProblemsInBatch(problemAliases: string[]): Promise<void> {
    try {
      problemAliases.forEach((problemAlias) => {
        cy.get(`input[type="checkbox"][data-selected-problem="${problemAlias}"]`)
          .should('be.visible')
          .click();
      });
      cy.get('select[data-selected-problems]').should('be.visible').select('2');
      cy.get('[data-visibility-action]').should('be.visible').click();
      cy.get('.modal-footer>button.btn-danger').should('be.visible').click();
    } catch (error) {
      cy.log('Error deleting problems in batch:', error);
      throw error;
    }
  }

  async changePassword(oldPassword: string, newPassword: string): Promise<void> {
    try {
      await this.navigateToProfile('changePassword');
      cy.get(this.selectors.password.old).should('be.visible').type(oldPassword);
      cy.get(this.selectors.password.new).should('be.visible').type(newPassword);
      cy.get(this.selectors.password.confirm).should('be.visible').type(newPassword);
      cy.get(this.selectors.password.saveButton).should('be.visible').click();
    } catch (error) {
      cy.log('Error changing password:', error);
      throw error;
    }
  }

  async updateProfileInformation(userBasicInformation: UserInformation): Promise<void> {
    try {
      await this.navigateToProfile('basicInfo');
      
      cy.get(this.selectors.profile.name)
        .should('be.visible')
        .clear()
        .type(userBasicInformation.name);
      
      cy.get(this.selectors.profile.gender)
        .should('be.visible')
        .select(userBasicInformation.gender);
      
      cy.get(this.selectors.profile.countries)
        .should('be.visible')
        .select(userBasicInformation.country);

      // Wait for states to load after country selection
      cy.wait(1000);
      
      if (userBasicInformation.state) {
        cy.get(this.selectors.profile.states)
          .should('be.visible')
          .select(userBasicInformation.state);
      }
      
      cy.get(this.selectors.profile.dateOfBirth)
        .should('be.visible')
        .clear()
        .type(userBasicInformation.dateOfBirth);
      
      cy.get(this.selectors.profile.saveButton)
        .should('be.visible')
        .click();

      if (userBasicInformation.state === '') {
        cy.get(this.selectors.profile.stateWarning)
          .should('be.visible')
          .should('exist');
      }
    } catch (error) {
      cy.log('Error updating profile information:', error);
      throw error;
    }
  }

  async verifyProfileInformation(userBasicInformation: UserInformation): Promise<void> {
    try {
      await this.navigateToProfile('data');
      cy.get(this.selectors.user.name).should('contain', userBasicInformation.name);
      cy.get(this.selectors.user.country).should('contain', userBasicInformation.country);
      cy.get(this.selectors.user.state).should('contain', userBasicInformation.state);
    } catch (error) {
      cy.log('Error verifying profile information:', error);
      throw error;
    }
  }

  async updatePreferences(userPreferences: UserPreferences): Promise<void> {
    try {
      await this.navigateToProfile('preferences');
      
      cy.get(this.selectors.preferences.language)
        .should('be.visible')
        .select(userPreferences.language);
      
      cy.get(this.selectors.preferences.programmingLanguage)
        .should('be.visible')
        .select(userPreferences.programmingLanguage);
      
      cy.get(this.selectors.preferences.useCase)
        .should('be.visible')
        .select(userPreferences.useCase);
      
      cy.get(this.selectors.preferences.objective)
        .should('be.visible')
        .select(userPreferences.objective);
      
      cy.get(this.selectors.preferences.saveButton)
        .should('be.visible')
        .click();
      
      cy.get(this.selectors.preferences.preferredLanguages)
        .should('contain', 'C++17');
    } catch (error) {
      cy.log('Error updating preferences:', error);
      throw error;
    }
  }

  async updateSchoolDetails(schoolDetails: SchoolDetails): Promise<void> {
    try {
      await this.navigateToProfile('manageSchools');
      
      cy.get(this.selectors.school.name)
        .should('be.visible')
        .click()
        .type(schoolDetails.name);
      
      cy.get(this.selectors.school.schoolSuggestion)
        .first()
        .should('be.visible')
        .click();
      
      cy.get(this.selectors.school.grade)
        .should('be.visible')
        .select(schoolDetails.grade);
      
      if (schoolDetails.enrolledStatus) {
        cy.get(this.selectors.school.enrollmentStatus).check('true');
      } else {
        cy.get(this.selectors.school.enrollmentStatus).check('false');
        cy.get(this.selectors.school.graduationDate)
          .should('be.visible')
          .type(schoolDetails.graduationDate || '01/01/2001');
      }
      
      cy.get(this.selectors.school.saveButton)
        .should('be.visible')
        .click();
    } catch (error) {
      cy.log('Error updating school details:', error);
      throw error;
    }
  }

  async verifySchoolDetails(schoolDetails: SchoolDetails): Promise<void> {
    try {
      cy.reload();
      await this.navigateToProfile('data');
      cy.get(this.selectors.user.school).should('contain', schoolDetails.name);
      if (schoolDetails.graduationDate) {
        cy.get(this.selectors.school.graduationDate)
          .should('contain', schoolDetails.graduationDate);
      }
    } catch (error) {
      cy.log('Error verifying school details:', error);
      throw error;
    }
  }

  async mergeIdentities(identityLogin: LoginOptions): Promise<void> {
    try {
      await this.navigateToProfile('manageIdentities');
      
      cy.get(this.selectors.identity.username)
        .should('be.visible')
        .type(identityLogin.username);
      
      cy.get(this.selectors.identity.password)
        .should('be.visible')
        .type(identityLogin.password);
      
      cy.get(this.selectors.identity.addButton)
        .should('be.visible')
        .click();

      cy.get(this.selectors.identity.addedUsername)
        .should('have.length', 2)
        .first()
        .should('contain', identityLogin.username);
      
      cy.reload();
    } catch (error) {
      cy.log('Error merging identities:', error);
      throw error;
    }
  }

  async changeIdentity(username: string): Promise<void> {
    try {
      cy.get(this.selectors.nav.user).should('be.visible').click();
      cy.get('button').contains(username).should('be.visible').click();
    } catch (error) {
      cy.log('Error changing identity:', error);
      throw error;
    }
  }
}

export const profilePage = new ProfilePage();