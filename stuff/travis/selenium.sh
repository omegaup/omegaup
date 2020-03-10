#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	init_submodules
	init_frontend_submodules

	# Install pre-dependencies
	sudo ln -sf python3.6 /usr/bin/python3
	python3 -m pip install --user --upgrade pip
	python3 -m pip install --user --upgrade urllib3
	python3 -m pip install --user setuptools
	python3 -m pip install --user selenium
	python3 -m pip install --user pytest
	python3 -m pip install --user pytest-xdist
	python3 -m pip install --user flaky

	install_mysql8
	install_yarn
}

stage_install() {
	install_omegaup_gitserver

	# Expand all templates
	for tpl in `find "${OMEGAUP_ROOT}/stuff/travis/nginx/" -name '*.conf.tpl'`; do
		/bin/sed -e "s%\${OMEGAUP_ROOT}%${OMEGAUP_ROOT}%g" "${tpl}" > "${tpl%.tpl}"
	done

	# Start the servers
	~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm \
		--fpm-config "${OMEGAUP_ROOT}/stuff/travis/nginx/php-fpm.conf"
	nginx -c "${OMEGAUP_ROOT}/stuff/travis/nginx/nginx.conf"

	mkdir -p /tmp/omegaup/{submissions,grade,problems.git}

	# Install the PHP config
	/bin/sed -e "s%\${OMEGAUP_ROOT}%${OMEGAUP_ROOT}%g" \
		"${OMEGAUP_ROOT}/stuff/travis/nginx/config.php.tpl" > \
		"${OMEGAUP_ROOT}/frontend/server/config.php"

	wait_for_mysql

	setup_phpenv

	mysql -e 'CREATE DATABASE IF NOT EXISTS `omegaup`;'
	mysql -uroot -e "GRANT ALL ON *.* TO 'travis'@'localhost' WITH GRANT OPTION;"
	mysql -uroot -e "CREATE USER 'omegaup'@'localhost' IDENTIFIED BY 'omegaup';"
	mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = '';"

	yarn install
	yarn build-development

	stuff/travis/nginx/gitserver-start.sh

	# Install the database schema
	python3 stuff/db-migrate.py --username=travis --password= \
		migrate --databases=omegaup --development-environment
	# As well as installing some users and problems
	python3 stuff/bootstrap-environment.py --root-url=http://localhost:8000
}

stage_before_script() {
	# Intentionally left blank.
	# Nothing should be here to prevent Sauce Labs timeouts.
	:
}

stage_script() {
	# TODO(https://github.com/omegaup/omegaup/issues/1798): Reenable Firefox
	python3 -m pytest "${OMEGAUP_ROOT}/frontend/tests/ui/" \
		--verbose --capture=no --log-cli-level=INFO --browser=chrome \
		--force-flaky --max-runs=2 --min-passes=1 --numprocesses=4
}

stage_after_failure() {
	cat /tmp/omegaup/gitserver.log
}

stage_after_script() {
	stuff/travis/nginx/gitserver-stop.sh
}
