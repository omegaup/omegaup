#!/bin/bash

set -e

# Check that all the Psalm type annotations are consistent with what MySQL
# returns.

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)

# Clean up anything that might have been left from a previous run.
if [[ -d "${OMEGAUP_ROOT}/frontend/tests/runfiles/" ]]; then
	find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;
fi

# Enable General Query Log
mysql -h mysql -uroot -e "TRUNCATE TABLE mysql.general_log;"
mysql -h mysql -uroot -e "SET GLOBAL general_log = 'ON';"
mysql -h mysql -uroot -e "SET GLOBAL log_output = 'TABLE';"

"${OMEGAUP_ROOT}/stuff/run-php-tests.sh"

# Disable General Query Log
mysql -h mysql -uroot -e "SET GLOBAL general_log = 'OFF';"


sort --unique \
	--output "${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log" \
	"${OMEGAUP_ROOT}"/frontend/tests/runfiles/*/mysql_types.log

find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_return_types.py" \
	"${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log"

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_explain_logs.py"
