#!/bin/bash

set -e

cd /opt/omegaup
yarn install
exec yarn run dev-all
