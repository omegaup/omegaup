// https://on.cypress.io/configuration

import './basic_commands';
import './creator_commands';

Cypress.on('uncaught:exception', (err, runnable) => {
  if (err.error.includes('idpiframe_initialization_failed')) {
    // Google API sign in error
    return false;
  }
});
