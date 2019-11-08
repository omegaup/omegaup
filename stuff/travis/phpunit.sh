#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	init_submodules

	sudo ln -sf python3.6 /usr/bin/python3
}

stage_install() {
	pip3 install --user --upgrade pip
	pip3 install --user setuptools
	pip3 install --user wheel
	pip3 install --user mysqlclient

	curl -sSfL -o ~/.phpenv/versions/$(phpenv version-name)/bin/phpunit \
		https://phar.phpunit.de/phpunit-6.5.9.phar
	composer install

	install_omegaup_gitserver
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
	phpunit --bootstrap frontend/tests/bootstrap.php \
		--configuration=frontend/tests/phpunit.xml \
		frontend/tests/badges
	python3 stuff/database_schema.py --database=omegaup-test validate --all < /dev/null
	python3 stuff/policy-tool.py --database=omegaup-test validate

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
