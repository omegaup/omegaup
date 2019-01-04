#!/bin/bash

set -e

DIR="$(realpath "$(dirname "$0")")"
PIDFILE="${DIR}/gitserver.pid"

if [ -f "${PIDFILE}" ]; then
	kill "$(cat "${PIDFILE}")"
	rm "${PIDFILE}"
fi
