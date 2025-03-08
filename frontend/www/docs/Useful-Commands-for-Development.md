Below is a list of commands/scripts that are useful for people involved in development:  



### Run all omegaUp linters
**Command:**
```bash
./stuff/lint.sh
```
**Description:** Script required to run all code validations available in omegaUp. If not executed directly, it will run automatically when performing `git push`.  
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
**Description:** It is currently responsible for running tests in `PHPUnit`, as well as executing the type validator in `MySQL` and `PSALM`.  
**Execution Location:** Inside the Docker container, in the root directory.  



### Run Cypress Tests
```bash
npx cypress open
```
**Description:** It opens the `Cypress Test Runner`, a graphical interface that allows you to interactively run and debug Cypress tests in a browser. It enables you to select and run individual tests, view detailed test results, and inspect any failures in real-time, providing an efficient environment for writing, running, and debugging tests. It could require extra configurations to work properly in the local environment. 
**Execution Location:** Outside the Docker container.  

### Reset database to initial state
```bash
./stuff/bootstrap-environment.py --purge
```
**Description:** A script required to restore the database to its initial state. It also runs a series of API requests to populate the local development environment. With this script, contests, courses, problems, and everything necessary for manual testing can be created. The generated data is stored in the `stuff/bootstrap.json` file.
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
**Description:** Command to run type validators on PHP files.  
**Execution Location:** Inside the Docker container, in the root directory.  

### Run PHP unit tests for a specific file
```bash
./stuff/run-php-tests.sh frontend/tests/controllers/$MY_FILE.php
```
**Description:** Command to run unit tests for an individual PHP file. To run all tests, omit the file name.  
**Execution Location:** Inside the Docker container, in the root directory.  

### Apply changes to schema.sql
```bash
./stuff/update-dao.sh
```
**Description:** Script required to apply changes to the `schema.sql` file when adding a new migration file in `.sql` (It will work until the migration file is committed).  
**Execution Location:** Inside the Docker container, in the root directory.  

### Apply database migrations locally
```bash
./stuff/db-migrate.py migrate --databases=omegaup,omegaup-test
```
**Description:** Script required to apply schema changes locally when a new migration file in `.sql` is added.  
**Execution Location:** Inside the Docker container, in the root directory.  

### Run Vue unit tests
```bash
yarn run test:watch
```
**Description:** It runs Vue unit tests in "watch" mode. This means that whenever changes are made to test files or the source code files being tested, the tests are automatically rerun to reflect those changes. This makes development easier, as it allows developers to continuously see test results without having to manually rerun them each time a change is made to the code. The command runs within the local development environment of the application, providing an efficient and dynamic workflow during test development.
**Execution Location:** Inside the Docker container, in the root directory.  

### Run a specific Vue unit test file
```bash
./node_modules/.bin/jest frontend/www/js/omegaup/components/$MY_FILE.test.ts
```
**Description:** Useful command to run a single Vue unit test file.  
**Execution Location:** It works fine inside or outside the docker container.  

### Restart Docker service
```bash
systemctl restart docker.service
```
**Description:** Command used to restart the Docker process. It is useful when encountering the following error while running Docker:  
```bash
OCI runtime exec failed: exec failed: unable to start container process: open /dev/pts/0: operation not permitted: unknown
```
**Execution Location:** Outside the docker container
