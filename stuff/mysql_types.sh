#!/bin/bash

set -e

# Check that all the Psalm type annotations are consistent with what MySQL
# returns.

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)

# ponytail: round-robin by filename. Weight shards by junit time if one runs >1.5x the others.
assign_shards() {
	local workers="$1"
	shift
	local i=0
	local f
	for f in "$@"; do
		echo "$((i % workers)) ${f}"
		i=$((i + 1))
	done
}

if [[ "${1:-}" == "--self-check" ]]; then
	got=$(assign_shards 2 aTest.php bTest.php cTest.php)
	want=$'0 aTest.php\n1 bTest.php\n0 cTest.php'
	if [[ "${got}" != "${want}" ]]; then
		echo "shard split failed" >&2
		echo "${got}" >&2
		exit 1
	fi
	echo "shard split ok"
	exit 0
fi

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

disable_general_log() {
	mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL general_log = 'OFF';"
}

# Clean up anything that might have been left from a previous run.
if [[ -d "${OMEGAUP_ROOT}/frontend/tests/runfiles/" ]]; then
	find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;
fi

if [[ -n "${OMEGAUP_TEST_WORKERS:-}" ]]; then
	workers="${OMEGAUP_TEST_WORKERS}"
elif command -v nproc >/dev/null 2>&1; then
	workers="$(nproc)"
	if (( workers > 1 )); then
		workers=$((workers - 1))
	fi
else
	workers="$(sysctl -n hw.ncpu)"
	if (( workers > 1 )); then
		workers=$((workers - 1))
	fi
fi

tests=()
while IFS= read -r test_file; do
	tests+=("${test_file}")
done < <(find "${OMEGAUP_ROOT}/frontend/tests" -name '*Test.php' -type f | sort)

if (( ${#tests[@]} > 0 && workers > ${#tests[@]} )); then
	workers=${#tests[@]}
fi
echo "php test workers=${workers}"

if (( workers > 1 && ${#tests[@]} > 1 )); then
	schema="$(mktemp)"
	trap 'rm -f "${schema}"' EXIT
	mysqldump "${MYSQL_ARGS[@]}" --no-data --routines --triggers \
		--single-transaction --skip-comments omegaup-test > "${schema}"
	for token in $(seq 1 "${workers}"); do
		mysql "${MYSQL_ARGS[@]}" -e "DROP DATABASE IF EXISTS \`omegaup-test-${token}\`; CREATE DATABASE \`omegaup-test-${token}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
		mysql "${MYSQL_ARGS[@]}" "omegaup-test-${token}" < "${schema}"
	done
	rm -f "${schema}"
	trap - EXIT
fi

# Enable General Query Log
mysql "${MYSQL_ARGS[@]}" -e "TRUNCATE TABLE mysql.general_log;"
mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL general_log = 'ON';"
trap disable_general_log EXIT
mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL log_output = 'TABLE';"

if (( workers <= 1 || ${#tests[@]} <= 1 )); then
	"${OMEGAUP_ROOT}/stuff/run-php-tests.sh"
else
	shard_root="$(mktemp -d)"
	for token in $(seq 1 "${workers}"); do
		mkdir -p "${shard_root}/${token}"
	done
	while IFS= read -r line; do
		idx="${line%% *}"
		path="${line#* }"
		token=$((idx + 1))
		ln -s "${path}" "${shard_root}/${token}/$(basename "$(dirname "${path}")")_$(basename "${path}")"
	done < <(assign_shards "${workers}" "${tests[@]}")

	pids=()
	for token in $(seq 1 "${workers}"); do
		TEST_TOKEN="${token}" \
			OMEGAUP_JUNIT="${OMEGAUP_ROOT}/frontend/tests/runfiles/phpunit-junit-${token}.xml" \
			OMEGAUP_CLOVER="${OMEGAUP_ROOT}/frontend/tests/runfiles/coverage-${token}.php" \
			"${OMEGAUP_ROOT}/stuff/run-php-tests.sh" "${shard_root}/${token}" &
		pids+=("$!")
	done
	status=0
	for pid in "${pids[@]}"; do
		wait "${pid}" || status=1
	done
	rm -rf "${shard_root}"
	if (( status != 0 )); then
		exit "${status}"
	fi
	php "${OMEGAUP_ROOT}/stuff/merge-coverage.php" \
		"${OMEGAUP_ROOT}/vendor/autoload.php" \
		"${OMEGAUP_ROOT}/frontend/tests/runfiles" \
		"${OMEGAUP_ROOT}/coverage.xml"
fi

# Disable General Query Log
disable_general_log
trap - EXIT

sort --unique \
	--output "${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log" \
	"${OMEGAUP_ROOT}"/frontend/tests/runfiles/*/mysql_types.log

find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_return_types.py" \
	"${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log"

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_explain_logs.py"
