// https://on.cypress.io/configuration

import './commands';
import '@cypress/code-coverage/support';
// eslint-disable-next-line @typescript-eslint/no-unused-vars
Cypress.on('uncaught:exception', (err, runnable) => {
  if (
    (err as any).message?.includes('idpiframe_initialization_failed') ||
    (err as any).error?.includes('idpiframe_initialization_failed') ||
    (err as any).message?.includes(
      'ResizeObserver loop completed with undelivered notifications',
    )
  ) {
    // Google API sign in error
    return false;
  }
});
