#!/bin/bash

set -e

DIR="$(realpath "$(dirname "$0")")"
PIDFILE="${DIR}/gitserver.pid"

if [ -f "${PIDFILE}" ]; then
	# Ignore failures in case the pidfile was stale.
	kill "$(cat "${PIDFILE}")" 2>/dev/null || true
	rm "${PIDFILE}"
fi
