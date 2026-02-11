<?php
/**
 * @psalm-suppress MissingFile try_define.php definitely exists...
 * @psalm-suppress MixedOperand OMEGAUP_ROOT is really a string
 */
require_once OMEGAUP_ROOT . '/server/try_define.php';

/** @var string */
$_omegaUpRoot = OMEGAUP_ROOT;
$_testShard = intval(getenv('TEST_TOKEN') ?: '0');

# ####################################
# EXPERIMENTS
# ####################################

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
# GRADER CONFIG (test-only secrets - do not use in production)
# ####################################
try_define(
    'OMEGAUP_GITSERVER_SECRET_KEY',
    'GdhxduUWe/y18iCnEWbTFX+JE4O8vSQPTUkjWtWf6ASAoSDkmUg4DUGwjERNliGN35kZyFj+tl5AzQaF4Ba9fA=='
);
try_define(
    'OMEGAUP_GITSERVER_PUBLIC_KEY',
    'gKEg5JlIOA1BsIxETZYhjd+ZGchY/rZeQM0GheAWvXw='
);
try_define('OMEGAUP_GRADER_SECRET', 'secret');
try_define(
    'OMEGAUP_COURSE_CLONE_SECRET_KEY',
    '6f8xSU_xkrelmCTSahbbxl3PRovgAfkrThyrqQ9JesE'
);
try_define('OMEGAUP_EXPERIMENT_SECRET', 'omegaup');
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
try_define('OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE', true);

# #########################
# CACHE CONFIG
# #########################
try_define('OMEGAUP_CACHE_IMPLEMENTATION', 'inprocess');
try_define('OMEGAUP_SESSION_CACHE_ENABLED', false);
try_define('OMEGAUP_SESSION_API_HOURLY_LIMIT', 10);

# #########################
# TEMPLATES
# #########################
try_define('TEMPLATE_CACHE_DIR', '/var/tmp/omegaup/');

# ####################################
# RABBITMQ CONFIG
# ####################################
try_define('OMEGAUP_RABBITMQ_HOST', 'rabbitmq');
try_define('OMEGAUP_RABBITMQ_PORT', '5672');
try_define('OMEGAUP_RABBITMQ_USERNAME', 'omegaup');
try_define('OMEGAUP_RABBITMQ_PASSWORD', 'omegaup');
