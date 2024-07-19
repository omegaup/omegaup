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

# Path to the general query log file
GENERAL_LOG_FILE="/var/log/omegaup/mysql_general.log"

# Create the log file and set permissions
touch ${GENERAL_LOG_FILE}
chmod 666 ${GENERAL_LOG_FILE}

# Enable General Query Log
mysql -e "SET GLOBAL general_log = 'ON';"
mysql -e "SET GLOBAL general_log_file = '${GENERAL_LOG_FILE}';"

exec "${OMEGAUP_ROOT}/vendor/bin/phpunit" \
	--bootstrap "${OMEGAUP_ROOT}/frontend/tests/bootstrap.php" \
	--configuration="${OMEGAUP_ROOT}/frontend/tests/phpunit.xml" \
	--coverage-clover="${OMEGAUP_ROOT}/coverage.xml" \
	"${ARGS[@]}"

# Disable General Query Log
mysql -e "SET GLOBAL general_log = 'OFF';"