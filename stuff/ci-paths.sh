#!/bin/bash

# Classify a pull-request diff into the CI jobs that can see it.
# ponytail: prefix match. A shared PHP file still runs the whole php job.

set -eu

classify() {
	local php=false frontend=false python=false f
	while IFS= read -r f || [[ -n "${f:-}" ]]; do
		[[ -z "${f}" ]] && continue
		case "${f}" in
			frontend/www/docs/*|stuff/docs/*) ;;
			.github/workflows/*|.github/actions/*|docker-compose.yml|stuff/ci-paths.sh)
				php=true
				frontend=true
				python=true
				;;
			composer.json|composer.lock|frontend/server/*|frontend/tests/*|frontend/database/*|stuff/*)
				php=true
				;;
		esac
		case "${f}" in
			frontend/www/docs/*|stuff/docs/*) ;;
			.github/workflows/*|stuff/ci-paths.sh) ;;
			frontend/www/*|cypress/*|package.json|pnpm-lock.yaml|pnpm-workspace.yaml|webpack.config*.js|jest.config.js|tsconfig.json|.babelrc|.nvmrc|cypress.config.ts)
				frontend=true
				;;
		esac
		case "${f}" in
			stuff/docs/*) ;;
			stuff/*.py)
				python=true
				;;
		esac
	done
	printf 'php=%s\nfrontend=%s\npython=%s\n' "${php}" "${frontend}" "${python}"
}

if [[ "${1:-}" == "--self-check" ]]; then
	check() {
		local name="$1" input="$2" want="$3" got
		got=$(printf '%s\n' ${input} | classify)
		if [[ "${got}" != "${want}" ]]; then
			echo "FAIL ${name}" >&2
			echo "want:" >&2
			printf '%s\n' "${want}" >&2
			echo "got:" >&2
			printf '%s\n' "${got}" >&2
			exit 1
		fi
	}
	check docs $'README.md' $'php=false\nfrontend=false\npython=false'
	check wiki $'frontend/www/docs/Home.md' $'php=false\nfrontend=false\npython=false'
	check php $'frontend/server/src/Controllers/User.php' $'php=true\nfrontend=false\npython=false'
	check js $'frontend/www/js/omegaup/api.ts' $'php=false\nfrontend=true\npython=false'
	check py $'stuff/cron/foo.py' $'php=true\nfrontend=false\npython=true'
	check sh $'stuff/mysql_types.sh' $'php=true\nfrontend=false\npython=false'
	check workflow $'.github/workflows/ci.yml' $'php=true\nfrontend=true\npython=true'
	check compose $'docker-compose.yml' $'php=true\nfrontend=true\npython=true'
	check docs_py $'stuff/docs/tool.py' $'php=false\nfrontend=false\npython=false'
	echo "ci-paths ok"
	exit 0
fi

classify
