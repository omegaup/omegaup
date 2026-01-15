# Release and Deployment  

## Deployment Process  

We use **GitHub Actions** for our continuous integration. Our deployment flow is managed as follows:  

- **Production deployment**: Automatically performed on weekend nights (Central Mexico time). Before deployment, all automated tests are executed to ensure code stability.  
- **Sandbox deployment**: Each merge into the (`main`) branch triggers an immediate deployment to [sandbox.omegaup.com](https://sandbox.omegaup.com). This environment serves as a pre-production testing ground, allowing us to catch errors before they reach production and enabling rollbacks if necessary.  
- **Hotfixes**: In case of critical errors in production, manual deployments can be performed following an internal validation process.  

## CI/CD Validations  

Before a Pull Request (PR) is approved and merged into `main`, it must pass a series of validations in **GitHub Actions**, ensuring code quality and preventing failures in production. The tests include:  

- **php**: Unit tests for controllers, written in **PHPUnit**.  
- **javascript**: Unit tests for **Vue.js**, written in **TypeScript + Jest**.  
- **lint**: Style and format validators for all languages used in the project, as well as type validators in **Psalm** for PHP.  
- **cypress**: **End-to-end (E2E) tests** to validate the proper functioning of critical platform flows.  
- **python**: Automated tests written in **pytest**.  

## Code Coverage  

We also use **Codecov** to measure test coverage. This helps us identify untested parts of the code, improving software quality and reliability.  

- Codecov measures test coverage for **PHP and TypeScript**.  
- Currently, **test coverage for Cypress is not yet measured**, which remains a pending task.  
