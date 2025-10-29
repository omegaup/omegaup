## Preliminary Steps
You need to have `node` installed to use `cypress`:
```bash
sudo apt-get install nodejs
sudo apt-get install npm
```

If you still get:
```bash
No version of Cypress is installed in: ~/.cache/Cypress/9.1.1/Cypress

Please reinstall Cypress by running: cypress install

----------

Cypress executable not found at: ~/.cache/Cypress/9.1.1/Cypress/Cypress

----------

Platform: linux-x64 (Ubuntu - 20.04)
Cypress Version: 9.1.1
```

You need to install cypress as follows:
```bash
./node_modules/.bin/cypress install
```

Other errors that may appear include:
```bash
It looks like this is your first time using Cypress: 13.3.0


Cypress failed to start.

This may be due to a missing library or dependency. https://on.cypress.io/required-dependencies

Please refer to the error below for more details.

----------

~/.cache/Cypress/13.3.0/Cypress/Cypress: error while loading shared libraries: libnss3.so: cannot open shared object file: No such file or directory

Platform: linux-x64 (Ubuntu - 22.04)
Cypress Version: 13.3.0
```
You need to install the following dependencies:
```bash
sudo apt install libgconf-2-4 libatk1.0-0 libatk-bridge2.0-0 libgdk-pixbuf2.0-0 libgtk-3-0 libgbm-dev libnss3-dev libxss-dev
```

Or the following error:
```bash
It looks like this is your first time using Cypress: 13.3.0


Cypress failed to start.

This may be due to a missing library or dependency. https://on.cypress.io/required-dependencies

Please refer to the error below for more details.

----------

~/.cache/Cypress/13.3.0/Cypress/Cypress: error while loading shared libraries: libasound.so.2: cannot open shared object file: No such file or directory

----------

Platform: linux-x64 (Ubuntu - 22.04)
Cypress Version: 13.3.0
```

You need to install the dependency:
```bash
sudo apt-get install libasound2
```

## Introduction
**Note:** Currently, tests are run outside of the Docker container.

Everything related to Cypress is located inside the `./cypress` folder at the root directory.

## GUI
To run the Cypress GUI (which is great), use `npx cypress run` `./node_modules/.bin/cypress open`. This will open a screen with all available tests.

![image](https://github.com/user-attachments/assets/a3bf6b11-0e9a-4290-b5e5-fec6884be3e7)

Click on the test you are interested in running.

### Test View
![image](https://github.com/user-attachments/assets/b1f615c1-e8f8-4259-ad97-52d6f1490478)

Now you can see the test execution in real time, and if you hover over a command, you can see a `snapshot` of the page at that moment in the test. You can also open the console (right-click -> inspect element) and see the output of the page actions.

You can select an element with the selector tool to get the necessary command to select that object in the test code.

![image](https://github.com/user-attachments/assets/403b84b6-5516-4956-8123-ec60bd1b2b26)

## Headless
If you don't want to run the test with GUI, you can use `npx cypress run` or `./node_modules/.bin cypress run` to execute the tests without an interface. A video will be created inside `./cypress/videos` so you can see how the test was performed.

## Writing Tests for Cypress
The tests are contained within `./cypress/e2e` and have the filename format `name.cy.ts`. (Subfolders can be created if necessary.)

For basic commands, refer to: https://docs.cypress.io/guides/getting-started/writing-your-first-test

### Commands
One interesting feature of Cypress is **custom commands**. Custom commands are simply functions that contain Cypress commands for testing. For example, a custom command could be `login(username, password)`. This command would handle the login process so that it doesn't need to be rewritten in every test.

These commands are declared in `./cypress/support/commands.js`:
```typescript
Cypress.Commands.add('login', ({ username, password }: LoginOptions) => {
  const URL =
    '/api/user/login?' + buildURLQuery({ usernameOrEmail: username, password });
  cy.request(URL).then((response) => {
    expect(response.status).to.equal(200);
    cy.reload();
  });
});
```
Since we are using TypeScript, it is necessary to add the command type inside `./cypress/support/cypress.d.ts`:
```typescript
// <reference types="cypress"/>

declare global {
  namespace Cypress {
    // Includes custom omegaup API error types
    interface Error {
      error: string;
    }

    interface Chainable {
      login(loginOptions: LoginOptions): void;
      // Aquí van los demás comandos
  }
}
```
https://docs.cypress.io/api/cypress-api/custom-commands

### Events
Sometimes, we want to continue running a test even if an exception occurs. For that, Cypress events exist. These can be declared globally or locally. For example, when running Cypress, the Google Sign-In API did not recognize `127.0.0.1` (Docker IP) as a permitted host, which was breaking the tests. A global event can be added so that when this exception appears, the test does not stop and continues running.

Global events are added in `./cypress/support/e2e.ts`. Local events are added within the test file.

```typescript
import './commands';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
Cypress.on('uncaught:exception', (err, runnable) => {
  if (err.error.includes('idpiframe_initialization_failed')) {
    // Google API sign in error
    return false;
  }
});

```
https://docs.cypress.io/api/events/catalog-of-events

### Cypress Studio
The coolest way to write tests! If you open the Cypress GUI and go to any test, at the end, there is a button that allows you to record the test yourself. All interactions you make with the page will be added to the test you are modifying!


![](https://docs.cypress.io/_nuxt/img/extend-activate-studio.91d9bd8.png)

Or if you move the mouse a little higher, you can create a completely new test.

After recording, these interactions are saved in the respective file as commands.

**Note**: Since Studio saves your interactions as commands, there is no need to rush through actions, as the time between each interaction is not recorded.

### Important Plugins
Currently, two plugins are installed in Cypress: WaitUntil and File-Upload.

## Github Actions
Sometimes, your tests may work locally, but when you try to make a PR, the test fails in GitHub Actions. To see exactly what went wrong, go to the `Checks` tab inside the PR and then select `CI`.
![](https://i.imgur.com/6iu4w1L.png)

Scroll down on this screen and download the `cypress-screenshots-${{ github.run_attempt }}` and `cypress-videos-${{ github.run_attempt }}` artifacts.
![](https://i.imgur.com/4Huvtpy.png)

Scroll down on this screen and download the `cypress-screenshots-<run_attempt>` and `cypress-videos-<run_attempt>` artifacts.

> **Note:** The `<run_attempt>` is a number that indicates the attempt count of the workflow run.  
> You can find this by checking the workflow run URL or from the GitHub Actions UI under the "Attempt" number shown in the header.

Example:
If the workflow URL ends with `/attempts/3`, the artifact name will be:
- `cypress-screenshots-3`
- `cypress-videos-3`
There, you will find all the videos generated by the Cypress container running in GitHub Actions. You can see exactly what is happening with your test and debug it more effectively!

