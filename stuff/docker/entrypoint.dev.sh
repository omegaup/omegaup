#!/bin/bash
set -e

# Build ReKarel webapp si no existe
if [ ! -f /opt/omegaup/frontend/www/rekarel/webapp/js/cindex.js ]; then
  cd /opt/omegaup/frontend/www/rekarel
  npm install
  npx gulp
fi

# Ejecuta el comando original (supervisord)
exec "$@"
