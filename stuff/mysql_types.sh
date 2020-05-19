#!/bin/bash

set -e

# Check that all the Psalm type annotations are consistent with what MySQL
# returns.

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)

"${OMEGAUP_ROOT}/vendor/bin/phpunit" \
	--bootstrap "${OMEGAUP_ROOT}/frontend/tests/bootstrap.php" \
	--configuration="${OMEGAUP_ROOT}/frontend/tests/phpunit.xml" \
	--coverage-clover="${OMEGAUP_ROOT}/coverage.xml" \
	"${OMEGAUP_ROOT}/frontend/tests/controllers"
mv "${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log" \
	"${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log.1"

"${OMEGAUP_ROOT}/vendor/bin/phpunit" \
	--bootstrap "${OMEGAUP_ROOT}/frontend/tests/bootstrap.php" \
	--configuration="${OMEGAUP_ROOT}/frontend/tests/phpunit.xml" \
	"${OMEGAUP_ROOT}/frontend/tests/badges"
mv "${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log" \
	"${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log.2"

cat "${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log.1" \
	"${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log.2" > \
	"${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log"

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_return_types.py" \
	"${OMEGAUP_ROOT}/frontend/tests/controllers/mysql_types.log"
