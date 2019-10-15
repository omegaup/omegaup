#!/bin/bash

OMEGAUP_ROOT="$(/usr/bin/git rev-parse --show-toplevel)"

if [[ $# != 0 ]]; then
	# The caller has given us the explicit arguments.
	ARGS="$@"
else
	# Try to guess the set of changed files.
	REMOTE="origin"
	if [ -d "${OMEGAUP_ROOT}/.git/refs/remotes/upstream" ]; then
		REMOTE="upstream"
	fi
	REMOTE_HASH="$(/usr/bin/git rev-parse "${REMOTE}/master")"
	MERGE_BASE="$(/usr/bin/git merge-base "${REMOTE_HASH}" HEAD)"
	ARGS="fix ${MERGE_BASE}"
fi

exec /usr/bin/docker run --interactive --tty --rm \
	--volume "${OMEGAUP_ROOT}:/src" \
	--volume "${OMEGAUP_ROOT}:${OMEGAUP_ROOT}" \
	--env 'PYTHONIOENCODING=utf-8' \
	omegaup/hook_tools:20191013 -j4 $ARGS
