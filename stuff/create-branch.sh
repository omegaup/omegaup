#!/bin/bash

set -e

if [ $# -lt 1 ]; then
	echo "Usage: $0 <branch-name>"
	exit 1
fi

BRANCH_NAME="$1"
UPSTREAM_NAME="${UPSTREAM_NAME:-upstream}"
REMOTE_NAME="${REMOTE_NAME:-origin}"

# Use the new push default.
git config --global push.default >/dev/null || \
	git config --global push.default simple

git fetch "${UPSTREAM_NAME}" master
git checkout "${UPSTREAM_NAME}/master" -b "${BRANCH_NAME}"
# We use --no-verify to avoid running the pre-upload hooks. Since we are just
# cloning upstream/master, we know that it must be clean.
git push -u "${REMOTE_NAME}" "HEAD:${BRANCH_NAME}" -f --no-verify
