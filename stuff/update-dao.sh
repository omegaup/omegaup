#!/bin/sh -e

OMEGAUP_ROOT=`/usr/bin/git rev-parse --show-toplevel`

python3 "${OMEGAUP_ROOT}/stuff/update-dao.py"
