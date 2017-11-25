#!/bin/bash

stage_before_install() {
	git submodule update --init --recursive \
		stuff/hook_tools \
		frontend/server/libs/third_party/smarty \
		frontend/server/libs/third_party/phpmailer \
		frontend/server/libs/third_party/log4php \
		frontend/server/libs/third_party/adodb \
		frontend/server/libs/third_party/facebook-php-graph-sdk \
		frontend/server/libs/third_party/google-api-php-client
}

stage_before_script() {
	# Workaround for Travis' flaky MySQL connection.
	for _ in `seq 30`; do
		mysql -e ';' && break || sleep 1
	done

	mysql -e 'CREATE DATABASE IF NOT EXISTS `omegaup-test`;'
	mysql -uroot -e "GRANT ALL ON *.* TO 'travis'@'localhost' WITH GRANT OPTION;"
	python3 stuff/db-migrate.py --username=travis --password= \
		migrate --databases=omegaup-test
	mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('');"
}

stage_script() {
	phpunit --bootstrap frontend/tests/bootstrap.php --configuration \
		frontend/tests/phpunit.xml frontend/tests/controllers
	python3 stuff/database_schema.py --database=omegaup-test validate --all < /dev/null
}
