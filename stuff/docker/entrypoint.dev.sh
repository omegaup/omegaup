#!/bin/bash
set -e

cd /opt/omegaup/frontend/www/rekarel
npm install
npx gulp
npm run build

# Execute the original command
exec "$@"
