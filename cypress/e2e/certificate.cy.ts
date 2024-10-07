import { LoginOptions } from '../support/types';

describe('Certificate Test', () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
    cy.visit('/');
  });

  // TODO: Temporarily skipping the test until we can generate certificates.
  it.skip('Should copy the verification link', () => {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);

    let url: string;
    cy.url().then((currentUrl) => {
      url = currentUrl;
    });

    cy.visit('/certificates/mine/');
    cy.get('button[copy-to-clipboard]').each(($button) => {
      cy.wrap($button)
        .click()
        .then(() => {
          cy.window().then((win) => {
            win.navigator.clipboard.readText().then((text) => {
              const code = $button.attr('data-code');
              expect(text).to.eq(`${url}cert/${code}/`);
            });
          });
        });
    });

    cy.logout();
  });

  // TODO: Temporarily skipping the test until we can generate certificates.
  it.skip('Should download the certificates PDF', () => {
    const loginOptions: LoginOptions = {
      username: 'user',
      password: 'user',
    };
    cy.login(loginOptions);

    cy.visit('/certificates/mine/');
    cy.get('a[download-file]').each(($button) => {
      $button.attr('download', '');
      cy.wrap($button).click();
      const code = $button.attr('data-code');
      cy.readFile(`cypress/downloads/certificate_${code}.pdf`).should('exist');
    });

    cy.logout();
  });
});
