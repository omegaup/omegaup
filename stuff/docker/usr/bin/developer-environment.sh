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
define('OMEGAUP_GITSERVER_SECRET_TOKEN', 'secret');
EOF
ensure_contents "/opt/omegaup/frontend/server/config.php" "${config_contents}"

! read -r -d '' test_config_contents <<EOF
<?php
define('OMEGAUP_DB_HOST', 'mysql');
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

# Ensure that the database version is up to date.
if ! /opt/omegaup/stuff/db-migrate.py --mysql-config-file="${HOME}/.my.cnf" exists ; then
  mysql --defaults-file=/home/ubuntu/.my.cnf \
    -e "CREATE USER IF NOT EXISTS 'omegaup'@'localhost' IDENTIFIED BY 'omegaup';"
  mysql --defaults-file="${HOME}/.my.cnf" \
    -e 'GRANT ALL PRIVILEGES ON `omegaup-test%`.* TO "omegaup"@"%";'
  /opt/omegaup/stuff/bootstrap-environment.py \
    --mysql-config-file="${HOME}/.my.cnf" \
    --purge --verbose --root-url=http://localhost:8001/
else
  /opt/omegaup/stuff/db-migrate.py \
    --mysql-config-file=/home/ubuntu/.my.cnf migrate
fi

# If this is a local-backend build, ensure that the built omegaup-gitserver is
# copied.
if [[ -x /var/lib/omegaup/omegaup-gitserver ]]; then
	sudo mv /var/lib/omegaup/omegaup-gitserver /usr/bin/omegaup-gitserver
fi

exec /usr/bin/composer install
