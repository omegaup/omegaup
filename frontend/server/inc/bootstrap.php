<?php

	/*
	 * Bootstrap file
	 * 
	 * 
	 * */

   // Set default time
   date_default_timezone_set('UTC');
   
   // Loads config
   define('SERVER_PATH', dirname(__DIR__));     
   
   ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH);

   
   
   if(!is_file(SERVER_PATH . "/config.php")){
   		echo "<h2>You are missing the config file.</h2>";
   		exit;
   }


   require_once(SERVER_PATH . "/config.php");
   require_once("libs/Logger/Logger.php");
   require_once('dao/model.inc.php');

	/**
	 * I am the API:
	 * Connect to DB, and load the DAO's. 
	 *
	 * */
	if(defined("WHOAMI") && WHOAMI == "API"){
		
		require_once('adodb5/adodb.inc.php');
		require_once('adodb5/adodb-exceptions.inc.php');
		
		$conn = null;

		try{
                    
		    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);                    
		    $conn->debug = OMEGAUP_DB_DEBUG;
		    $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);
                    
		    if(!$conn) {
				/**
				 * Dispatch missing parameters
				 * */
				header('HTTP/1.1 500 INTERNAL SERVER ERROR');

				die(json_encode(array(
					"status" => "error",
					"error"	 => "Conection to the database has failed.",
					"errorcode" => 1
				)));

		    }
		    $conn->SetCharSet('utf8');
		    $conn->EXECUTE('SET NAMES \'utf8\';');
		} catch (Exception $e) {
			
			header('HTTP/1.1 500 INTERNAL SERVER ERROR');
			
			die(json_encode(array(
				"status" => "error",
				"error"	 => $e,
				"errorcode" => 2
			)));

		}
		$GLOBALS["conn"] = $conn;
		return;
	}
		
	
	/* ****************************************************************************************************************
	 * Start and evaluate session
	 * 
	 * 
	 * 
	 * **************************************************************************************************************** */
	session_start();



	/*
	 * require googleopenid lib
	 * 
	 * 
	 * */
	require_once( "libs/GoogleOpenID.php" );
	
	
	
	
	
	/*
	 * Start and evaluate session
	 * 
	 * 
	 * 
	 * */
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

	    if(!$conn) {

			$GUI->prettyDie("No database");
	    }

	} catch (Exception $e) {

			$GUI->prettyDie("No database");

	}
	
	
	
	
	
	/*
	 * This bootstrap should be loaded via a frontend page.
	 * All pages *must* set this variable to see if they have
	 * permissions to see this page.
	 *
	 * */
	/*
	if( !defined(LEVEL_NEEDED)  ){
		//LEVEL_NEEDED WAS NOT SET !
		$GUI->prettyDie("LEVEL_NEEDED WAS NOT SET");
		
	}else{
		//check for permissions

	}
	*/
