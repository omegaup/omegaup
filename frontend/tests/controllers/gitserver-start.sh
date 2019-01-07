#!/bin/bash

set -e

DIR="$(realpath "$(dirname "$0")")"

"${DIR}/gitserver-stop.sh"

/usr/bin/nohup /usr/bin/omegaup-gitserver \
	-port=33863 -pprof-port=-1 \
	-secret-token=cbaf89d3bb2ee6b0a90bc7a90d937f9ade16739ed9f573c76e1ac72064e397aac2b35075040781dd0df9a8f1d6fc4bd4a4941eb6b0b62541b0a35fb0f89cfc3f \
	-public-key= \
	-allow-direct-push-to-master \
	"-root=${DIR}/problems.git/" >> "${DIR}/gitserver.log" 2>&1 &
echo $! > "${DIR}/gitserver.pid"
