<?php
if (!function_exists('try_define')) {
    function try_define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}

# ####################################
# EXPERIMENTS
# ####################################
try_define('EXPERIMENT_SCHOOLS', true);

# ####################################
# DATABASE CONFIG
# ####################################
try_define('OMEGAUP_DB_NAME', 'omegaup-test');
try_define('OMEGAUP_DB_USER', 'root');
try_define('OMEGAUP_DB_PASS', '');

# ####################################
# TEST CONFIG
# ####################################
try_define('OMEGAUP_FRONTEND_SERVER_ROOT', OMEGAUP_ROOT . '/server/');
try_define('OMEGAUP_TEST_ROOT', OMEGAUP_ROOT . '/tests/controllers/');
try_define('OMEGAUP_RESOURCES_ROOT', OMEGAUP_ROOT . '/tests/resources/');
try_define('OMEGAUP_BASE_URL', 'http://localhost');

# ####################################
# LOG CONFIG
# ####################################
try_define('OMEGAUP_LOG_FILE', OMEGAUP_TEST_ROOT . 'omegaup.log');
try_define('OMEGAUP_LOG_LEVEL', 'debug');

# ####################################
# GRADER CONFIG
# ####################################
try_define('OMEGAUP_GRADER_URL', 'https://localhost:21680/grade/');
try_define('OMEGAUP_SSLCERT_URL', OMEGAUP_ROOT . '/omegaup.pem');
try_define('OMEGAUP_CACERT_URL', OMEGAUP_ROOT . '/omegaup.pem');
try_define('RUNS_PATH', OMEGAUP_TEST_ROOT . 'submissions');
try_define('GRADE_PATH', OMEGAUP_TEST_ROOT . 'grade');
try_define('PROBLEMS_PATH', OMEGAUP_TEST_ROOT . 'problems');
try_define('PROBLEMS_GIT_PATH', OMEGAUP_TEST_ROOT . 'problems.git');
try_define('IMAGES_PATH', OMEGAUP_TEST_ROOT . 'img/');
try_define('IMAGES_URL_PATH', '/img/');
try_define('BIN_PATH', OMEGAUP_ROOT . '/../bin');

# #########################
# CACHE CONFIG
# #########################
try_define('APC_USER_CACHE_ENABLED', true);
try_define('OMEGAUP_SESSION_CACHE_ENABLED', false);

# #########################
# SMARTY USER CACHE
# #########################
try_define('SMARTY_CACHE_DIR', '/var/tmp/omegaup/');
#
# #########################
# PAGER CONSTANTS FOR TESTING
# #########################
try_define('PROBLEMS_PER_PAGE', 2);
