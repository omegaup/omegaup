#!/bin/bash

set -e

OMEGAUP_ROOT="$(git rev-parse --show-toplevel)"

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

if [[ -t 0 ]]; then
	# This is being run in an environment where stdin is connected to a TTY.
	TTY_ARGS="--interactive --tty"
else
	TTY_ARGS=""
fi

if [[ -d /proc ]] && grep -q pids:/docker /proc/1/cgroup; then
	echo "Running ./stuff/lint.sh inside a container is not supported." 1>&2
	echo "Please run this command outside the container" 1>&2
	exit 1
fi
DOCKER_PATH="$(which docker)"
if [[ -z "${DOCKER_PATH}" ]]; then
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
	--env 'PYTHONIOENCODING=utf-8' \
	--env "MYPYPATH=${OMEGAUP_ROOT}/stuff" \
	omegaup/hook_tools:20210420 --command-name="./stuff/lint.sh" $ARGS

echo OK
