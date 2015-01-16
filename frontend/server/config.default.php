<?php
if (!function_exists('try_define')) {
	function try_define($name, $value) {
		if (!defined($name)) define($name, $value);
	}
}
# ###################################
# GLOBAL CONFIG
# ###################################
try_define('OMEGAUP_ROOT', '/opt/omegaup/frontend');
try_define('OMEGAUP_LOCKDOWN_DOMAIN', 'localhost-lockdown');
try_define('OMEGAUP_COOKIE_DOMAIN', null);
try_define('OMEGAUP_USE_CSP', false);

# ####################################
# DATABASE CONFIG
# ####################################
try_define('OMEGAUP_DB_USER',				'omegaup');
try_define('OMEGAUP_DB_PASS',				'');
try_define('OMEGAUP_DB_HOST',				'localhost');
try_define('OMEGAUP_DB_NAME',				'omegaup');
try_define('OMEGAUP_DB_DRIVER',				'mysqli');
try_define('OMEGAUP_DB_DEBUG',				false);
try_define('OMEGAUP_MD5_SALT',				'omegaup');

try_define('OMEGAUP_SLAVE_DB_USER',				'omegaup');
try_define('OMEGAUP_SLAVE_DB_PASS',				'');
try_define('OMEGAUP_SLAVE_DB_HOST',				'8.8.8.8');
try_define('OMEGAUP_SLAVE_DB_NAME',				'omegaup');
try_define('OMEGAUP_SLAVE_DB_DRIVER',			'mysqlt');

# ####################################
# LOG CONFIG
# ####################################
try_define('OMEGAUP_LOG_TO_FILE',				true);
try_define('OMEGAUP_LOG_DB_QUERYS',				false);
try_define('OMEGAUP_LOG_LEVEL',				"info");
try_define('OMEGAUP_LOG_FILE',				'/var/log/omegaup/omegaup.log');

# ####################################
# GRADER CONFIG
# ####################################
try_define('OMEGAUP_GRADER_URL',				'https://localhost:21680/grade/');
try_define('OMEGAUP_GRADER_SECRET', 'secret');
try_define('OMEGAUP_SSLCERT_URL',				'/opt/omegaup/frontend/omegaup.pem');
try_define('OMEGAUP_CACERT_URL',				'/opt/omegaup/frontend/omegaup.pem');
try_define('RUNS_PATH',					'/var/lib/omegaup/submissions');
try_define('PROBLEMS_PATH',					'/var/lib/omegaup/problems');
try_define('PROBLEMS_GIT_PATH',					'/var/lib/omegaup/problems.git');
try_define('BIN_PATH',					'/opt/omegaup/bin');
try_define('IMAGES_PATH',					'/opt/omegaup/frontend/www/img/');
try_define('IMAGES_URL_PATH',				'/img/');
try_define('TEMPLATES_PATH',					'/opt/omegaup/frontend/www/templates/');
try_define('TEMPLATES_URL_PATH',				'/templates/');
try_define('OMEGAUP_GRADER_CONFIG_PATH',			'/opt/omegaup/grader/omegaup.conf');
try_define('OMEGAUP_GRADER_RELOAD_CONFIG_URL',		'https://localhost:21680/reload-config/');
try_define('OMEGAUP_GRADER_STATUS_URL',			'https://localhost:21680/status/');
try_define('OMEGAUP_ENABLE_REJUDGE_ON_PROBLEM_UPDATE',	true);
try_define('OMEGAUP_GRADER_BROADCAST_URL',			'https://localhost:21680/broadcast/');

# ####################################
# FACEBOOK LOGIN CONFIG
# ####################################
try_define('OMEGAUP_FB_APPID',				'xxxxx');
try_define('OMEGAUP_FB_SECRET',				'xxxxx');

# ####################################
# GOOGLE ANALYTICS
# ####################################
try_define('OMEGAUP_GA_TRACK',				false);
try_define('OMEGAUP_GA_ID',					'xxxxx');

# ####################################
# EMAIL CONFIG
# ####################################
try_define('OMEGAUP_EMAIL_SEND_EMAILS',			false);
try_define('OMEGAUP_FORCE_EMAIL_VERIFICATION',		false);
try_define('OMEGAUP_EMAIL_SMTP_HOST',			'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_USER',			'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_PASSWORD',		'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_PORT',			'xxxx');
try_define('OMEGAUP_EMAIL_SMTP_FROM',			'xxxx');
try_define('OMEGAUP_EMAIL_MAILCHIMP_ENABLE', false);
try_define('OMEGAUP_EMAIL_MAILCHIMP_API_KEY', 'xxxx');
try_define('OMEGAUP_EMAIL_MAILCHIMP_LIST_ID', 'xxxx');

# #########################
# CACHE CONFIG
# #########################
try_define('APC_USER_CACHE_ENABLED',			true);
try_define('APC_USER_CACHE_CONTEST_INFO_TIMEOUT',		10);
try_define('APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT',	60); // in seconds
try_define('APC_USER_CACHE_PROBLEM_STATS_TIMEOUT',		 0); // in seconds
try_define('APC_USER_CACHE_SESSION_TIMEOUT', 8 * 3600); // seconds, match OMEGAUP_EXPIRE_TOKEN_AFTER
try_define('OMEGAUP_SESSION_CACHE_ENABLED', true);

# #########################
# SMARTY
# #########################
try_define('SMARTY_CACHE_DIR',				'/var/tmp');
try_define('IS_TEST',					FALSE);

# #########################
# PAGER CONSTANTS
# #########################
try_define('PROBLEMS_PER_PAGE', 100);

# #########################
# USER TYPES
# #########################
try_define('USER_ADMIN',		'user_admin');
try_define('USER_NORMAL',		'user_normal');
try_define('USER_ANONYMOUS',	'user_anonymous');

# ########################
# PASSWORD RESET CONFIG
# ########################
try_define('PASSWORD_RESET_TIMEOUT', 2 * 3600);
