Below is a list of commands/scripts that are useful for people involved in development:  

---

### Run all omegaUp linters
**Command:**
```bash
./stuff/lint.sh
```
**Description:** Runs all code validations available in omegaUp. If not executed directly, it will run automatically when performing `git push`.  
**Execution Location:** Outside the Docker container, in the project's root directory. e.g. `ubuntu@pc:~/dev/omegaup$`  



### Generate `.lang` files
**Command:**
```bash
./stuff/lint.sh --linters=i18n fix --all
```
**Description:** Generates `*.lang*` files based on `es.lang`, `en.lang`, and `pt.lang`.  
**Execution Location:** Outside the Docker container, in the project's root directory. e.g. `ubuntu@pc:~/dev/omegaup$`  



### Run all omegaUp tests and validations in PHP
**Command:**
```bash
./stuff/runtests.sh
```
**Description:** Runs tests using `PHPUnit`, as well as executing the type validator in `MySQL` and `PSALM`.  
**Execution Location:** Inside the Docker container, in the root directory.  



### Run Cypress Tests
```bash
npx cypress open
```
**Description:** Opens the `Cypress Test Runner`, a graphical interface that allows interactive test execution and debugging. Additional configurations may be required for local environments; the most common are:
- Install `nodejs`
- Install `npm`
- Install `libasound2`

\* For more detailed information, visit [How to use Cypress in omegaUp](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/How-to-use-Cypress-in-omegaUp.md)

**Execution Location:** Outside the Docker container.  

### Reset database to initial state
```bash
./stuff/bootstrap-environment.py --purge
```
**Description:** Restores the database to its initial state and populates the local development environmentwith data (contests, courses, problems, etc.) for manual testing via a series of API requests. The generated data is stored in the `stuff/bootstrap.json` file.

**Execution Location:** Inside the Docker container, in the root directory.  

### Run PHP type validators
```bash
find frontend/ \
    -name *.php \
    -and -not -wholename 'frontend/server/libs/third_party/*' \
    -and -not -wholename 'frontend/tests/badges/*' \
    -and -not -wholename 'frontend/tests/controllers/*' \
    -and -not -wholename 'frontend/tests/runfiles/*' \
    -and -not -wholename 'frontend/www/preguntas/*' \
  | xargs ./vendor/bin/psalm \
    --long-progress \
    --show-info=false
```
**Description:** Runs type validation on PHP files.  
**Execution Location:** Inside the Docker container, in the root directory.  

### Run PHP unit tests for a specific file
```bash
./stuff/run-php-tests.sh frontend/tests/controllers/$MY_FILE.php
```
**Description:** Runs unit tests for an individual PHP file. To run all tests, omit the file name.  
**Execution Location:** Inside the Docker container, in the root directory.  

### Regenerate DAO files from schema
```bash
./stuff/update-dao.sh
```
**Description:** Updates DAO (Data Access Object) files after modifying the database schema. This script should be run as part of a **two-step deployment process**:

1. **First commit:** Modify `schema.sql` and add migration scripts (`database/*.sql`). Deploy to production and verify everything works correctly.
2. **Second commit (after verification):** Run this script to:
   - Copy `schema.sql` to `dao_schema.sql`
   - Regenerate all DAO Base and VO PHP files in `frontend/server/src/DAO/`

**Why separate commits?** This allows safe rollback if the schema migration fails in production. Since DAOs aren't regenerated yet, the old code continues working, and you can manually revert database changes without code conflicts.

**Execution Location:** Inside the Docker container, in the root directory.  

### Apply database migrations locally
```bash
./stuff/db-migrate.py migrate --databases=omegaup,omegaup-test
```
**Description:** Applies local schema changes when a new migration file in `database/*.sql` is added.

**Execution Location:** Inside the Docker container, in the root directory.  

### Run Vue unit tests
```bash
yarn run test:watch
```
**Description:** Runs Vue unit tests in watch mode, automatically rerunning tests when code changes. The command runs within the local development environment of the application, providing an efficient and dynamic workflow during test development.

**Execution Location:** Inside the Docker container, in the root directory.  

### Run a specific Vue unit test file
```bash
./node_modules/.bin/jest frontend/www/js/omegaup/components/$MY_FILE.test.ts
```
**Description:** Runs a single Vue unit test file.  
**Execution Location:** Inside or outside the docker container.  

### Restart Docker service
```bash
systemctl restart docker.service
```
**Description:** Restarts the Docker service. Useful for resolving errors such as:  
```bash
OCI runtime exec failed: exec failed: unable to start container process: open /dev/pts/0: operation not permitted: unknown
```
**Execution Location:** Outside the docker container
