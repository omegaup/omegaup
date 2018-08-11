#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	init_submodules
	init_frontend_submodules

	# Install pre-dependencies
	python3.5 -m pip install --user --upgrade pip
	# TODO: Figure out why 3.14.0 is broken
	python3.5 -m pip install --user selenium==3.13.0
	python3.5 -m pip install --user pytest
	python3.5 -m pip install --user pytest-xdist
	python3.5 -m pip install --user flaky

	install_yarn
}

stage_install() {
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
}

stage_before_script() {
	wait_for_mysql

	setup_phpenv

	mysql -e 'CREATE DATABASE IF NOT EXISTS `omegaup`;'
	mysql -uroot -e "GRANT ALL ON *.* TO 'travis'@'localhost' WITH GRANT OPTION;"
	mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('');"

	yarn install
	yarn build-development

	# Install the database schema
	python3 stuff/db-migrate.py --username=travis --password= \
		migrate --databases=omegaup --development-environment
	# As well as installing some users and problems
	python3 stuff/bootstrap-environment.py --root-url=http://localhost:8000
}

stage_script() {
	# TODO(https://github.com/omegaup/omegaup/issues/1798): Reenable Firefox
	python3.5 -m pytest "${OMEGAUP_ROOT}/frontend/tests/ui/" \
		--verbose --capture=no --log-cli-level=INFO --browser=chrome \
		--force-flaky --max-runs=2 --min-passes=1 --numprocesses=4
}
