#!/bin/bash

set -e

OMEGAUP_ROOT="$(git rev-parse --show-toplevel)"
CONTAINER_VERSION=omegaup/hook_tools:v1.0.9

if [[ $# != 0 ]]; then
	# The caller has given us the explicit arguments.
	ARGS="$@"
else
	# Try to guess the set of changed files. Only specifying one commit so it
	# diffs against the current working tree.
	REMOTE="origin"
	if [ -d "${OMEGAUP_ROOT}/.git/refs/remotes/upstream" ]; then
		REMOTE="upstream"
	fi
	REMOTE_HASH="$(git rev-parse "${REMOTE}/main")"
	MERGE_BASE="$(git merge-base "${REMOTE_HASH}" HEAD)"
	ARGS="fix ${MERGE_BASE}"
fi

cd "${OMEGAUP_ROOT}"
# ponytail: eslint-plugin-vue template rules are not in oxlint. They are dropped.
OX_GLOBS=(
	'frontend/www/**/*.ts'
	'frontend/www/**/*.vue'
	'frontend/www/js/**/*.js'
	'frontend/www/**/*.css'
	'frontend/www/**/*.scss'
	'cypress/**/*.ts'
	'cypress/**/*.js'
)
ox_start=$(date +%s)
pnpm exec oxlint --deny-warnings frontend/www cypress
ox_mid=$(date +%s)
if [[ " ${ARGS} " == *" validate "* ]]; then
	pnpm exec oxfmt --check "${OX_GLOBS[@]}"
	pnpm exec stylelint 'frontend/www/**/*.css' 'frontend/www/**/*.scss'
else
	pnpm exec oxfmt "${OX_GLOBS[@]}"
	pnpm exec stylelint --fix 'frontend/www/**/*.css' 'frontend/www/**/*.scss'
fi
ox_end=$(date +%s)
echo "oxlint_seconds=$((ox_mid - ox_start)) oxfmt_seconds=$((ox_end - ox_mid))"

refactor_failed=0
while IFS= read -r js_file; do
	js_tmp="$(mktemp)"
	cp "${js_file}" "${js_tmp}"
	node stuff/refactor.js "${js_tmp}" "${js_file}"
	if ! cmp -s "${js_tmp}" "${js_file}"; then
		if [[ " ${ARGS} " == *" validate "* ]]; then
			echo "refactor.js would change ${js_file}" >&2
			refactor_failed=1
		else
			cp "${js_tmp}" "${js_file}"
		fi
	fi
	rm -f "${js_tmp}"
done < <(git ls-files 'frontend/www/js/**/*.js' | grep -v third_party | grep -v 'omegaup/lang.')
if (( refactor_failed != 0 )); then
	exit 1
fi

if [[ -t 0 ]]; then
	# This is being run in an environment where stdin is connected to a TTY.
	TTY_ARGS="--interactive --tty"
else
	TTY_ARGS=""
fi

if [[ "${OMEGAUP_ROOT}" == "/opt/omegaup" ]]; then
	echo "Running ./stuff/lint.sh inside a container is not supported." 1>&2
	echo "Please run this command outside the container" 1>&2
	exit 1
fi
DOCKER_PATH="$(which docker)"
if [[ $? != 0 || -z "${DOCKER_PATH}" ]]; then
	echo "Docker binary not found." 1>&2
	echo "Please install docker or run this command outside the container." 1>&2
	exit 1
fi

"${DOCKER_PATH}" run $TTY_ARGS --rm \
	--user "$(id -u):$(id -g)" \
	--env "GIT_AUTHOR_NAME=$(git config user.name)" \
	--env "GIT_AUTHOR_EMAIL=$(git config user.email)" \
	--env "GIT_COMMITTER_NAME=$(git config user.name)" \
	--env "GIT_COMMITTER_EMAIL=$(git config user.email)" \
	--volume "${OMEGAUP_ROOT}:/src" \
	--volume "${OMEGAUP_ROOT}:${OMEGAUP_ROOT}" \
	--volume "${OMEGAUP_ROOT}:/opt/omegaup" \
	--env 'PYTHONIOENCODING=utf-8' \
	--env "MYPYPATH=${OMEGAUP_ROOT}/stuff" \
	--env "VIRTUAL_ENV=${OMEGAUP_ROOT}/stuff/venv" \
	--entrypoint='' \
	"${CONTAINER_VERSION}" bash -c "PATH=\"${OMEGAUP_ROOT}/stuff/venv/bin:\$PATH\" exec python3 -m omegaup_hook_tools --command-name=\"./stuff/lint.sh\" ${ARGS}"

echo OK
