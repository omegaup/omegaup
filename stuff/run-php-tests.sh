#!/bin/bash

set -e

# This script is the one which actually runs the PHPUnit test suite.
# In its default usage:
#   stuff/run-php-tests.sh
#
# it runs all tests in the 'frontend/tests' directory.
#
# However, this script will also pass along any arguments to the phpunit
# command, so one can run specific tests and use phpunit's filtering
# capabilities.
#
# For example:
#   stuff/run-php-tests.sh frontend/tests/controllers/UserRankTest.php \
#       --filter testUserRankingClassName

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)

if [[ -z "$@" ]]; then
        ARGS=("${OMEGAUP_ROOT}/frontend/tests/")
else
        ARGS="$@"
fi

exec "${OMEGAUP_ROOT}/vendor/bin/phpunit" \
	--bootstrap "${OMEGAUP_ROOT}/frontend/tests/bootstrap.php" \
	--configuration="${OMEGAUP_ROOT}/frontend/tests/phpunit.xml" \
	--coverage-clover="${OMEGAUP_ROOT}/coverage.xml" \
	"${ARGS[@]}"