<?php




	# ####################################
	# DATABASE CONFIG
	# ####################################	
	define("OMEGAUP_DB_USER", "root");
	define("OMEGAUP_DB_PASS", 	"");
	define("OMEGAUP_DB_HOST", 	"localhost");
	define("OMEGAUP_DB_NAME", 	"omegaup2");	
	define("OMEGAUP_DB_DRIVER", "mysqlt");
	define("OMEGAUP_DB_DEBUG", 	false);	
	define("OMEGAUP_MD5_SALT", 	"om3gaup");	
	
	ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "C:/XAMPP/htdocs/omegaup/frontend/server");
	ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "/Applications/XAMPP/xamppfiles/htdocs/omegaup/frontend/server");	