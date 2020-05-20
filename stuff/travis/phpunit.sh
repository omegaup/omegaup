#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	init_submodules
	install_mysql8
}

stage_install() {
	pip3 install --user --upgrade pip
	pip3 install --user setuptools
	pip3 install --user wheel
	pip3 install --user mysqlclient

	composer install

	install_omegaup_gitserver
}

stage_before_script() {
	wait_for_mysql

	mysql -e 'CREATE DATABASE IF NOT EXISTS `omegaup-test`;'
	mysql -uroot -e "GRANT ALL ON *.* TO 'travis'@'localhost' WITH GRANT OPTION;"
	mysql -uroot -e "CREATE USER 'omegaup'@'localhost' IDENTIFIED BY 'omegaup';"
	python3 stuff/db-migrate.py --username=travis --password= \
		migrate --databases=omegaup-test
	mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = '';"
}

stage_script() {
	./stuff/mysql_types.sh

	# Create optional directories to simplify psalm config.
	mkdir -p frontend/www/{phpminiadmin,preguntas}
	touch 'frontend/server/config.php'
	touch 'frontend/tests/test_config.php'
	./vendor/bin/psalm
}
