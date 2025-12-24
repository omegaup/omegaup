#!/bin/sh -e

OMEGAUP_ROOT=`/usr/bin/git rev-parse --show-toplevel`

# Copy schema.sql to dao_schema.sql to trigger DAO regeneration
cp "${OMEGAUP_ROOT}/frontend/database/schema.sql" "${OMEGAUP_ROOT}/frontend/database/dao_schema.sql"

python3 "${OMEGAUP_ROOT}/stuff/update-dao.py"
