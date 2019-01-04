#!/bin/bash

set -e

DIR="$(realpath "$(dirname "$0")")"

/usr/bin/nohup /usr/bin/omegaup-gitserver \
	-port=33862 -pprof-port=-1 \
	-allow-direct-push-to-master \
	"-root=${DIR}/problems.git/" >> "${DIR}/gitserver.log" 2>&1 &
echo $! > "${DIR}/gitserver.pid"
