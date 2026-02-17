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

  giveAdminPrivilege(roleName: string, user: string) {
    cy.loginAdmin();
    const userAdminUrl = '/admin/user/' + user;
    cy.visit(userAdminUrl);
    cy.get(`.${roleName}`).check();
    cy.logout();
  }

  registerSingleUser(loginOptions: LoginOptions): void {
    cy.get('[data-login-button]').click();
    cy.get('.introjs-skipbutton').click();
    cy.get('a[href="#signup"]').click();
    cy.get('[data-signup-username]').type(loginOptions.username);
    cy.get('[data-signup-password]').type(loginOptions.password);
    cy.get('[data-signup-repeat-password]').type(loginOptions.password);
    cy.get('[data-signup-email]').type(`${loginOptions.username}@omegaup.com`);
    cy.get('[data-signup-accept-policies]').check();
    cy.get('[data-signup-submit]').click();
  }

  verifyUsername(loginOptions: LoginOptions): void {
    cy.waitUntil(() =>
      cy.get('header .username').should('have.text', loginOptions.username),
    );
    cy.logoutUsingApi();
  }

  loginByGUI(loginOptions: LoginOptions): void {
    cy.get('[data-login-button]').click();
    cy.get('.introjs-skipbutton').click();
    cy.get('[data-login-username]').type(loginOptions.username);
    cy.get('[data-login-password]').type(loginOptions.password);
    cy.get('[data-login-submit]').click();
  }
}

export const loginPage = new LoginPage();
