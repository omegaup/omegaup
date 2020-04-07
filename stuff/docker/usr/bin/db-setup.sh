#!/bin/sh

set -e

if ! /opt/omegaup/stuff/db-migrate.py --mysql-config-file=/home/ubuntu/.my.cnf exists ; then
  mysql --defaults-file=/home/ubuntu/.my.cnf \
    -e "CREATE USER IF NOT EXISTS 'omegaup'@'localhost' IDENTIFIED BY 'omegaup';"
  exec /opt/omegaup/stuff/bootstrap-environment.py \
    --mysql-config-file=/home/ubuntu/.my.cnf \
    --purge --verbose --root-url=http://localhost:8000/
else
  exec /opt/omegaup/stuff/db-migrate.py \
    --mysql-config-file=/home/ubuntu/.my.cnf migrate
fi

