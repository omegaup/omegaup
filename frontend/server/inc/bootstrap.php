<?php

	/*
	 * Bootstrap file
	 * 
	 * 
	 * */
	//var_dump($_SERVER);
	$loc = $SERVER["SCRIPT_FILENAME"];
	

	/*
	 * Load configuration file, and parse the contents needed to parse.
	 * 
	 * 
	 * */
	require_once( "../server/config.php" );
	
	/*
	 * Start and evaluate session
	 * 
	 * 
	 * 
	 * */
	

	

	


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
	require_once( "gui.php" );
	
	
	
	
	/*
	 * Load theme onto $GUI which is the variable 
	 * used in the frontend rendering, so changing
	 * a theme would be as painless as possible.
	 * 
	 * @TODO this should be loaded according to the theme specified in the config file, it might even be loaded via specific user value in the database
	 * 
	 * */
	$GUI = new ClassicTheme();
	
	
	
	
	/*
	 * Connect to database with the appropiate permissions
	 * based on the session retrived above. This way, a non-logged
	 * user will have only SELECT permissions for example. If a 
	 * bug is found, they will have limited access to the database.
	 * 
	 * */
	require_once('../adodb5/adodb.inc.php');
	require_once('../adodb5/adodb-exceptions.inc.php');

	$conn = null;

	try{
	    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);
	    $conn->debug = OMEGAUP_DB_DEBUG;
	    $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);

	    if(!$conn) {

			$GUI::prettyDie("No database");
	    }

	} catch (Exception $e) {

			$GUI::prettyDie("No database");

	}
	
	
	
	
	
	/*
	 * This bootstrap should be loaded via a frontend page.
	 * All pages *must* set this variable to see if they have
	 * permissions to see this page.
	 *
	 * */
	
	if( LEVEL_NEEDED  ){
		//LEVEL_NEEDED WAS NOT SET !
		$GUI::prettyDie();
		
	}else{
		//check for permissions
		
	}
?>