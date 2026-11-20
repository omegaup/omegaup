import { defineConfig } from 'cypress'

export default defineConfig({
  experimentalStudio: true,
  chromeWebSecurity: false,
  experimentalMemoryManagement: true,
  numTestsKeptInMemory: 0,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config)
    },
    baseUrl: 'http://127.0.0.1:8001',
    retries: {
      // ponytail: fail-fast on PRs (0 retries); keep 2 retries on main where flakes block merges.
      runMode: process.env.CYPRESS_FAIL_FAST === '1' ? 0 : 2,
      // Keep interactive `cypress open` runs at zero retries so flakes
      // stay visible while developing tests locally.
      openMode: 0,
    },
  },
})
