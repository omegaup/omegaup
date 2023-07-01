import { v4 as uuid } from 'uuid';
import { LoginOptions } from '../types';

export class LoginPage {
  registerMultipleUsers(noOfUsers: number): LoginOptions[] {
    const users: LoginOptions[] = [];

    for (let i = 0; i < noOfUsers; i++) {
      const userLoginOptions: LoginOptions = {
        username: 'utGroup_user' + i + '_' + uuid(),
        password: 'P@55w0rd',
      };

      cy.register(userLoginOptions);
      cy.logout();
      users.push(userLoginOptions);
    }

    return users;
  }

  addUsername(userName: string): void {
    cy.get('[data-nav-user]').click();
    cy.get('[data-nav-profile]').click();
    cy.get('a[href="/profile/#edit-basic-information"]').click();
    cy.get('[data-name]').type(userName);
    cy.get('[data-save-profile-changes-button]').click();
    cy.get('#alert-close').click();
  }
}

export const loginPage = new LoginPage();
