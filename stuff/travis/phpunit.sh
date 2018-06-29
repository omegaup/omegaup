#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	init_submodules
}

stage_install() {
	pip3 install --user mysqlclient
}

stage_before_script() {
	wait_for_mysql

	mysql -e 'CREATE DATABASE IF NOT EXISTS `omegaup-test`;'
	mysql -uroot -e "GRANT ALL ON *.* TO 'travis'@'localhost' WITH GRANT OPTION;"
	python3 stuff/db-migrate.py --username=travis --password= \
		migrate --databases=omegaup-test
	mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('');"
}

stage_script() {
	phpunit --bootstrap frontend/tests/bootstrap.php \
		--configuration=frontend/tests/phpunit.xml \
		--coverage-clover=coverage.xml \
		frontend/tests/controllers
	python3 stuff/database_schema.py --database=omegaup-test validate --all < /dev/null
	python3 stuff/policy-tool.py validate
}

stage_after_success() {
	bash <(curl -s https://codecov.io/bash)
}
