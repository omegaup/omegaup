#!/bin/sh -e

OMEGAUP_ROOT=`/usr/bin/git rev-parse --show-toplevel`

hhvm /usr/bin/phpunit --bootstrap $OMEGAUP_ROOT/frontend/tests/bootstrap.php --configuration $OMEGAUP_ROOT/frontend/tests/phpunit-coverage.xml $OMEGAUP_ROOT/frontend/tests/controllers/
