<?php
# ####################################
# DATABASE CONFIG
# ####################################
define('OMEGAUP_DB_NAME',                      'omegaup-test');

# ####################################
# TEST CONFIG
# ####################################
define('OMEGAUP_FRONTEND_SERVER_ROOT',		OMEGAUP_ROOT . 'server/');
define('OMEGAUP_TEST_ROOT',			'/opt/omegaup/frontend/tests/controllers/');
define('OMEGAUP_RESOURCES_ROOT',		'/opt/omegaup/frontend/tests/resources/');
define('OMEGAUP_BASE_URL',			'http://localhost');

# ####################################
# LOG CONFIG
# ####################################
define('OMEGAUP_LOG_FILE',				OMEGAUP_TEST_ROOT . 'omegaup.log');
define('OMEGAUP_LOG_LEVEL',				"info");

# ####################################
# GRADER CONFIG
# ####################################
define('OMEGAUP_GRADER_URL',			'https://localhost:21680/grade/');
define('OMEGAUP_SSLCERT_URL',			'/opt/omegaup/frontend/omegaup.pem');
define('OMEGAUP_CACERT_URL',			'/opt/omegaup/frontend/omegaup.pem');
define('RUNS_PATH',				OMEGAUP_TEST_ROOT . 'submissions');
define('PROBLEMS_PATH',				OMEGAUP_TEST_ROOT . 'problems');
define('IMAGES_PATH',				OMEGAUP_ROOT. 'www/img/');
define('IMAGES_URL_PATH',			'/img/');
define('BIN_PATH',				'/opt/omegaup/bin');
define('OMEGAUP_GRADER_CONFIG_PATH',		'/opt/omegaup/grader/omegaup.conf');

# #########################
# CACHE CONFIG
# #########################
define('APC_USER_CACHE_ENABLED',			false);

# #########################
# SMARTY USER CACHE
# #########################
define('SMARTY_CACHE_DIR',			'/var/tmp/omegaup/');
