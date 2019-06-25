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
	python3 -m pip install --user setuptools
	python3 -m pip install --user wheel
	python3 -m pip install --user awscli

	install_yarn
}

stage_before_script() {
	setup_phpenv
}

stage_script() {
	rm -rf frontend/www/{js,css,media}/dist
	yarn install
	yarn run build
	yarn test

	python3 stuff/db-migrate.py validate
	docker run --rm -v "$PWD:/src" -v "$PWD:/opt/omegaup" omegaup/hook_tools -j4 validate --all < /dev/null
}

stage_after_success() {
	if [[ "${TRAVIS_PULL_REQUEST}" == "false" ]]; then
		mkdir -p build/webpack-artifacts

		# Upload a tarball with the build artifacts.
		local tarball="build/webpack-artifacts/${TRAVIS_COMMIT}.tar.xz"
		tar --xz --create --file "${tarball}" -C frontend/www js/dist css/dist media/dist
		aws s3 cp "${tarball}" "s3://omegaup-build-artifacts/webpack-artifacts/${TRAVIS_COMMIT}.tar.xz"

		# Start a deployment now that the build artifacts are done.
		curl -H "${GITHUB_OAUTH_TOKEN}" \
			https://api.github.com/repos/omegaup/omegaup/deployments \
			--data "{\"ref\":\"${TRAVIS_BRANCH}\",\"required_contexts\":[]}"
	fi
}
