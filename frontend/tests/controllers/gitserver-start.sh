#!/bin/bash

set -e

if [[ $# -ne 2 ]]; then
	echo "Usage: $0 <port> <problems.git path>"
	exit 1
fi

DIR="$(realpath "$(dirname "$0")")"
PORT="$1"
ROOT_PATH="$2"

"${DIR}/gitserver-stop.sh"

cat > "${DIR}/gitserver.config.json" <<EOF
{
	"Logging": {
		"File": ""
	},
	"Gitserver": {
		"Port": ${PORT},
		"PprofPort": 0,
		"SecretToken": "cbaf89d3bb2ee6b0a90bc7a90d937f9ade16739ed9f573c76e1ac72064e397aac2b35075040781dd0df9a8f1d6fc4bd4a4941eb6b0b62541b0a35fb0f89cfc3f",
		"PublicKey": "",
		"AllowDirectPushToMaster": true,
		"RootPath": "${ROOT_PATH}"
	}
}
EOF

/usr/bin/nohup /usr/bin/omegaup-gitserver \
	-insecure-skip-authorization \
	-config="${DIR}/gitserver.config.json" >> "${DIR}/gitserver.log" 2>&1 &
echo $! > "${DIR}/gitserver.pid"
