<?php
/** @psalm-suppress MissingFile try_define.php definitely exists... */
require_once OMEGAUP_ROOT . '/server/try_define.php';

/** @var string */
$_omegaUpRoot = OMEGAUP_ROOT;
$_testShard = intval(getenv('TEST_TOKEN') ?: '0');

# ####################################
# EXPERIMENTS
# ####################################
try_define('EXPERIMENT_IDENTITIES', true);

# ####################################
# DATABASE CONFIG
# ####################################
if (!empty($_testShard)) {
    try_define('OMEGAUP_DB_NAME', "omegaup-test-{$_testShard}");
} else {
    try_define('OMEGAUP_DB_NAME', 'omegaup-test');
}
try_define('OMEGAUP_DB_USER', 'root');
try_define('OMEGAUP_DB_PASS', '');
try_define('OMEGAUP_DB_HOST', '127.0.0.1');

# ####################################
# TEST CONFIG
# ####################################
try_define('OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT', true);
try_define('OMEGAUP_TEST_RESOURCES_ROOT', "{$_omegaUpRoot}/tests/resources/");
try_define(
    'OMEGAUP_TEST_ROOT',
    "{$_omegaUpRoot}/tests/runfiles/shard-{$_testShard}/"
);
try_define('OMEGAUP_TEST_SHARD', $_testShard);

# ####################################
# LOG CONFIG
# ####################################
try_define('OMEGAUP_LOG_FILE', OMEGAUP_TEST_ROOT . 'omegaup.log');
try_define(
    'OMEGAUP_MYSQL_TYPES_LOG_FILE',
    OMEGAUP_TEST_ROOT . 'mysql_types.log'
);
try_define('OMEGAUP_LOG_LEVEL', 'debug');
try_define('DUMP_MYSQL_QUERY_RESULT_TYPES', true);

# ####################################
# GRADER CONFIG
# ####################################
try_define('IMAGES_PATH', OMEGAUP_TEST_ROOT . 'img/');
try_define('IMAGES_URL_PATH', '/img/');
try_define('OMEGAUP_GITSERVER_PORT', 33863 + $_testShard);
try_define(
    'OMEGAUP_GITSERVER_URL',
    'http://localhost:' . strval(OMEGAUP_GITSERVER_PORT)
);
try_define(
    'OMEGAUP_GITSERVER_SECRET_TOKEN',
    'cbaf89d3bb2ee6b0a90bc7a90d937f9ade16739ed9f573c76e1ac72064e397aac2b35075040781dd0df9a8f1d6fc4bd4a4941eb6b0b62541b0a35fb0f89cfc3f'
);
try_define('TEMPLATES_PATH', OMEGAUP_TEST_ROOT . '/templates/');
try_define('INPUTS_PATH', OMEGAUP_TEST_ROOT . '/probleminput/');

# #########################
# CACHE CONFIG
# #########################
try_define('APC_USER_CACHE_ENABLED', true);
try_define('OMEGAUP_SESSION_CACHE_ENABLED', false);
try_define('OMEGAUP_SESSION_API_HOURLY_LIMIT', 10);

# #########################
# SMARTY USER CACHE
# #########################
try_define('SMARTY_CACHE_DIR', '/var/tmp/omegaup/');
