<?php  
  define('OMEGAUP_DB_USER',         'theuser');
  define('OMEGAUP_DB_PASS',         'thepassword');
  define('OMEGAUP_DB_HOST',         'localhost');
  define('OMEGAUP_DB_NAME',         'omegaup');	
  define('OMEGAUP_DB_DRIVER',       'mysqlt');
  define('OMEGAUP_DB_DEBUG',        false);	
  define('OMEGAUP_ROOT',            'PATH TO ROOT');
  define('OMEGAUP_MD5_SALT',        'omegaup');
  define('OMEGAUP_GRADER_URL',      'https://omegaup.com:21680/grade/');
  define('OMEGAUP_SSLCERT_URL',     'omegaup.pem');
  define('OMEGAUP_CACERT_URL',      'omegaup.pem');

  
  define('OMEGAUP_TEST_ROOT',            'PATH TO TEST ROOT DIR');
  define('OMEGAUP_LOG_TO_FILE',     true);
  define('OMEGAUP_LOG_ACCESS_FILE', OMEGAUP_TEST_ROOT . 'omegaup.log');
  define('OMEGAUP_LOG_ERROR_FILE',  OMEGAUP_TEST_ROOT . 'omegaup.log');
  define('OMEGAUP_LOG_TRACKBACK',   true);
  define('OMEGAUP_LOG_DB_QUERYS',   true);

  define('RUNS_PATH',               OMEGAUP_TEST_ROOT .'/submissions');
  define('PROBLEMS_PATH',            OMEGAUP_TEST_ROOT .'/problems');

  define("OMEGAUP_FB_APPID",        "yep");
  define("OMEGAUP_FB_SECRET",       "yeah");



        # ####################################
	# EMAIL CONFIG
	# ####################################
	define("OMEGAUP_EMAIL_SEND_EMAILS", 			false);
	define("OMEGAUP_EMAIL_SMTP_HOST", 			"smtp.gmail.com");
	define("OMEGAUP_EMAIL_SMTP_USER", 			"no-reply@omegaup.com");
	define("OMEGAUP_EMAIL_SMTP_PASSWORD", 		"PASSWORD");
	define("OMEGAUP_EMAIL_SMTP_PORT", 			"PORT");
	define("OMEGAUP_EMAIL_SMTP_FROM", 			"no-reply@omegaup.com");

	# ####################################
	# MEMCACHE CONFIG
	# ####################################	
	define("OMEGAUP_MEMCACHE_DISABLED",				true);	
	define("OMEGAUP_MEMCACHE_HOST",					'localhost');
	define("OMEGAUP_MEMCACHE_PORT", 				12345);
	define("OMEGAUP_MEMCACHE_SCOREBOARD_TIMEOUT",	60);	// in seconds
	define("OMEGAUP_MEMCACHE_CONTEST_TIMEOUT",		30);	// in seconds	

	 # ####################################
        # GOOGLE ANALYTICS
        # ####################################
        define("OMEGAUP_GA_TRACK",                      true);
        define("OMEGAUP_GA_ID",                         "12345");


        # #########################
        # APC USER CACHE
        # #########################

        define("APC_USER_CACHE_ENABLED", false);
        define("APC_USER_CACHE_PROBLEM_STATEMENT", false);
        define("APC_USER_CACHE_PROBLEM_STATEMENT_TIMEOUT", 60); // in seconds        
        define("APC_USER_CACHE_SCOREBOARD", false);
        define("APC_USER_CACHE_SCOREBOARD_TIMEOUT", 60); // in seconds
        define("APC_USER_CACHE_ADMIN_SCOREBOARD", false);
        define("APC_USER_CACHE_ADMIN_SCOREBOARD_TIMEOUT", 60); // in seconds

