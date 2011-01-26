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
	 * Connect to database with the appropiate user.
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
?>