import {
  LoginOptions,
  SchoolDetails,
  TeamGroupOptions,
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
    cy.get('#alert-close').click();
  }

  createTeamGroup(teamGroupOptions: TeamGroupOptions): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-user-teams-groups]').click();
    cy.get('[href="/teamsgroup/new/"]').click();

    cy.get('[name="title"]').type(teamGroupOptions.groupTitle);
    cy.get('[name="description"]').type(teamGroupOptions.groupDescription);
    cy.get('[name="number-of-contestants"]')
      .clear()
      .type(teamGroupOptions.noOfContestants);

    cy.get('[data-create-teams-group]').click();
  }

  uploadTeamGroups(): void {
    cy.get('[href="#upload"]').click();
    cy.get('[name="identities"]').attachFile('team_groups.csv');

    cy.get('td[aria-colindex="2"]').then((rawHTMLElements) => {
      const teamNames: Array<string> = [];
      Cypress.$.makeArray(rawHTMLElements).forEach((element) => {
        cy.task('log', element.innerText);
        teamNames.push(element.innerText);
      });

      cy.wrap(teamNames).as('teamNamesList');
    });

    cy.get('[name="create-identities"]').click();
    cy.get('#alert-close').click();
    cy.waitUntil(() => {
      return cy.get('#alert-close').should('not.be.visible');
    });

    cy.get('[href="#teams"]').click();
    cy.get('@teamNamesList').then((textArray) => {
      cy.get('[data-group-team-name]')
        .should('have.length', textArray.length)
        .then((rawHTMLElements) => {
          return Cypress.$.makeArray(rawHTMLElements).map((el) => el.innerText);
        })
        .should('deep.equal', textArray);
    });
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
