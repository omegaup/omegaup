<?php
if (!function_exists('try_define')) {
	function try_define($name, $value) {
		if (!defined($name)) define($name, $value);
	}
}
# ####################################
# DATABASE CONFIG
# ####################################
try_define('OMEGAUP_DB_NAME',                      'omegaup-test');

# ####################################
# TEST CONFIG
# ####################################
try_define('OMEGAUP_FRONTEND_SERVER_ROOT',		OMEGAUP_ROOT . 'server/');
try_define('OMEGAUP_TEST_ROOT',			'/opt/omegaup/frontend/tests/controllers/');
try_define('OMEGAUP_RESOURCES_ROOT',		'/opt/omegaup/frontend/tests/resources/');
try_define('OMEGAUP_BASE_URL',			'http://localhost');

# ####################################
# LOG CONFIG
# ####################################
try_define('OMEGAUP_LOG_FILE',				OMEGAUP_TEST_ROOT . 'omegaup.log');
try_define('OMEGAUP_LOG_LEVEL',				"debug");

# ####################################
# GRADER CONFIG
# ####################################
try_define('OMEGAUP_GRADER_URL',			'https://localhost:21680/grade/');
try_define('OMEGAUP_SSLCERT_URL',			'/opt/omegaup/frontend/omegaup.pem');
try_define('OMEGAUP_CACERT_URL',			'/opt/omegaup/frontend/omegaup.pem');
try_define('RUNS_PATH',				OMEGAUP_TEST_ROOT . 'submissions');
try_define('PROBLEMS_PATH',				OMEGAUP_TEST_ROOT . 'problems');
try_define('IMAGES_PATH',				OMEGAUP_ROOT. 'www/img/');
try_define('IMAGES_URL_PATH',			'/img/');
try_define('BIN_PATH',				'/opt/omegaup/bin');
try_define('OMEGAUP_GRADER_CONFIG_PATH',		'/opt/omegaup/grader/omegaup.conf');

# #########################
# CACHE CONFIG
# #########################
try_define('APC_USER_CACHE_ENABLED',			false);

# #########################
# SMARTY USER CACHE
# #########################
try_define('SMARTY_CACHE_DIR',			'/var/tmp/omegaup/');
