/// <reference types="cypress" />
// https://on.cypress.io/plugins-guide

/**
 * @type {Cypress.PluginConfig}
 */
// eslint-disable-next-line no-unused-vars
module.exports = (on, config) => {
    require('@cypress/code-coverage/task')(on, config);
    on('task', {
        log(message) {
            console.log(message);

            return null;
        },
    });
    return config;
};
