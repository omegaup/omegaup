#!/bin/bash

init_submodules() {
	git submodule update --init --recursive \
		stuff/hook_tools \
		frontend/server/libs/third_party/constant_time_encoding \
		frontend/server/libs/third_party/facebook-php-graph-sdk \
		frontend/server/libs/third_party/google-api-php-client \
		frontend/server/libs/third_party/log4php \
		frontend/server/libs/third_party/paseto \
		frontend/server/libs/third_party/phpmailer \
		frontend/server/libs/third_party/smarty \
		frontend/server/libs/third_party/sodium_compat
}

init_frontend_submodules() {
	git submodule update --init --recursive \
		frontend/www/third_party/js/csv.js \
		frontend/www/third_party/js/iso-3166-2.js \
		frontend/www/third_party/js/mathjax \
		frontend/www/third_party/js/pagedown \
		frontend/www/third_party/wenk
}

wait_for_mysql() {
	# Workaround for Travis' flaky MySQL connection.
	for _ in `seq 30`; do
		mysql -e ';' && break || sleep 1
	done
}

install_yarn() {
	if [ -z "`which nvm`" ]; then
		if [ ! -d ~/.nvm ]; then
			git clone https://github.com/creationix/nvm.git ~/.nvm
			(cd ~/.nvm && git checkout `git describe --abbrev=0 --tags`)
		fi
		source ~/.nvm/nvm.sh
	fi
	nvm install 6.9.1
	npm install -g yarn
}

install_omegaup_gitserver() {
	DOWNLOAD_URL='https://github.com/omegaup/gitserver/releases/download/v1.4.0/omegaup-gitserver.tar.xz'
	curl --location "${DOWNLOAD_URL}" | sudo tar -xJv -C /

	# omegaup-gitserver depends on libinteractive.
	DOWNLOAD_URL='https://github.com/omegaup/libinteractive/releases/download/v2.0.25/libinteractive.jar'
	TARGET='/usr/share/java/libinteractive.jar'
	sudo curl --location "${DOWNLOAD_URL}" -o "${TARGET}"
}

setup_phpenv() {
	phpenv rehash
	echo "include_path='.:/home/travis/.phpenv/versions/$(phpenv version-name)/lib/php/pear/:/home/travis/.phpenv/versions/$(phpenv version-name)/share/pear'" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
}
