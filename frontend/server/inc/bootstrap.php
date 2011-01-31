<?php

	/*
	 * Bootstrap file
	 * 
	 * 
	 * */

	/*
	 * Load configuration file, and parse the contents needed to parse.
	 * 
	 * 
	 * */
	
	/*
	 * Start and evaluate session
	 * 
	 * 
	 * 
	 * */
	

	
	/*
	 * Connect to database with the appropiate permissions
	 * based on the session retrived above. This way, a non-logged
	 * user will have only SELECT permissions for example. If a 
	 * bug is found, they will have limited access to the database.
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