#!/bin/bash

set -e

DIR="$(realpath "$(dirname "$0")")"

"${DIR}/gitserver-stop.sh"

cat > "/tmp/omegaup/gitserver.config.json" <<EOF
{
	"Logging": {
		"File": ""
	},
	"Gitserver": {
		"AllowDirectPushToMaster": true,
		"RootPath": "/tmp/omegaup/problems.git",
		"FrontendAuthorizationProblemRequestURL": "http://localhost/api/authorization/problem/"
	}
}
EOF

/usr/bin/nohup /usr/bin/omegaup-gitserver \
	-config=/tmp/omegaup/gitserver.config.json >> /tmp/omegaup/gitserver.log 2>&1 &
echo $! > /tmp/omegaup/gitserver.pid
