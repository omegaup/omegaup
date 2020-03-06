#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	init_submodules

	if [[ "${UBUNTU}" == "focal" ]]; then
		# In addition to the newer PHP version, this needs MySQL 8.
		sudo apt-key adv --keyserver keys.gnupg.net --recv-keys 8C718D3B5072E1F5
		wget https://repo.mysql.com/mysql-apt-config_0.8.13-1_all.deb
		sudo dpkg -i mysql-apt-config_0.8.13-1_all.deb
		sudo apt-get update -q
		sudo apt-get install -q -y --allow-unauthenticated -o Dpkg::Options::=--force-confnew mysql-server

		sudo systemctl restart mysql

		sudo mysql_upgrade
	fi
}

stage_install() {
	pip3 install --user --upgrade pip
	pip3 install --user setuptools
	pip3 install --user wheel
	pip3 install --user mysqlclient

	curl -sSfL -o ~/.phpenv/versions/$(phpenv version-name)/bin/phpunit \
		https://phar.phpunit.de/phpunit-8.5.2.phar
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
	if [[ "${UBUNTU}" == "focal" ]]; then
		# MySQL 8 uses a _slightly_ different syntax.
		mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = '';"
	else
		mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('');"
	fi
}

stage_script() {
	phpunit --bootstrap frontend/tests/bootstrap.php \
		--configuration=frontend/tests/phpunit.xml \
		--coverage-clover=coverage.xml \
		frontend/tests/controllers
	mv frontend/tests/controllers/mysql_types.log \
		frontend/tests/controllers/mysql_types.log.1
	phpunit --bootstrap frontend/tests/bootstrap.php \
		--configuration=frontend/tests/phpunit.xml \
		frontend/tests/badges
	if [[ "${UBUNTU}" != "focal" ]]; then
		mv frontend/tests/controllers/mysql_types.log \
			frontend/tests/controllers/mysql_types.log.2
		cat frontend/tests/controllers/mysql_types.log.1 \
			frontend/tests/controllers/mysql_types.log.2 > \
			frontend/tests/controllers/mysql_types.log
		python3 stuff/process_mysql_return_types.py \
			frontend/tests/controllers/mysql_types.log
		python3 stuff/database_schema.py --database=omegaup-test validate --all < /dev/null
		python3 stuff/policy-tool.py --database=omegaup-test validate
	fi

	# Create optional directories to simplify psalm config.
	mkdir -p frontend/www/{phpminiadmin,preguntas}
	touch 'frontend/server/config.php'
	touch 'frontend/tests/test_config.php'
	./vendor/bin/psalm --update-baseline --show-info=false

	if [[ "$(/usr/bin/git status --porcelain psalm.baseline.xml)" != "" ]]; then
		/usr/bin/git diff -- psalm.baseline.xml
		>&2 echo "Some psalm errors have been fixed! Please run:"
		>&2 echo ""
		>&2 echo "    ./vendor/bin/psalm --show-info=false --update-baseline"
		exit 1
	fi
}

stage_after_success() {
	bash <(curl -s https://codecov.io/bash)
}

stage_after_failure() {
	cat frontend/tests/controllers/gitserver.log
}
