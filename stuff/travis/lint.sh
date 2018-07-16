#!/bin/bash

. "${OMEGAUP_ROOT}/stuff/travis/common.sh"

stage_before_install() {
	git submodule update --init --recursive \
		stuff/hook_tools

	init_frontend_submodules

	pip install --user --upgrade pip
	pip install --user six
	pip install --user https://github.com/google/closure-linter/zipball/master
	python3 -m pip install --user --upgrade pip
	python3 -m pip install --user pylint
	python3 -m pip install --user pep8
	python3.5 -m pip install --user --upgrade pip
	python3.5 -m pip install --user pylint
	python3.5 -m pip install --user pep8

	install_yarn
}

stage_before_script() {
	pear install pear/PHP_CodeSniffer-2.9.1

	setup_phpenv
}

stage_script() {
	yarn install
	yarn build
	yarn test

	python3 stuff/db-migrate.py validate
	python3.5 stuff/hook_tools/lint.py -j4 validate --all < /dev/null
}
