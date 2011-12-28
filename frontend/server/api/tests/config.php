<?php


# ####################################
# DATABASE CONFIG
# ####################################	
define("OMEGAUP_DB_USER", 	"root");
define("OMEGAUP_DB_PASS", 	"");
define("OMEGAUP_DB_HOST", 	"localhost");
define("OMEGAUP_DB_NAME", 	"omegaup2");	
define("OMEGAUP_DB_DRIVER", "mysqlt");
define("OMEGAUP_DB_DEBUG", 	false);	
define("OMEGAUP_MD5_SALT", 	"om3gaup");	


define("OMEGAUP_LOG_TO_FILE", true);
define("OMEGAUP_LOG_ACCESS_FILE", 	"/Applications/XAMPP/htdocs/omegaup/frontend/server/api/tests/omegaup.log");
define("OMEGAUP_LOG_ERROR_FILE", 	"/Applications/XAMPP/htdocs/omegaup/frontend/server/api/tests/omegaup.log");
define("OMEGAUP_LOG_TRACKBACK", false);
define("OMEGAUP_LOG_DB_QUERYS", true);

define("OMEGAUP_SSLCERT_URL", "/Applications/XAMPP/htdocs/omegaup/frontend/server/api/tests/omegaup.pem");
define("OMEGAUP_CACERT_URL", "/Applications/XAMPP/htdocs/omegaup/frontend/server/apu/tests/omegaup-ca.crt");
define("OMEGAUP_GRADER_URL", "https://localhost:21680/grade/");


ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "C:/XAMPP/htdocs/omegaup/frontend/server");
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "/Applications/XAMPP/xamppfiles/htdocs/omegaup/frontend/server");	