#!/bin/bash

set -e

if [[ "${CI}" == "true" ]]; then
	# When running in a CI environment, pnpm is run outside.
	echo "CI=true was passed, not running pnpm."
	exec sleep infinity
fi

cd /opt/omegaup
corepack enable
pnpm install
exec pnpm run dev-all:watch
