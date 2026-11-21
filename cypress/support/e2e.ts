// https://on.cypress.io/configuration

import './commands';
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

// ponytail: abort remaining specs in shard on first failure during PR runs without adding cypress-fail-fast plugin.
afterEach(function () {
  if (
    this.currentTest?.state === 'failed' &&
    String(Cypress.env('FAIL_FAST')) === '1'
  ) {
    Cypress.runner.stop();
  }
});
