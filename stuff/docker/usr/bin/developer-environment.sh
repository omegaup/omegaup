#!/bin/bash

set -e

if [[ ! -f /opt/omegaup/frontend/server/config.php ]]; then
  cat > /opt/omegaup/frontend/server/config.php <<EOF
<?php
define('OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT', true);
define('OMEGAUP_CSP_LOG_FILE', '/tmp/csp.log');
define('OMEGAUP_DB_HOST', 'mysql');
define('OMEGAUP_DB_NAME', 'omegaup');
define('OMEGAUP_DB_PASS', 'omegaup');
define('OMEGAUP_DB_USER', 'omegaup');
define('OMEGAUP_ENVIRONMENT', 'development');
define('OMEGAUP_LOG_FILE', '/tmp/omegaup.log');
define('OMEGAUP_ENABLE_SOCIAL_MEDIA_RESOURCES', false);
define('SMARTY_CACHE_DIR', '/tmp');
define('OMEGAUP_CACERT_URL', '/etc/omegaup/frontend/certificate.pem');
define('OMEGAUP_SSLCERT_URL', '/etc/omegaup/frontend/certificate.pem');
define('OMEGAUP_GITSERVER_URL', 'http://gitserver:33861');
define('OMEGAUP_GRADER_URL', 'https://grader:21680');
EOF
fi

if ! /opt/omegaup/stuff/db-migrate.py --mysql-config-file=/home/ubuntu/.my.cnf exists ; then
  mysql --defaults-file=/home/ubuntu/.my.cnf \
    -e "CREATE USER IF NOT EXISTS 'omegaup'@'localhost' IDENTIFIED BY 'omegaup';"
  /opt/omegaup/stuff/bootstrap-environment.py \
    --mysql-config-file=/home/ubuntu/.my.cnf \
    --purge --verbose --root-url=http://localhost:8000/
else
  /opt/omegaup/stuff/db-migrate.py \
    --mysql-config-file=/home/ubuntu/.my.cnf migrate
fi

exec /usr/bin/composer install
