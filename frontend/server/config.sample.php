<?php


	# ####################################
	# TEST DATABASE CONFIG
	# ####################################	
	# PLEASE DON'T POINT IT TO THE PRODUCTION DB!!!
	define('OMEGAUP_TEST_DB_USER',         '');
	define('OMEGAUP_TEST_DB_NAME',    	   '');
	define('OMEGAUP_TEST_DB_PASS',         '');
	define('OMEGAUP_TEST_DB_DRIVER',       'mysqlt');
	define('OMEGAUP_TEST_DB_DEBUG',        false);
	define('OMEGAUP_TEST_DB_HOST',         'localhost');



	# #####################################
	# DATABASE CONFIG
	# ####################################
	define('OMEGAUP_DB_USER',         '');
	define('OMEGAUP_DB_PASS',         '');
	define('OMEGAUP_DB_HOST',         'localhost');
	define('OMEGAUP_DB_NAME',         '');	
	define('OMEGAUP_DB_DRIVER',       'mysqlt');
	define('OMEGAUP_DB_DEBUG',        false);	
	define('OMEGAUP_ROOT',            '/opt/omegaup/frontend');
	define('OMEGAUP_MD5_SALT',        '');
	define('OMEGAUP_GRADER_URL',      '');
	define('OMEGAUP_SSLCERT_URL',     '');
	define('OMEGAUP_CACERT_URL',      '');

	define('OMEGAUP_LOG_TO_FILE',     true);
	define('OMEGAUP_LOG_ACCESS_FILE', '/opt/omegaup/frontend/log/omegaup.log');
	define('OMEGAUP_LOG_ERROR_FILE',  '/opt/omegaup/frontend/log/omegaup.log');
	define('OMEGAUP_LOG_TRACKBACK',   false);
	define('OMEGAUP_LOG_DB_QUERYS',   false);

	define('RUNS_PATH',               '');
	define('PROBLEMS_PATH',           '');

	define("OMEGAUP_FB_APPID",        "");
	define("OMEGAUP_FB_SECRET",       "");



	# ####################################
	# EMAIL CONFIG
	# ####################################
	define("OMEGAUP_EMAIL_SEND_EMAILS", 		true);
	define("OMEGAUP_EMAIL_SMTP_HOST", 			"");
	define("OMEGAUP_EMAIL_SMTP_USER", 			"");
	define("OMEGAUP_EMAIL_SMTP_PASSWORD", 		"");
	define("OMEGAUP_EMAIL_SMTP_PORT", 			"");
	define("OMEGAUP_EMAIL_SMTP_FROM", 			"");

	# ####################################
	# MEMCACHE CONFIG
	# ####################################	
	define("OMEGAUP_MEMCACHE_DISABLED",				true);	
	define("OMEGAUP_MEMCACHE_HOST",					'localhost');
	define("OMEGAUP_MEMCACHE_PORT", 				11211);
	define("OMEGAUP_MEMCACHE_SCOREBOARD_TIMEOUT",	60);	// in seconds
	define("OMEGAUP_MEMCACHE_CONTEST_TIMEOUT",		30);	// in seconds	

	# ####################################
	# GOOGLE ANALYTICS
	# ####################################
	define("OMEGAUP_GA_TRACK",                      true);
	define("OMEGAUP_GA_ID",                         "	");	