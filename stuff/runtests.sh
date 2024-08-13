#!/bin/bash

set -e

OMEGAUP_ROOT=$(git rev-parse --show-toplevel)
REF=$(git rev-parse --abbrev-ref --symbolic-full-name @{u} 2>/dev/null || true)

if [[ "$REF" = "" ]]; then
	echo "This branch has no remote set, running against working directory." >&2
	echo "If you want to run them, use" >&2
	echo >&2
	echo "    git branch --set-upstream-to=<upstream>" >&2
	echo >&2
fi

if [[ "${OMEGAUP_ROOT}" == "/opt/omegaup" ]]; then
	IN_DOCKER=1
else
	IN_DOCKER=0
fi

if [[ "${IN_DOCKER}" == 1 ]]; then
	python3 "${OMEGAUP_ROOT}/stuff/db-migrate.py" validate
	python3 "${OMEGAUP_ROOT}/stuff/policy-tool.py" validate
	# This runs the controllers + badges PHPUnit tests, as well as the MySQL return
	# type check.
	"${OMEGAUP_ROOT}/stuff/mysql_types.sh"
else
	docker compose exec -T frontend python3 "./stuff/db-migrate.py" validate
	docker compose exec -T frontend python3 "./stuff/policy-tool.py" validate
	# This runs the controllers + badges PHPUnit tests, as well as the MySQL return
	# type check.
	docker compose exec -T frontend "./stuff/mysql_types.sh"
fi

"${OMEGAUP_ROOT}/vendor/bin/psalm" --show-info=false

if [[ "${IN_DOCKER}" == 1 ]]; then
	echo "Please run \`./stuff/lint.sh ${REF}\` outside the container after this."
	echo "Please run \`python3 -m pytest ./frontend/tests/ui/ -s\` outside the container after this."
else
	"${OMEGAUP_ROOT}/stuff/git-hooks/pre-push" $REF
	python3 -m pytest "${OMEGAUP_ROOT}/frontend/tests/ui/" -s
fi
