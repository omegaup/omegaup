import { v4 as uuid } from 'uuid';
import { LoginOptions } from "../types";

export class LoginPage {

    registerMultipleUsers(noOfUsers: number): LoginOptions[] {
        const users: LoginOptions[] = [];

        for (let i = 0; i < noOfUsers; i++) {
            const userLoginOptions: LoginOptions = {
                username: 'utGroup_' + uuid(),
                password: 'P@55w0rd',
            };

            cy.register(userLoginOptions);
            cy.logout();
            users.push(userLoginOptions);
        }

        return users;
    }
}

export const loginPage = new LoginPage();