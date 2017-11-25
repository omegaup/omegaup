#!/bin/bash

stage_before_install() {
	git submodule update --init --recursive \
		stuff/hook_tools

	pip install --user six
	pip install --user https://github.com/google/closure-linter/zipball/master
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

stage_before_script() {
	pear install pear/PHP_CodeSniffer-2.9.1
	phpenv rehash
	echo "include_path='.:/home/travis/.phpenv/versions/$(phpenv version-name)/lib/php/pear/:/home/travis/.phpenv/versions/$(phpenv version-name)/share/pear'" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
}

stage_script() {
	yarn install
	yarn build
	yarn test

	python3 stuff/i18n.py --validate < /dev/null
	python3 stuff/hook_tools/lint.py -j4 validate --all < /dev/null
}
