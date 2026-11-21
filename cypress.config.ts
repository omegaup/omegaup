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
      // ponytail: 1 retry on PRs absorbs transient flakes without 3x failure cost; 2 on main.
      runMode: process.env.CYPRESS_FAIL_FAST === '1' ? 1 : 2,
      // Keep interactive `cypress open` runs at zero retries so flakes
      // stay visible while developing tests locally.
      openMode: 0,
    },
  },
})
