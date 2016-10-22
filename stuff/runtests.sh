#!/bin/sh -e

OMEGAUP_ROOT=`/usr/bin/git rev-parse --show-toplevel`
REMOTE=`git rev-parse --abbrev-ref --symbolic-full-name @{u}`

$OMEGAUP_ROOT/stuff/git-hooks/pre-push $REMOTE
hhvm /usr/bin/phpunit --bootstrap $OMEGAUP_ROOT/frontend/tests/bootstrap.php --configuration $OMEGAUP_ROOT/frontend/tests/phpunit.xml $OMEGAUP_ROOT/frontend/tests/controllers/$1
