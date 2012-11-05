<?php

/*
 * Bootstrap file
 * 
 * 
 * */

// Set default time
date_default_timezone_set('UTC');

//set paths
define( 'SERVER_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . "server" );
ini_set( 'include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH );



if(!is_file(SERVER_PATH . DIRECTORY_SEPARATOR . "config.php"))
{
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

require_once( SERVER_PATH . "/config.php" );
define("OMEGAUP_AUTH_TOKEN_COOKIE_NAME", "ouat");

/*
 * Load libraries
 * 
 * */
require_once("libs/logger/Logger.php");
require_once("libs/dao/model.inc.php");
require_once("libs/SessionManager.php");


/**
  * Load controllers
  *
  **/
require_once("controllers/users.controller.php");
require_once("controllers/sesion.controller.php");

require_once("libs/adodb5/adodb.inc.php");
require_once("libs/adodb5/adodb-exceptions.inc.php");

$conn = null;

try
{
    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);
    $conn->debug = OMEGAUP_DB_DEBUG;
    if( /* site ready only? */ false )
    {
        $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_READONLY_USER, OMEGAUP_DB_READONLY_PASS, OMEGAUP_DB_NAME);
    }else{
        $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);
    }
    

} catch ( Exception $databaseConectionException ) {
    var_dump( $databaseConectionException );
}


if( /* do we need smarty to load? */ true)
{
    include("libs/smarty/Smarty.class.php");

    $smarty = new Smarty;
    $smarty->assign( 'ERROR_TO_USER', '' );
    $smarty->setTemplateDir( SERVER_PATH . '\\..\\templates\\' );
    $smarty->setCacheDir( "C:\\Users\\Alan\\Desktop\\cache" )->setCompileDir(  "C:\\Users\\Alan\\Desktop\\cache" );
    $smarty->configLoad("C:\\xampp\\htdocs\\omegaup\\omegaup\\frontend\\templates\\es.lang");
    $smarty->assign( 'LOGGED_IN', '0' );


    $c_Sesion = new SesionController;
    if (  $c_Sesion->CurrentSesionAvailable( ) )
    {
        $smarty->assign( 'LOGGED_IN', '1' );

        $a_CurrentSesion = $c_Sesion->CurrentSesion( );

        $smarty->assign( 'CURRENT_USER_USERNAME', $a_CurrentSesion["username"] );
        $smarty->assign( 'CURRENT_USER_EMAIL', $a_CurrentSesion["email"] );
        $smarty->assign( 'CURRENT_USER_GRAVATAR_URL_128', "<img src='https://secure.gravatar.com/avatar/" . md5( $a_CurrentSesion["email"] ) . "?s=92'>" );
        $smarty->assign( 'CURRENT_USER_GRAVATAR_URL_16', "<img src='https://secure.gravatar.com/avatar/" . md5( $a_CurrentSesion["email"] ) . "?s=16'>" );
    }

}


