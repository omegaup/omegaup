#!/bin/sh -e

OMEGAUP_ROOT=`/usr/bin/git rev-parse --show-toplevel`

hhvm /usr/bin/phpunit --bootstrap $OMEGAUP_ROOT/frontend/tests/bootstrap.php --configuration $OMEGAUP_ROOT/frontend/tests/phpunit.xml $OMEGAUP_ROOT/frontend/tests/controllers/$1
$OMEGAUP_ROOT/stuff/git-hooks/pre-push origin
