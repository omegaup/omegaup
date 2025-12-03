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
define('OMEGAUP_CSRF_HOSTS', ['frontend', '127.0.0.1']);
define('APC_USER_CACHE_CONTEST_INFO_TIMEOUT', 10);
define('MAX_PROBLEMS_IN_CONTEST', 30);
define(
    'IMAGES_PATH',
    sprintf('%s/www/img/', strval(OMEGAUP_ROOT))
);
define('IMAGES_URL_PATH', '/img/');
define('APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT', 60);
define('APC_USER_CACHE_PROBLEM_STATS_TIMEOUT', 0);
define('APC_USER_CACHE_PROBLEM_LIST_TIMEOUT', 60 * 30);
define('IDENTITY_ANONYMOUS', 'identity_anonymous');
define('IDENTITY_ADMIN', 'identity_admin');
define('IDENTITY_NORMAL', 'identity_normal');
define('APC_USER_CACHE_SESSION_TIMEOUT', 8 * 3600);
define(
    'TEMPLATES_PATH',
    sprintf('%s/www/templates/', strval(OMEGAUP_ROOT))
);
define(
    'INPUTS_PATH',
    sprintf('%s/www/probleminput/', strval(OMEGAUP_ROOT))
);
define('OMEGAUP_URL', 'http://localhost');
define('PASSWORD_RESET_TIMEOUT', 2 * 3600);
define('PASSWORD_RESET_MIN_WAIT', 5 * 60);
define('AWS_CLI_SECRET_ACCESS_KEY', null);
define('AWS_CLI_ACCESS_KEY_ID', null);
define('OMEGAUP_GRADER_SECRET', 'secret');
define('OMEGAUP_FB_APPID', 'xxxxx');
define('OMEGAUP_AUTH_TOKEN_COOKIE_NAME', 'ouat');
define('OMEGAUP_FB_SECRET', 'xxxxx');
define('OMEGAUP_MD5_SALT', 'omegaup');
define(
    'OMEGAUP_GOOGLE_CLIENTID',
    '982542692060-lf9htvij4ba13fiufpqeldic0qqqvird.apps.googleusercontent.com'
);
define('OMEGAUP_RECAPTCHA_SECRET', 'xxxx');

define('OMEGAUP_EMAIL_SENDY_SUBSCRIBE_URL', 'xxx');
define('OMEGAUP_EMAIL_SENDY_LIST', 'xxx');
define('APC_USER_CACHE_USER_RANK_TIMEOUT', 60 * 30);
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
