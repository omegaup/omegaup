#!/bin/bash
set -e

cd /opt/omegaup/frontend/www/rekarel
npm install
npx gulp

# Execute the original command
exec "$@"
