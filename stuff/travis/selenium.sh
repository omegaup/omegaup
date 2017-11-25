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

	if [ -z "`which nvm`" ]; then
		if [ ! -d ~/.nvm ]; then
			git clone https://github.com/creationix/nvm.git ~/.nvm
			(cd ~/.nvm && git checkout `git describe --abbrev=0 --tags`)
		fi
		source ~/.nvm/nvm.sh
	fi
	nvm install 6.9.1
	npm install -g yarn

	phpenv rehash
	echo "include_path='.:/home/travis/.phpenv/versions/$(phpenv version-name)/lib/php/pear/:/home/travis/.phpenv/versions/$(phpenv version-name)/share/pear'" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
}

stage_install() {
	pip3 install --user selenium
	pip3 install --user pytest

	"~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm" \
		--fpm-config "${OMEGAUP_ROOT}/stuff/travis/nginx/php-fpm.conf"

	nginx -c "${OMEGAUP_ROOT}/stuff/travis/nginx/nginx.conf"
}

stage_before_script() {
	# Workaround for Travis' flaky MySQL connection.
	for _ in `seq 30`; do
		mysql -e ';' && break || sleep 1
	done

	mysql -e 'CREATE DATABASE IF NOT EXISTS `omegaup`;'
	mysql -uroot -e "GRANT ALL ON *.* TO 'travis'@'localhost' WITH GRANT OPTION;"
	python3 stuff/db-migrate.py --username=travis --password= \
		migrate --databases=omegaup
	mysql -uroot -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('');"

	yarn install
	yarn build-development
}

stage_script() {
	/usr/bin/python3 -m pytest "${OMEGAUP_ROOT}/frontend/tests/ui/" -s
}
