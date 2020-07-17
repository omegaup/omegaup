#!/bin/bash

set -e

if [[ "${CI}" == "true" ]]; then
	# When running in a CI environment, yarn is run outside.
	echo "CI=true was passed, not running yarn."
	exec sleep infinity
fi

cd /opt/omegaup
yarn install
exec yarn run dev-all:watch
