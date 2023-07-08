import { TeamGroupOptions } from '../types';

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
}

export const profilePage = new ProfilePage();
