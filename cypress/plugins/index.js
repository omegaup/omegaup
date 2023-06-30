/// <reference types="cypress" />
// https://on.cypress.io/plugins-guide

/**
 * @type {Cypress.PluginConfig}
 */
// eslint-disable-next-line no-unused-vars
module.exports = (on, config) => {
    on('task', {
        log(message) {
            console.log(message);

            return null;
        },
    });
};
