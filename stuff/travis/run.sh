#!/bin/bash

set -e
set -x

OMEGAUP_ROOT="$(/usr/bin/git rev-parse --show-toplevel)"

# Load the correct provider.
case "$1" in
phpunit)
	. "${OMEGAUP_ROOT}/stuff/travis/phpunit.sh"
	;;
lint)
	. "${OMEGAUP_ROOT}/stuff/travis/lint.sh"
	;;
selenium)
	. "${OMEGAUP_ROOT}/stuff/travis/selenium.sh"
	;;
*)
	echo "Could not load the test suite for '$1'." > /dev/stderr
	exit 1
esac

# Run the correct test stage.
case "$2" in
before_install)
	if [ "`type -t stage_before_install`" = "function" ]; then
		stage_before_install
	fi
	;;
install)
	if [ "`type -t stage_install`" = "function" ]; then
		stage_install
	fi
	;;
before_script)
	if [ "`type -t stage_before_script`" = "function" ]; then
		stage_before_script
	fi
	;;
script)
	if [ "`type -t stage_script`" = "function" ]; then
		stage_script
	fi
	;;
after_success)
	if [ "`type -t stage_after_success`" = "function" ]; then
		stage_after_success
	fi
	;;
after_failure)
	if [ "`type -t stage_after_failure`" = "function" ]; then
		stage_after_failure
	fi
	;;
after_script)
	if [ "`type -t stage_after_script`" = "function" ]; then
		stage_after_script
	fi
	;;
*)
	echo "Could not execute the stage '$2'." > /dev/stderr
	exit 1
esac
