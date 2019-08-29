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
else
	# Only do pre-push check when no test filter was specified
	$OMEGAUP_ROOT/stuff/git-hooks/pre-push $REF
fi

/usr/bin/python3 $OMEGAUP_ROOT/stuff/db-migrate.py validate
/usr/bin/python3 $OMEGAUP_ROOT/stuff/policy-tool.py validate

/usr/bin/phpunit \
	--bootstrap $OMEGAUP_ROOT/frontend/tests/bootstrap.php \
	--configuration $OMEGAUP_ROOT/frontend/tests/phpunit.xml \
	$FILTER_ARG $OMEGAUP_ROOT/frontend/tests/controllers

/usr/bin/phpunit \
	--bootstrap frontend/tests/bootstrap.php \
	--configuration frontend/tests/phpunit.xml \
	$FILTER_ARG $OMEGAUP_ROOT/frontend/tests/badges

find \
	$OMEGAUP_ROOT/frontend/server/src/ \
	$OMEGAUP_ROOT/frontend/server/cmd/ -type d | xargs ./vendor/bin/psalm

/usr/bin/python3 -m pytest $OMEGAUP_ROOT/frontend/tests/ui/ -s
