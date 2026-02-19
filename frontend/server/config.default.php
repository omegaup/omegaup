<?php
require_once dirname(__DIR__) . '/server/try_define.php';

# ###################################
# GLOBAL CONFIG
# ###################################
try_define('OMEGAUP_LOCKDOWN_DOMAIN', 'localhost-lockdown');
try_define('OMEGAUP_COOKIE_DOMAIN', '');
try_define('OMEGAUP_AUTH_TOKEN_COOKIE_NAME', 'ouat');
try_define('OMEGAUP_MD5_SALT', 'omegaup');
try_define('OMEGAUP_URL', 'http://localhost');
try_define('OMEGAUP_CSRF_HOSTS', []);
try_define('OMEGAUP_ENVIRONMENT', 'production');
try_define('OMEGAUP_MAINTENANCE', null);
try_define('OMEGAUP_SESSION_API_HOURLY_LIMIT', 1000);

# ####################################
# TEST CONFIG
# ####################################
try_define('IS_TEST', false);
try_define('OMEGAUP_ENABLE_SOCIAL_MEDIA_RESOURCES', true);
/** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
try_define(
    'OMEGAUP_TEST_ROOT',
    sprintf('%s/tests/controllers/', strval(OMEGAUP_ROOT))
);
/** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
try_define(
    'OMEGAUP_TEST_RESOURCES_ROOT',
    sprintf('%s/tests/resources/', strval(OMEGAUP_ROOT))
);
try_define('OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT', false);

# ####################################
# DATABASE CONFIG
# ####################################
try_define('OMEGAUP_DB_USER', 'omegaup');
try_define('OMEGAUP_DB_PASS', '');
try_define('OMEGAUP_DB_HOST', 'mysql:13306');
try_define('OMEGAUP_DB_NAME', 'omegaup');

# ####################################
# EXPERIMENTS
# ####################################
try_define('OMEGAUP_EXPERIMENT_SECRET', 'CHANGE_ME');

# ####################################
# LOG CONFIG
# ####################################
try_define('OMEGAUP_LOG_TO_FILE', true);
try_define('OMEGAUP_LOG_DB_QUERYS', false);
try_define('OMEGAUP_LOG_LEVEL', 'info');
try_define('OMEGAUP_LOG_FILE', '/var/log/omegaup/omegaup.log');
try_define('OMEGAUP_CSP_LOG_FILE', '/var/log/omegaup/csp.log');
try_define('OMEGAUP_JSERROR_LOG_FILE', '/var/log/omegaup/jserror.log');
try_define('OMEGAUP_MYSQL_TYPES_LOG_FILE', '/var/log/omegaup/omegaup.log');

# ####################################
# GRADER CONFIG
# ####################################
try_define('OMEGAUP_GRADER_URL', 'https://localhost:21680');
try_define('OMEGAUP_GITSERVER_PORT', '33861');
try_define(
    'OMEGAUP_GITSERVER_URL',
    'http://localhost:' . OMEGAUP_GITSERVER_PORT
);
try_define('OMEGAUP_GITSERVER_SECRET_KEY', 'CHANGE_ME');
try_define('OMEGAUP_GITSERVER_PUBLIC_KEY', 'CHANGE_ME');
try_define('OMEGAUP_GITSERVER_SECRET_TOKEN', '');
try_define('OMEGAUP_GRADER_SECRET', 'CHANGE_ME');
/** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
try_define(
    'IMAGES_PATH',
    sprintf('%s/www/img/', strval(OMEGAUP_ROOT))
);
try_define('IMAGES_URL_PATH', '/img/');
/** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
try_define(
    'INPUTS_PATH',
    sprintf('%s/www/probleminput/', strval(OMEGAUP_ROOT))
);
try_define('INPUTS_URL_PATH', '/probleminput/');
/** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
try_define(
    'TEMPLATES_PATH',
    sprintf('%s/www/templates/', strval(OMEGAUP_ROOT))
);
try_define('TEMPLATES_URL_PATH', '/templates/');
try_define('OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE', false);
try_define('OMEGAUP_GRADER_FAKE', false);

# ####################################
# COURSE CLONE CONFIG
# ####################################
try_define('OMEGAUP_COURSE_CLONE_SECRET_KEY', 'CHANGE_ME');

# ####################################
# RABBITMQ CONFIG
# ####################################
try_define('OMEGAUP_RABBITMQ_HOST', 'rabbitmq');
try_define('OMEGAUP_RABBITMQ_PORT', 5672);
try_define('OMEGAUP_RABBITMQ_USERNAME', 'xxxxx');
try_define('OMEGAUP_RABBITMQ_PASSWORD', 'xxxxx');

# ####################################
# FACEBOOK LOGIN CONFIG
# ####################################
try_define('OMEGAUP_FB_APPID', 'xxxxx');
try_define('OMEGAUP_FB_SECRET', 'xxxxx');

# ####################################
# GOOGLE LOGIN CONFIG
# ####################################
try_define('OMEGAUP_GOOGLE_SECRET', 'xxxxx');
try_define('OMEGAUP_GOOGLE_CLIENTID', 'xxxxx');

# ####################################
# GITHUB LOGIN CONFIG
# ####################################
try_define('OMEGAUP_GITHUB_CLIENT_ID', 'xxxxx');
try_define(
    'OMEGAUP_GITHUB_CLIENT_SECRET',
    'xxxxx'
);

# ####################################
# GOOGLE ANALYTICS
# ####################################
try_define('OMEGAUP_GA_TRACK', false);
try_define('OMEGAUP_GA_ID', 'xxxxx');

# ####################################
# GOOGLE RECAPTCHA
# ####################################
try_define('OMEGAUP_RECAPTCHA_SECRET', 'xxxx');
try_define('OMEGAUP_VALIDATE_CAPTCHA', false);

# ####################################
# EMAIL CONFIG
# ####################################
try_define('OMEGAUP_EMAIL_SEND_EMAILS', false);
try_define('OMEGAUP_FORCE_EMAIL_VERIFICATION', false);
try_define('OMEGAUP_EMAIL_SMTP_HOST', 'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_USER', 'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_PASSWORD', 'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_PORT', 'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_FROM', 'xxxx');
try_define('OMEGAUP_EMAIL_SENDY_ENABLE', true);
try_define('OMEGAUP_EMAIL_SENDY_SUBSCRIBE_URL', 'xxx');
try_define('OMEGAUP_EMAIL_SENDY_LIST', 'xxx');

# #########################
# CACHE CONFIG
# #########################
try_define('APC_USER_CACHE_TIMEOUT', 7 * 24 * 3600); // in seconds
try_define('APC_USER_CACHE_CONTEST_INFO_TIMEOUT', 10);
try_define('APC_USER_CACHE_PROBLEM_LIST_TIMEOUT', 60 * 30); // in seconds
try_define('APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT', 60); // in seconds
try_define('APC_USER_CACHE_PROBLEM_STATS_TIMEOUT', 0); // in seconds
try_define('APC_USER_CACHE_SESSION_TIMEOUT', 8 * 3600); // seconds, match OMEGAUP_EXPIRE_TOKEN_AFTER
try_define('APC_USER_CACHE_USER_RANK_TIMEOUT', 60 * 30); // in seconds, half of mysql event Refresh_User_Rank_Event
try_define('OMEGAUP_SESSION_CACHE_ENABLED', true);
try_define('OMEGAUP_CACHE_IMPLEMENTATION', 'apcu'); // apcu or redis
try_define('REDIS_HOST', 'redis');
try_define('REDIS_PORT', 6379);
try_define('REDIS_PASS', 'redis');

# #########################
# TEMPLATES
# #########################
try_define('TEMPLATE_CACHE_DIR', '/var/lib/omegaup/templates');

# #########################
# CONTEST CONSTANTS
# #########################
try_define('MAX_PROBLEMS_IN_CONTEST', 30);

# #########################
# IDENTITY TYPES
# #########################
try_define('IDENTITY_ADMIN', 'identity_admin');
try_define('IDENTITY_NORMAL', 'identity_normal');
try_define('IDENTITY_ANONYMOUS', 'identity_anonymous');

# ########################
# PASSWORD RESET CONFIG
# ########################
try_define('PASSWORD_RESET_TIMEOUT', 2 * 3600);
try_define('PASSWORD_RESET_MIN_WAIT', 5 * 60);

# ########################
# S3 CONFIG
# ########################
try_define('AWS_CLI_ACCESS_KEY_ID', null);
try_define('AWS_CLI_SECRET_ACCESS_KEY', null);

# ########################
# NEW RELIC CONFIG
# ########################
try_define('NEW_RELIC_SCRIPT', null);
try_define('NEW_RELIC_SCRIPT_HASH', null);
