#!/bin/bash

set -e

ROOT="$(git rev-parse --show-toplevel)"

for problem in "${ROOT}/test/problems/"*; do
	echo $(basename "${problem}")
	"${ROOT}/cmd/kareljs" compile "${problem}/sol.txt" -o "${ROOT}/cpp/sol.kx"
	for casename in "${problem}/cases"/*.in; do
		"${ROOT}/cpp/karel" "${ROOT}/cpp/sol.kx" < "${casename}" | diff -Naurw --ignore-blank-lines "${casename%.in}.out" -
	done
done
