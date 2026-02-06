#!/bin/bash

set -e

if [[ "${CI}" == "true" ]]; then
  # When running in a CI environment, the database/composer setup is done
  # outside.
  echo "CI=true was passed, not running database/composer setup."
  exit 0
fi

function ensure_contents() {
  local path="$1"
  local contents="$2"

  local expected_hash="$(echo "${contents}" | sha1sum 2>/dev/null | sed -e 's/\s.*//' || true)"
  local actual_hash="$(sha1sum "${path}" 2>/dev/null | sed -e 's/\s.*//' || true)"

  if [[ "${expected_hash}" == "${actual_hash}" ]]; then
    return
  fi
  echo "${contents}" | cat > "${path}"
}

# Create a directory for Psalm's benefit.
if [[ ! -d /opt/omegaup/frontend/www/phpminiadmin ]]; then
  mkdir -p /opt/omegaup/frontend/www/phpminiadmin
fi

# Create configuration files.
! read -r -d '' config_contents <<EOF
<?php
define('OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT', true);
define('OMEGAUP_CACHE_IMPLEMENTATION', 'redis');
define('OMEGAUP_CSP_LOG_FILE', '/tmp/csp.log');
define('OMEGAUP_DB_HOST', 'mysql:13306');
define('OMEGAUP_DB_NAME', 'omegaup');
define('OMEGAUP_DB_PASS', 'omegaup');
define('OMEGAUP_DB_USER', 'omegaup');
define('OMEGAUP_ENVIRONMENT', 'development');
define('OMEGAUP_LOG_FILE', '/tmp/omegaup.log');
define('OMEGAUP_ENABLE_SOCIAL_MEDIA_RESOURCES', false);
define('TEMPLATE_CACHE_DIR', '/tmp/templates');
define('OMEGAUP_GITSERVER_URL', 'http://omegaup-gitserver-1:33861');
define('OMEGAUP_GRADER_URL', 'https://grader:21680');
define('OMEGAUP_GITSERVER_SECRET_TOKEN', 'secret');
define('OMEGAUP_GITSERVER_SECRET_KEY', 'GdhxduUWe/y18iCnEWbTFX+JE4O8vSQPTUkjWtWf6ASAoSDkmUg4DUGwjERNliGN35kZyFj+tl5AzQaF4Ba9fA==');
define('OMEGAUP_GITSERVER_PUBLIC_KEY', 'gKEg5JlIOA1BsIxETZYhjd+ZGchY/rZeQM0GheAWvXw=');
define('OMEGAUP_GRADER_SECRET', 'secret');
define('OMEGAUP_COURSE_CLONE_SECRET_KEY', '6f8xSU_xkrelmCTSahbbxl3PRovgAfkrThyrqQ9JesE');
define('OMEGAUP_GOOGLE_SECRET', 'acmtr0Y37vnTVJV4BwmdhOsK');
define('OMEGAUP_GOOGLE_CLIENTID', '982542692060-lf9htvij4ba13fiufpqeldic0qqqvird.apps.googleusercontent.com');
define('OMEGAUP_CSRF_HOSTS', ['frontend', '127.0.0.1']);
EOF
ensure_contents "/opt/omegaup/frontend/server/config.php" "${config_contents}"

! read -r -d '' test_config_contents <<EOF
<?php
define('OMEGAUP_DB_HOST', 'mysql:13306');
define('OMEGAUP_DB_PASS', 'omegaup');
define('OMEGAUP_DB_USER', 'omegaup');
EOF
ensure_contents "/opt/omegaup/frontend/tests/test_config.php" "${test_config_contents}"

# Install all the git hooks.
for hook in /opt/omegaup/stuff/git-hooks/*; do
  hook_name="$(basename "${hook}")"
  hook_path="/opt/omegaup/.git/hooks/${hook_name}"
  if [[ ! -L "${hook_path}" ]]; then
    ln -sf "../../stuff/git-hooks/${hook_name}" "${hook_path}"
  fi
done

# Create the virtual environment
if [[ ! -d /opt/omegaup/stuff/venv/lib/python3.8/site-packages/wheel ]]; then
  python3 -m venv /opt/omegaup/stuff/venv
  python3 -m pip install wheel
fi
python3 -m pip install -r /opt/omegaup/stuff/requirements.txt

# Ensure that the database version is up to date.
if ! /opt/omegaup/stuff/db-migrate.py --mysql-config-file="${HOME}/.my.cnf" exists ; then
  mysql --defaults-file="${HOME}/.my.cnf" \
    -e "CREATE USER IF NOT EXISTS 'omegaup'@'localhost' IDENTIFIED BY 'omegaup';"
  mysql --defaults-file="${HOME}/.my.cnf" \
    -e 'GRANT ALL PRIVILEGES ON `omegaup-test%`.* TO "omegaup"@"%";'
  /opt/omegaup/stuff/bootstrap-environment.py \
    --mysql-config-file="${HOME}/.my.cnf" \
    --purge --verbose --root-url=http://localhost:8001/
else
  /opt/omegaup/stuff/db-migrate.py \
    --mysql-config-file="${HOME}/.my.cnf" migrate
fi

# If this is a local-backend build, ensure that the built omegaup-gitserver is
# copied.
if [[ -x /var/lib/omegaup/omegaup-gitserver ]]; then
  sudo mv /var/lib/omegaup/omegaup-gitserver /usr/bin/omegaup-gitserver
fi

exec /usr/bin/composer install
