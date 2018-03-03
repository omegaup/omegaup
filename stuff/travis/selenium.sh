#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	init_submodules

	install_yarn
}

stage_install() {
	# Install pre-dependencies
	pip3 install --user selenium
	pip3 install --user pytest
	pip3 install --user flaky

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
	/usr/bin/python3 -m pytest "${OMEGAUP_ROOT}/frontend/tests/ui/" -s --browser=chrome
}
