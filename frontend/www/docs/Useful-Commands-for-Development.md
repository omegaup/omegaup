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

### Apply changes to schema.sql
```bash
./stuff/update-dao.sh
```
**Description:** Applies changes to the `schema.sql` file when adding a new migration file in `.sql`. Works until the migration file is committed.

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

### Verify GitHub Actions workflow references
```bash
./hack/gha-reversemap.sh verify-mapusage
```
**Description:** Checks that all workflow files in `.github/workflows` reference GitHub Actions by commit hash and that each hash matches the one in `.gha-reversemap.yml`. This ensures GitHub Actions are referenced by immutable commit hashes rather than mutable tags. You can optionally specify specific workflow files as arguments.  
**Prerequisites:** Requires `yq` (version v4.45 or higher) to be installed.  
**Execution Location:** Outside the Docker container, in the project's root directory. e.g. `ubuntu@pc:~/dev/omegaup$`

### Apply reversemap to GitHub Actions workflows
```bash
./hack/gha-reversemap.sh apply-reversemap
```
**Description:** Updates workflow files in `.github/workflows` with commit hashes from `.gha-reversemap.yml`, replacing any tag references with the corresponding commit hashes. You can optionally specify specific workflow files as arguments.  
**Prerequisites:** Requires `yq` (version v4.45 or higher) to be installed.  
**Execution Location:** Outside the Docker container, in the project's root directory. e.g. `ubuntu@pc:~/dev/omegaup$`

### Update GitHub Action version in reversemap
```bash
./hack/gha-reversemap.sh update-action-version actions/checkout
```
**Description:** Updates the version of a GitHub Action in `.gha-reversemap.yml` (sha, tag, urls) to its latest regular release tag. Replace `actions/checkout` with the action reference you want to update (format: `{gh_owner}/{gh_repo}`). You can update multiple actions by providing multiple arguments.  
**Prerequisites:** Requires `yq` (version v4.45 or higher) to be installed. Optionally set `GITHUB_TOKEN` environment variable to avoid rate limiting.  
**Execution Location:** Outside the Docker container, in the project's root directory. e.g. `ubuntu@pc:~/dev/omegaup$`

### Update reversemap from GitHub Actions workflows
```bash
./hack/gha-reversemap.sh update-reversemap
```
**Description:** Updates `.gha-reversemap.yml` with information (sha, tag, urls) from the GitHub Actions used in workflow files. If workflow files reference actions by tags, it fetches the corresponding commit hashes and updates the reversemap. You can optionally specify specific workflow files as arguments.  
**Prerequisites:** Requires `yq` (version v4.45 or higher) to be installed. Optionally set `GITHUB_TOKEN` environment variable to avoid rate limiting.  
**Execution Location:** Outside the Docker container, in the project's root directory. e.g. `ubuntu@pc:~/dev/omegaup$`
