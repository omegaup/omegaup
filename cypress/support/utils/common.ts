import 'cypress-file-upload';
import 'cypress-wait-until';
import { GroupOptions } from "../types";

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
