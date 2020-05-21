#!/bin/bash

set -e

if [[ "${CONTINUOUS_INTEGRATION}" == "true" ]]; then
	exit 0
fi

cd /opt/omegaup
yarn install
exec yarn run dev-all
