#!/bin/sh -e

OMEGAUP_ROOT=`/usr/bin/git rev-parse --show-toplevel`

java -jar ${OMEGAUP_ROOT}/stuff/orm-client.jar -lang=php \
	-in=${OMEGAUP_ROOT}/frontend/database/schema.sql \
	-out=${OMEGAUP_ROOT}/frontend/server/libs \
	-omit-call -spaces
