#!/bin/bash

set -e

DIR="$(realpath "$(dirname "$0")")"

"${DIR}/gitserver-stop.sh"

/usr/bin/nohup /usr/bin/omegaup-gitserver \
	-allow-direct-push-to-master \
	"-root=/tmp/omegaup/problems.git/" >> /tmp/omegaup/gitserver.log 2>&1 &
echo $! > /tmp/omegaup/gitserver.pid
