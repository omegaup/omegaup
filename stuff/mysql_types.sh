#!/bin/bash

set -e

# Check that all the Psalm type annotations are consistent with what MySQL
# returns.

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)

# ponytail: greedy LPT bin-packing with static seconds for the >=10s suites; beats dynamic timing caches with zero state.
assign_shards() {
	local workers="$1"
	shift
	printf '%s\n' "$@" | awk -v w="${workers}" '
	function weight(path, base) {
		base = path
		sub(/^.*\//, "", base)
		if (base == "CoderOfTheMonthTest.php") return 141
		if (base == "ProblemListTest.php") return 48
		if (base == "UserRankTest.php") return 45
		if (base == "SchoolOfTheMonthRulesTest.php") return 38
		if (base == "Badge_100solvedProblemsTest.php") return 33
		if (base == "CacheTest.php") return 28
		if (base == "QualityNominationTest.php") return 23
		if (base == "BadgesTest.php") return 17
		if (base == "CollectionListTest.php") return 16
		if (base == "SchoolOfTheMonthTest.php") return 12
		if (base == "ProblemUpdateTest.php") return 11
		if (base == "RunDetailsTest.php") return 10
		if (base == "CertificatesTest.php") return 10
		return 2
	}
	NF {
		n++
		files[n] = $0
		weights[n] = weight($0)
		order[n] = n
	}
	END {
		for (i = 2; i <= n; i++) {
			idx = order[i]
			wt = weights[idx]
			j = i - 1
			while (j >= 1 && weights[order[j]] < wt) {
				order[j + 1] = order[j]
				j--
			}
			order[j + 1] = idx
		}
		for (k = 0; k < w; k++) load[k] = 0
		for (i = 1; i <= n; i++) {
			idx = order[i]
			best = 0
			for (k = 1; k < w; k++) {
				if (load[k] < load[best]) best = k
			}
			shard[idx] = best
			load[best] += weights[idx]
		}
		for (i = 1; i <= n; i++) {
			print shard[i] " " files[i]
		}
	}'
}

if [[ "${1:-}" == "--self-check" ]]; then
	got=$(assign_shards 2 aTest.php bTest.php cTest.php)
	want=$'0 aTest.php\n1 bTest.php\n0 cTest.php'
	if [[ "${got}" != "${want}" ]]; then
		echo "shard split failed" >&2
		echo "${got}" >&2
		exit 1
	fi
	heavy=$(assign_shards 2 CoderOfTheMonthTest.php ProblemListTest.php UserRankTest.php CacheTest.php)
	heavy_want=$'0 CoderOfTheMonthTest.php\n1 ProblemListTest.php\n1 UserRankTest.php\n1 CacheTest.php'
	if [[ "${heavy}" != "${heavy_want}" ]]; then
		echo "weighted shard split failed" >&2
		echo "${heavy}" >&2
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
	# ponytail: one PHPUnit process per core. MySQL shares the runner; set OMEGAUP_TEST_WORKERS if that stalls.
	workers="$(nproc)"
else
	workers="$(sysctl -n hw.ncpu)"
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
	mysqldump "${MYSQL_ARGS[@]}" --routines --triggers \
		--single-transaction --skip-comments omegaup-test > "${schema}"
	for token in $(seq 1 "${workers}"); do
		mysql "${MYSQL_ARGS[@]}" -e "DROP DATABASE IF EXISTS \`omegaup-test-${token}\`; CREATE DATABASE \`omegaup-test-${token}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
		mysql "${MYSQL_ARGS[@]}" "omegaup-test-${token}" < "${schema}"
	done
	rm -f "${schema}"
	trap - EXIT
fi

# ponytail: non-blocking explain report stays on main; skip global general_log lock contention on PRs.
if [[ "${OMEGAUP_EXPLAIN_LOG:-1}" != "0" ]]; then
	mysql "${MYSQL_ARGS[@]}" -e "TRUNCATE TABLE mysql.general_log;"
	mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL general_log = 'ON';"
	trap disable_general_log EXIT
	mysql "${MYSQL_ARGS[@]}" -e "SET GLOBAL log_output = 'TABLE';"
fi

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
	if (( BASH_VERSINFO[0] > 4 || (BASH_VERSINFO[0] == 4 && BASH_VERSINFO[1] >= 3) )); then
		remaining=${#pids[@]}
		while (( remaining > 0 )); do
			if ! wait -n; then
				status=1
				if [[ "${OMEGAUP_FAIL_FAST:-0}" == "1" ]]; then
					kill "${pids[@]}" 2>/dev/null || true
					wait 2>/dev/null || true
					break
				fi
			fi
			remaining=$((remaining - 1))
		done
	else
		for pid in "${pids[@]}"; do
			wait "${pid}" || status=1
		done
	fi
	rm -rf "${shard_root}"
	if (( status != 0 )); then
		exit "${status}"
	fi
	if [[ "${OMEGAUP_COVERAGE:-1}" != "0" ]]; then
		php "${OMEGAUP_ROOT}/stuff/merge-coverage.php" \
			"${OMEGAUP_ROOT}/vendor/autoload.php" \
			"${OMEGAUP_ROOT}/frontend/tests/runfiles" \
			"${OMEGAUP_ROOT}/coverage.xml"
	fi
fi

if [[ "${OMEGAUP_EXPLAIN_LOG:-1}" != "0" ]]; then
	disable_general_log
	trap - EXIT
fi

sort --unique \
	--output "${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log" \
	"${OMEGAUP_ROOT}"/frontend/tests/runfiles/*/mysql_types.log

find "${OMEGAUP_ROOT}/frontend/tests/runfiles/" -mindepth 2 -name mysql_types.log -exec rm -f {} \;

python3 "${OMEGAUP_ROOT}/stuff/process_mysql_return_types.py" \
	"${OMEGAUP_ROOT}/frontend/tests/runfiles/mysql_types.log"

if [[ "${OMEGAUP_EXPLAIN_LOG:-1}" != "0" ]]; then
	python3 "${OMEGAUP_ROOT}/stuff/process_mysql_explain_logs.py"
fi
