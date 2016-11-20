#!/bin/sh -e

OMEGAUP_ROOT=$(/usr/bin/git rev-parse --show-toplevel)
REF=$(git rev-parse --abbrev-ref --symbolic-full-name @{u} 2>/dev/null || true)

if [ "$REF" = "" ]; then
	echo "This branch has no remote set, running against working directory." >&2
	echo "If you want to run them, use" >&2
	echo >&2
	echo "    git branch --set-upstream-to=<upstream>" >&2
	echo >&2
fi

FILTER_ARG=""
if [ -n "$1" ]; then
	FILTER_ARG="--filter $1"
fi

$OMEGAUP_ROOT/stuff/git-hooks/pre-push $REF
hhvm /usr/bin/phpunit \
	--bootstrap $OMEGAUP_ROOT/frontend/tests/bootstrap.php \
	--configuration $OMEGAUP_ROOT/frontend/tests/phpunit.xml \
	$FILTER_ARG $OMEGAUP_ROOT/frontend/tests/controllers
