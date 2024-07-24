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
mysql -h mysql -P 13306 -uroot -e "SET GLOBAL general_log = 'OFF';"
mysql -h mysql -P 13306 -uroot -e "ALTER TABLE mysql.general_log ENGINE = MyISAM;"
mysql -h mysql -P 13306 -uroot -e "TRUNCATE TABLE mysql.general_log;"
mysql -h mysql -P 13306 -uroot -e "SET GLOBAL general_log = 'ON';"

"${OMEGAUP_ROOT}/stuff/run-php-tests.sh"

# Display General Query Log
mysql -h mysql -P 13306 -uroot -e "SELECT CONVERT(argument USING utf8) AS argument FROM mysql.general_log WHERE command_type = 'Query' ORDER BY event_time DESC LIMIT 100;"

# Disable General Query Log
mysql -h mysql -P 13306 -uroot -e "SET GLOBAL general_log = 'OFF';"

sort --unique \
	--output "${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log" \
	"${OMEGAUP_ROOT}"/frontend/tests/runfiles/*/mysql_types.log

find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_return_types.py" \
	"${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log"
