#!/bin/bash

set -e
set -x

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)

# Load the correct provider.
case "$1" in
phpunit)
	. "${OMEGAUP_ROOT}/stuff/travis/phpunit.sh"
	;;
lint)
	. "${OMEGAUP_ROOT}/stuff/travis/lint.sh"
	;;
*)
	echo "Could not load the test suite for '$1'." > /dev/stderr
	exit 1
esac

# Run the correct test stage.
case "$2" in
before_install)
  before_install
	;;
before_script)
  before_script
	;;
script)
  script
	;;
*)
	echo "Could not execute the stage '$2'." > /dev/stderr
	exit 1
esac
