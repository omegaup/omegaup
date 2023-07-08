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

  giveAdminPrivilage(roleName: string, user: string) {
    cy.loginAdmin();
    const userAdminUrl = '/admin/user/' + user;
    cy.visit(userAdminUrl);
    cy.get(`.${roleName}`).check();
    cy.get('#alert-close').click();
    cy.logout();
  }
}

export const loginPage = new LoginPage();
