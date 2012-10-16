<?php

/*
 * Bootstrap file
 * 
 * 
 * */

// Set default time
date_default_timezone_set('UTC');

// Loads config
define('SERVER_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . "server" );

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH);

if(!is_file(SERVER_PATH . DIRECTORY_SEPARATOR . "config.php")){

		?>
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<HTML>
		<head>
			<link rel="stylesheet" type="text/css" href="css/style.css">
		</head>
		<body style="padding:5px">
			<h1>No config file.</h1>
				<p>You are missing the config file. It must look something like this:</p>
			<pre class="code">
				<?php include ("config.php.sample") ; ?>
			</pre>
			</body>
		</html>
		<?php

		exit;
}

require_once(SERVER_PATH . "/config.php");

/*
 * Load libraries
 * 
 * 
 * */
require_once("libs/Logger/Logger.php");
require_once('dao/model.inc.php');
require_once( "libs/GoogleOpenID.php" );

/**
  * Load controllers
  *
  **/
require_once("controllers/login.controller.php");
require_once("controllers/users.controller.php");
require_once("controllers/problems.controller.php");





/*
 * This global variables, will be accessible trough all of the program
 * and will contain relevant information for the current session, and 
 * other handy global configuration options.
 * 
 *
 * */
$GLOBALS = array();	
$GLOBALS["user"] = array();
$GLOBALS["session"] = array();




/*
 * Load the rest of the base classes
 * 
 * */
require_once( "definitions.php" );
require_once( "libs/GUI/GUI.inc.php" );




/*
 * Connect to database with the appropiate permissions
 * based on the session retrived above. This way, a non-logged
 * user will have only SELECT permissions for example. If a 
 * bug is found, they will have limited access to the database.
 * 
 * */
require_once('adodb5/adodb.inc.php');
require_once('adodb5/adodb-exceptions.inc.php');

$conn = null;


try{
    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);
    $conn->debug = OMEGAUP_DB_DEBUG;
    $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);

} catch (Exception $e) {

	Logger::error($e);
        //if(!$conn) {
            $conn = ADONewConnection(OMEGAUP_SLAVE_DB_DRIVER);
            $conn->PConnect(OMEGAUP_SLAVE_DB_HOST, OMEGAUP_SLAVE_DB_USER, OMEGAUP_SLAVE_DB_PASS, OMEGAUP_SLAVE_DB_NAME);
        //}
	
	//die("error");
}
