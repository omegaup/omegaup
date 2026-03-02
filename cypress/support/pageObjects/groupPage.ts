import { GroupOptions, TeamGroupOptions } from '../types';

export class GroupPage {
  /**
   * Dismisses the global notification alert by clicking its close button.
   * Asserts the alert is visible before clicking and verifies it disappears after.
   * Uses a fresh DOM query after click to avoid flakiness from detached elements.
   */
  private dismissAlert(): void {
    // First, locate and assert the alert is visible, then click its close button
    // Uses role="alert" selector which works with both old jQuery and new Vue notifications
    cy.get('.alert[role="alert"]')
      .should('be.visible')
      .find('[data-alert-close]')
      .click();

    // Perform a fresh root query to verify the alert was dismissed
    cy.get('.alert[role="alert"]').should('not.exist');
  }

  createGroup(groupOptions: GroupOptions): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-user-groups]').click();

    cy.get('[href="/group/new/"]').click();

    cy.get('[name="title"]').type(groupOptions.groupTitle);
    cy.get('[name="description"]').type(groupOptions.groupDescription);

    cy.get('[data-group-new]').submit();
  }

  addIdentitiesGroup(): void {
    cy.get('[href="#identities"]').click();
    cy.get('.introjs-skipbutton').click();
    cy.get('[name="identities"]').attachFile('identities.csv');

    cy.get('[data-identity-username]').then((rawHTMLElements) => {
      const userNames: Array<string> = [];
      Cypress.$.makeArray(rawHTMLElements).forEach((element) => {
        cy.task('log', element.innerText);
        userNames.push(element.innerText);
      });

      cy.wrap(userNames).as('userNamesList');
    });

    const uploadedPasswords: Array<string> = [];
    cy.get('[data-identity-password]').then((rawHTMLElements) => {
      uploadedPasswords.concat(
        Cypress.$.makeArray(rawHTMLElements).map((el) => el.innerText),
      );
    });

    cy.get('[name="create-identities"]').click();
    this.dismissAlert();

    cy.get('[href="#members"]').click();
    cy.get('@userNamesList').then((textArray) => {
      cy.get('[data-members-username]')
        .should('have.length', textArray.length)
        .then((rawHTMLElements) => {
          return Cypress.$.makeArray(rawHTMLElements).map((el) => el.innerText);
        })
        .should('deep.equal', textArray);
    });
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
    this.dismissAlert();

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
}

export const groupPage = new GroupPage();
