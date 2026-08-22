#!/bin/bash

set -e

# Check that all the Psalm type annotations are consistent with what MySQL
# returns.

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)

MYSQL_CONFIG_FILE="${OMEGAUP_MYSQL_CONFIG_FILE:-${HOME}/.my.cnf}"
MYSQL_ARGS=()
if [[ -f "${MYSQL_CONFIG_FILE}" ]]; then
	MYSQL_ARGS+=("--defaults-file=${MYSQL_CONFIG_FILE}")
else
	MYSQL_HOST="${OMEGAUP_MYSQL_HOST:-mysql}"
	MYSQL_USER="${OMEGAUP_MYSQL_USER:-root}"
	MYSQL_PASSWORD="${OMEGAUP_MYSQL_PASSWORD:-${MYSQL_ROOT_PASSWORD:-}}"
	MYSQL_ARGS+=("--protocol=TCP" "-h" "${MYSQL_HOST}" "-u${MYSQL_USER}")
	if [[ -n "${MYSQL_PASSWORD}" ]]; then
		MYSQL_ARGS+=("--password=${MYSQL_PASSWORD}")
	else
		MYSQL_ARGS+=("--skip-password")
	fi
fi

# Clean up anything that might have been left from a previous run.
if [[ -d "${OMEGAUP_ROOT}/frontend/tests/runfiles/" ]]; then
	find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;
fi

# Enable General Query Log
mysql "${MYSQL_ARGS[@]}" -e "TRUNCATE TABLE mysql.general_log;"
mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL general_log = 'ON';"
mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL log_output = 'TABLE';"

"${OMEGAUP_ROOT}/stuff/run-php-tests.sh"

# Disable General Query Log
mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL general_log = 'OFF';"

sort --unique \
	--output "${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log" \
	"${OMEGAUP_ROOT}"/frontend/tests/runfiles/*/mysql_types.log

find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_return_types.py" \
	"${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log"

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_explain_logs.py"
