#!/bin/bash

set -e

if [[ $# -ne 1 ]]; then
	echo "Usage: $0 <runfiles directory>"
	exit 1
fi

RUNFILES="$1"
PIDFILE="${RUNFILES}/gitserver.pid"

if [ -f "${PIDFILE}" ]; then
	# Ignore failures in case the pidfile was stale.
	kill "$(cat "${PIDFILE}")" 2>/dev/null || true
	rm "${PIDFILE}"
fi
