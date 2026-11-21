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

mkdir -p "${OMEGAUP_ROOT}/frontend/tests/runfiles"

JUNIT_PATH="${OMEGAUP_JUNIT:-${OMEGAUP_ROOT}/frontend/tests/runfiles/phpunit-junit.xml}"
CLOVER_PATH="${OMEGAUP_CLOVER:-${OMEGAUP_ROOT}/coverage.xml}"
COVERAGE_ARGS=()
if [[ "${OMEGAUP_COVERAGE:-1}" != "0" ]]; then
	if [[ "${CLOVER_PATH}" == *.php ]]; then
		COVERAGE_ARGS+=("--coverage-php=${CLOVER_PATH}")
	else
		COVERAGE_ARGS+=("--coverage-clover=${CLOVER_PATH}")
	fi
fi

FAIL_FAST_ARGS=()
if [[ "${OMEGAUP_FAIL_FAST:-0}" == "1" ]]; then
	FAIL_FAST_ARGS+=("--stop-on-failure" "--stop-on-error")
fi

exec "${OMEGAUP_ROOT}/vendor/bin/phpunit" \
	--bootstrap "${OMEGAUP_ROOT}/frontend/tests/bootstrap.php" \
	--configuration="${OMEGAUP_ROOT}/frontend/tests/phpunit.xml" \
	"${COVERAGE_ARGS[@]}" \
	"${FAIL_FAST_ARGS[@]}" \
	--log-junit="${JUNIT_PATH}" \
	"${ARGS[@]}"
