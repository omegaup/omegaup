import 'cypress-file-upload';
import 'cypress-wait-until';
import { ContestOptions, GroupOptions } from "../types";

/**
 * Creates a group as an admin and returns a generated group alias.
 */
export const createGroup = (groupOptions: GroupOptions) => {
   cy.get('[data-nav-user]').click();
   cy.get('[data-nav-user-groups]').click();

   // Click on the button to create a new group
   cy.get('[href="/group/new/"]').click();

   // Fill out the form to create a new group
   cy.get('[name="title"]').type(groupOptions.groupTitle);
   cy.get('[name="description"]').type(groupOptions.groupDescription);

   // Submit the form to create the group
   cy.get('[data-group-new]').submit();
};

/**
 * Upload csv and add identities into the group and returns identity value.
 */
export const addIdentitiesGroup = () => {
   // Navigate to the "Identities" tab
   cy.get('[href="#identities"]').click();

   // Upload a CSV file
   cy.get('[name="identities"]').attachFile('identities.csv');

   // Extract the usernames from the table
   cy.get('[data-identity-username]').then(($els) => {
      // we get a list of jQuery elements
      // let's convert the jQuery object into a plain array
      const userNames: Array<string> = [];
      Cypress.$.makeArray($els).forEach(element => {
         cy.task("log", element.innerText);
         userNames.push(element.innerText);
      });

      cy.wrap(userNames).as('savedTextArray');
   })

   // Extract the passwords from the table
   const uploadedPasswords: Array<string> = [];
   cy.get('[data-identity-password]').then(($els) => {
      // we get a list of jQuery elements
      // let's convert the jQuery object into a plain array
      uploadedPasswords.concat(Cypress.$.makeArray($els).map((el) => el.innerText));
   })

   cy.get('[name="create-identities"]').click();
   cy.get('#alert-close').click();
   cy.waitUntil(() => {
      return cy.get('#alert-close').should('not.be.visible');
   })

   // Navigate to the "Members" tab
   cy.get('[href="#members"]').click();

   cy.get('@savedTextArray').then((textArray) => {
      // Use textArray for assertions or other operations
      cy.get('[data-members-username]')
      .should('have.length', textArray.length)
      .then(($els) => {
         // we get a list of jQuery elements
         // let's convert the jQuery object into a plain array
         return (
            Cypress.$.makeArray($els)
            // and extract inner text from each
            .map((el) => el.innerText)
         )
      })
      .should('deep.equal', textArray);
   });
  
};

/**
 * Add students to a recently created contest.
 */
export const addStudentsBulk = (users: Array<string>) => {
   cy.get('a[data-nav-contest-edit]').click();
   cy.get('a[data-nav-contestant]').click();

   cy.get('textarea[data-contestant-names]').type(users.join(', '));
   cy.get('.user-add-bulk').click();

   // Extract the usernames from the table
   cy.get('[data-uploaded-contestants]').then(($els) => {
      // we get a list of jQuery elements
      // let's convert the jQuery object into a plain array
      const constestantNames: Array<string> = [];
      Cypress.$.makeArray($els).forEach(element => {
         cy.task("log", element.innerText);
         constestantNames.push(element.innerText);
      });

      cy.wrap(constestantNames).as('savedConstestantNames');
   })

   cy.get('@savedConstestantNames').should('deep.equal', users);
};

/**
 * Makes the user post a question in an specific contest and problem
 */
export const createClarificationUser = (contestOptions: ContestOptions, question: string) => {
   cy.get('a[href="#clarifications"]').click();
   cy.waitUntil(() =>
      cy.get('[data-tab-clarifications]').should('be.visible')
   );

   cy.get('a[data-new-clarification-button]').click();
   cy.get('[data-new-clarification-problem]').select(contestOptions.problems[0].problemAlias);
   cy.get('[data-new-clarification-message]').should('be.visible').type(question);

   cy.get('[data-new-clarification]').submit();
   cy.get('[data-form-clarification-message]').should('have.text', question);
};

/**
 * Updates the scoreboard for a contest.
 */
export const updateScoreboardForContest = (contestAlias: string) => {
   const encodedContestAlias = encodeURIComponent(contestAlias);
   const scoreboardRefreshUrl = `/api/scoreboard/refresh/alias/${encodedContestAlias}/token/secret`;

   cy.request(scoreboardRefreshUrl)
      .its('body')
      .should('contain', '"status":"ok"');
};
