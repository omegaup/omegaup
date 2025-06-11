#!/bin/bash
set -e

# Build ReKarel webapp if not exists
if [ ! -f /opt/omegaup/frontend/www/rekarel/webapp/js/cindex.js ]; then
  cd /opt/omegaup/frontend/www/rekarel
  npm install
  npx gulp
fi

# Execute the original command
exec "$@"
